<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Support\Facades\Http;

class LowLatencyStreamService
{
    public function start(Room $room): void
    {
        $this->stop($room);

        $sourceUrl = trim((string) $room->live_stream_url);
        if ($sourceUrl === '' || preg_match('/youtube\.com|youtu\.be/i', $sourceUrl)) {
            return;
        }

        $dir = $this->dir($room->id);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $snapshotUrl = $this->discoverSnapshotUrl($sourceUrl);
        $latestJpg = $dir . DIRECTORY_SEPARATOR . 'latest.jpg';

        if ($snapshotUrl) {
            file_put_contents($dir . DIRECTORY_SEPARATOR . 'snapshot.url', $snapshotUrl);
            $this->spawnSnapshotLoop($room->id, $snapshotUrl, $latestJpg);
            return;
        }

        $this->spawnFfmpegPreview($room->id, $sourceUrl, $latestJpg);
    }

    public function stop(Room $room): void
    {
        $dir = $this->dir($room->id);
        foreach (['loop.pid', 'ffmpeg.pid'] as $pidName) {
            $pidFile = $dir . DIRECTORY_SEPARATOR . $pidName;
            if (!is_file($pidFile)) {
                continue;
            }
            $pid = (int) trim((string) file_get_contents($pidFile));
            if ($pid > 1) {
                if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                    @exec('taskkill /F /PID ' . $pid . ' 2>NUL');
                } else {
                    @exec('kill -9 ' . $pid . ' 2>/dev/null');
                }
            }
            @unlink($pidFile);
        }
    }

    public function latestJpegPath(int $roomId): ?string
    {
        $path = $this->dir($roomId) . DIRECTORY_SEPARATOR . 'latest.jpg';
        if (is_file($path) && filesize($path) > 100 && (time() - filemtime($path)) <= 1) {
            return $path;
        }

        $snapFile = $this->dir($roomId) . DIRECTORY_SEPARATOR . 'snapshot.url';
        if (is_file($snapFile)) {
            $url = trim((string) file_get_contents($snapFile));
            $bytes = $this->download($url);
            if ($this->isJpeg($bytes)) {
                if (!is_dir($this->dir($roomId))) {
                    mkdir($this->dir($roomId), 0777, true);
                }
                file_put_contents($path, $bytes);
                return $path;
            }
        }

        $room = Room::find($roomId);
        $sourceUrl = $room ? trim((string) $room->live_stream_url) : '';
        if ($sourceUrl !== '') {
            $snapshotUrl = $this->discoverSnapshotUrl($sourceUrl);
            if ($snapshotUrl) {
                $bytes = $this->download($snapshotUrl);
                if ($this->isJpeg($bytes)) {
                    if (!is_dir($this->dir($roomId))) {
                        mkdir($this->dir($roomId), 0777, true);
                    }
                    file_put_contents($path, $bytes);
                    file_put_contents($this->dir($roomId) . DIRECTORY_SEPARATOR . 'snapshot.url', $snapshotUrl);
                    return $path;
                }
            }
        }

        return is_file($path) && filesize($path) > 100 ? $path : null;
    }

    public function rewrittenPlaylist(Room $room): ?string
    {
        $sourceUrl = trim((string) $room->live_stream_url);
        if ($sourceUrl === '' || !str_contains(strtolower($sourceUrl), '.m3u8')) {
            return null;
        }

        $cached = $this->readPlaylistCache($room->id, 'player');
        if ($cached !== null) {
            return $cached;
        }

        $body = $this->download($sourceUrl);
        if (!$body || !str_contains($body, '#EXTM3U')) {
            return null;
        }

        // If this is a master/multivariant playlist, drill into the best media
        // sub-playlist so every segment URL can be rewritten through our proxy.
        // Returning the master as-is would leave raw CCTV domain URLs in it,
        // causing the browser to fetch them directly (ERR_NAME_NOT_RESOLVED).
        if (str_contains($body, '#EXT-X-STREAM-INF')) {
            $variantUrl = $this->pickBestVariant($sourceUrl, $body);
            if (!$variantUrl) {
                return null;
            }
            $variantBody = $this->download($variantUrl);
            if (!$variantBody || !str_contains($variantBody, '#EXTM3U')) {
                return null;
            }
            // If variant is itself a master (shouldn't happen but be safe)
            if (str_contains($variantBody, '#EXT-X-STREAM-INF')) {
                return null;
            }
            $body = $variantBody;
            $sourceUrl = $variantUrl;
        }

        $lines = preg_split('/\r\n|\n|\r/', $body) ?: [];
        $header = ['#EXTM3U', '#EXT-X-VERSION:7', '#EXT-X-START:TIME-OFFSET=-1,PRECISE=YES'];
        $target = 2;
        $pairs = [];
        $rawSegments = [];
        $pending = [];
        $mapLine = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, '#EXT-X-TARGETDURATION:')) {
                $target = max(1, (int) substr($line, 22));
                continue;
            }
            if (str_starts_with($line, '#') && !str_starts_with($line, '#EXTINF') && !str_starts_with($line, '#EXT-X-DISCONTINUITY') && !str_starts_with($line, '#EXT-X-KEY') && !str_starts_with($line, '#EXT-X-MAP') && !str_starts_with($line, '#EXT-X-BYTERANGE') && !str_starts_with($line, '#EXT-X-PROGRAM-DATE-TIME') && !str_starts_with($line, '#EXT-X-PART')) {
                continue;
            }
            if (str_starts_with($line, '#EXT-X-MAP')) {
                if (preg_match('/URI="([^"]+)"/', $line, $m)) {
                    $mapSeg = $this->absoluteMediaUrl($sourceUrl, $m[1]);
                    $this->fetchSegment($mapSeg);
                    $line = '#EXT-X-MAP:URI="' . $this->proxiedSegmentUrl($room->id, $mapSeg) . '"';
                    $mapLine = $line;
                }
                continue;
            }
            if (str_starts_with($line, '#')) {
                $pending[] = $line;
                if (str_starts_with($line, '#EXTINF:')) {
                    $inf = (float) substr($line, 8);
                    if ($inf > $target) {
                        $target = (int) ceil($inf);
                    }
                }
                continue;
            }

            $seg = $this->absoluteMediaUrl($sourceUrl, $line);
            $rawSegments[] = $seg;
            $pairs[] = array_merge($pending, [$this->proxiedSegmentUrl($room->id, $seg)]);
            $pending = [];
        }

        $keep = array_slice($pairs, -3);
        if ($keep === []) {
            return null;
        }
        $this->prefetchNewest($rawSegments);

        $header[] = '#EXT-X-TARGETDURATION:' . max(1, $target);
        $header[] = '#EXT-X-MEDIA-SEQUENCE:' . max(0, count($pairs) - count($keep));
        $header[] = '#EXT-X-INDEPENDENT-SEGMENTS';
        if ($mapLine) {
            $header[] = $mapLine;
        }

        $out = implode("\n", $header) . "\n";
        foreach ($keep as $block) {
            $out .= implode("\n", $block) . "\n";
        }

        $this->writePlaylistCache($room->id, 'player', $out);

        return $out;
    }

    public function resolveSegmentUrl(int $roomId, string $url, string $sig): ?string
    {
        if (!hash_equals($this->sign($url), $sig)) {
            return null;
        }

        $room = Room::find($roomId);
        if (!$room || !$room->live_stream_url) {
            return null;
        }

        $allowedHost = strtolower((string) parse_url($room->live_stream_url, PHP_URL_HOST));
        $segHost     = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (!$allowedHost || !$segHost) {
            return null;
        }
        // Allow exact match OR segments served from a subdomain of the same root host
        // (e.g., master at camera.example.com, segments at cdn.example.com)
        $rootAllowed = preg_replace('/^[^.]+\./', '', $allowedHost);
        $rootSeg     = preg_replace('/^[^.]+\./', '', $segHost);
        $hostOk = $allowedHost === $segHost
                  || (strlen($rootAllowed) > 3 && $rootAllowed === $rootSeg);
        if (!$hostOk) {
            return null;
        }

        return $url;
    }

    private function proxiedSegmentUrl(int $roomId, string $segmentUrl): string
    {
        return url('/game/' . $roomId . '/live-seg') . '?' . http_build_query([
            'u' => $segmentUrl,
            's' => $this->sign($segmentUrl),
        ]);
    }

    private function proxiedAdminSegmentUrl(int $roomId, string $segmentUrl): string
    {
        return url('/admin/game-control/' . $roomId . '/live-seg') . '?' . http_build_query([
            'u' => $segmentUrl,
            's' => $this->sign($segmentUrl),
        ]);
    }

    /**
     * Same as rewrittenPlaylist but rewrites segment proxy URLs to use the
     * admin-middleware route (/admin/game-control/{id}/live-seg) so that the
     * admin panel CCTV stream never hits the player auth guard.
     */
    public function rewrittenPlaylistForAdmin(Room $room): ?string
    {
        $sourceUrl = trim((string) $room->live_stream_url);
        if ($sourceUrl === '' || !str_contains(strtolower($sourceUrl), '.m3u8')) {
            return null;
        }

        $cached = $this->readPlaylistCache($room->id, 'admin');
        if ($cached !== null) {
            return $cached;
        }

        $body = $this->download($sourceUrl);
        if (!$body || !str_contains($body, '#EXTM3U')) {
            return null;
        }

        if (str_contains($body, '#EXT-X-STREAM-INF')) {
            $variantUrl = $this->pickBestVariant($sourceUrl, $body);
            if (!$variantUrl) {
                return null;
            }
            $variantBody = $this->download($variantUrl);
            if (!$variantBody || !str_contains($variantBody, '#EXTM3U')) {
                return null;
            }
            if (str_contains($variantBody, '#EXT-X-STREAM-INF')) {
                return null;
            }
            $body      = $variantBody;
            $sourceUrl = $variantUrl;
        }

        $lines   = preg_split('/\r\n|\n|\r/', $body) ?: [];
        $header  = ['#EXTM3U', '#EXT-X-VERSION:7', '#EXT-X-START:TIME-OFFSET=-1,PRECISE=YES'];
        $target  = 2;
        $pairs   = [];
        $rawSegments = [];
        $pending = [];
        $mapLine = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, '#EXT-X-TARGETDURATION:')) {
                $target = max(1, (int) substr($line, 22));
                continue;
            }
            if (str_starts_with($line, '#') && !str_starts_with($line, '#EXTINF') && !str_starts_with($line, '#EXT-X-DISCONTINUITY') && !str_starts_with($line, '#EXT-X-KEY') && !str_starts_with($line, '#EXT-X-MAP') && !str_starts_with($line, '#EXT-X-BYTERANGE') && !str_starts_with($line, '#EXT-X-PROGRAM-DATE-TIME') && !str_starts_with($line, '#EXT-X-PART')) {
                continue;
            }
            if (str_starts_with($line, '#EXT-X-MAP')) {
                if (preg_match('/URI="([^"]+)"/', $line, $m)) {
                    $mapSeg = $this->absoluteMediaUrl($sourceUrl, $m[1]);
                    $this->fetchSegment($mapSeg);
                    $line = '#EXT-X-MAP:URI="' . $this->proxiedAdminSegmentUrl($room->id, $mapSeg) . '"';
                    $mapLine = $line;
                }
                continue;
            }
            if (str_starts_with($line, '#')) {
                $pending[] = $line;
                if (str_starts_with($line, '#EXTINF:')) {
                    $inf = (float) substr($line, 8);
                    if ($inf > $target) {
                        $target = (int) ceil($inf);
                    }
                }
                continue;
            }

            $seg = $this->absoluteMediaUrl($sourceUrl, $line);
            $rawSegments[] = $seg;
            $pairs[]  = array_merge($pending, [$this->proxiedAdminSegmentUrl($room->id, $seg)]);
            $pending  = [];
        }

        $keep = array_slice($pairs, -3);
        if ($keep === []) {
            return null;
        }
        $this->prefetchNewest($rawSegments);

        $header[] = '#EXT-X-TARGETDURATION:' . max(1, $target);
        $header[] = '#EXT-X-MEDIA-SEQUENCE:' . max(0, count($pairs) - count($keep));
        $header[] = '#EXT-X-INDEPENDENT-SEGMENTS';
        if ($mapLine) {
            $header[] = $mapLine;
        }

        $out = implode("\n", $header) . "\n";
        foreach ($keep as $block) {
            $out .= implode("\n", $block) . "\n";
        }

        $this->writePlaylistCache($room->id, 'admin', $out);

        return $out;
    }


    private function sign(string $url): string
    {
        return hash_hmac('sha256', $url, (string) config('app.key'));
    }

    /**
     * Parse a master HLS playlist and return the URL of the highest-bandwidth
     * (or first available) media sub-playlist.
     */
    private function pickBestVariant(string $masterUrl, string $masterBody): ?string
    {
        $lines = preg_split('/\r\n|\n|\r/', $masterBody) ?: [];
        $bestBandwidth = -1;
        $bestUrl = null;
        $pendingBandwidth = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, '#EXT-X-STREAM-INF')) {
                $pendingBandwidth = 0;
                if (preg_match('/BANDWIDTH=(\d+)/i', $line, $m)) {
                    $pendingBandwidth = (int) $m[1];
                }
                continue;
            }
            if ($pendingBandwidth !== null && !str_starts_with($line, '#')) {
                $url = $this->absoluteMediaUrl($masterUrl, $line);
                if ($pendingBandwidth > $bestBandwidth) {
                    $bestBandwidth = $pendingBandwidth;
                    $bestUrl = $url;
                }
                $pendingBandwidth = null;
            }
        }

        // Fallback: first non-comment line if no BANDWIDTH tags found
        if ($bestUrl === null) {
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '' && !str_starts_with($line, '#')) {
                    $bestUrl = $this->absoluteMediaUrl($masterUrl, $line);
                    break;
                }
            }
        }

        return $bestUrl;
    }

    private function discoverSnapshotUrl(string $streamUrl): ?string
    {
        $parts = parse_url($streamUrl);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        $dir = isset($parts['path']) ? rtrim(dirname($parts['path']), '/') : '';
        $user = $parts['user'] ?? null;
        $pass = $parts['pass'] ?? null;
        $auth = ($user !== null) ? rawurlencode($user) . ':' . rawurlencode((string) $pass) . '@' : '';
        $originAuth = $parts['scheme'] . '://' . $auth . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');

        $candidates = array_unique([
            preg_replace('/\.m3u8(\?.*)?$/i', '/snapshot.jpg', $streamUrl) ?: '',
            preg_replace('/\.m3u8(\?.*)?$/i', '.jpg', $streamUrl) ?: '',
            $originAuth . $dir . '/snapshot.jpg',
            $originAuth . $dir . '/latest.jpg',
            $originAuth . $dir . '/preview.jpg',
            $originAuth . '/snapshot.jpg',
            $originAuth . '/cgi-bin/snapshot.cgi',
            $originAuth . '/cgi-bin/snapshot.cgi?1',
            $originAuth . '/axis-cgi/jpg/image.cgi',
            $originAuth . '/ISAPI/Streaming/channels/101/picture',
            $originAuth . '/ISAPI/Streaming/channels/1/picture',
            $originAuth . '/onvifsnapshot/media_service/snapshot',
            $originAuth . '/jpg/image.jpg',
            $originAuth . '/image.jpg',
            $origin . $dir . '/snapshot.jpg',
        ]);

        foreach ($candidates as $candidate) {
            if (!$candidate || !preg_match('#^https?://#i', $candidate)) {
                continue;
            }
            $bytes = $this->download($candidate);
            if ($this->isJpeg($bytes)) {
                return $candidate;
            }
        }

        return null;
    }

    private function spawnSnapshotLoop(int $roomId, string $snapshotUrl, string $outFile): void
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            $bytes = $this->download($snapshotUrl);
            if ($this->isJpeg($bytes)) {
                file_put_contents($outFile, $bytes);
            }
            return;
        }

        $script = 'while true; do curl -sS -L --max-time 2 -o ' . escapeshellarg($outFile . '.tmp') . ' ' . escapeshellarg($snapshotUrl) . ' && mv -f ' . escapeshellarg($outFile . '.tmp') . ' ' . escapeshellarg($outFile) . '; sleep 0.12; done';
        $cmd = 'nohup bash -c ' . escapeshellarg($script) . ' >/dev/null 2>&1 & echo $!';
        $pid = trim((string) shell_exec($cmd));
        if ($pid !== '') {
            file_put_contents($this->dir($roomId) . DIRECTORY_SEPARATOR . 'loop.pid', $pid);
        }
    }

    private function spawnFfmpegPreview(int $roomId, string $sourceUrl, string $outFile): void
    {
        $ffmpeg = trim((string) shell_exec('command -v ffmpeg 2>/dev/null'));
        if ($ffmpeg === '' || strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            return;
        }

        $cmd = 'nohup ' . escapeshellarg($ffmpeg)
            . ' -nostdin -hide_banner -loglevel error'
            . ' -fflags nobuffer+discardcorrupt -flags low_delay -probesize 32768 -analyzeduration 0'
            . ' -i ' . escapeshellarg($sourceUrl)
            . ' -an -vf fps=8 -q:v 5 -f image2 -update 1 ' . escapeshellarg($outFile)
            . ' >>/var/www/online_gaming/storage/logs/ffmpeg-php.log 2>&1 & echo $!';
        $pid = trim((string) shell_exec($cmd));
        if ($pid !== '') {
            file_put_contents($this->dir($roomId) . DIRECTORY_SEPARATOR . 'ffmpeg.pid', $pid);
        }
    }

    private function download(string $url): ?string
    {
        try {
            $response = Http::timeout(8)
                ->connectTimeout(4)
                ->withOptions([
                    'verify' => false,
                    'allow_redirects' => true,
                    'cookies' => $this->cookieJar($url),
                ])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'Accept' => '*/*',
                ])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function isJpeg(?string $bytes): bool
    {
        return is_string($bytes) && strlen($bytes) > 100 && str_starts_with($bytes, "\xFF\xD8");
    }

    private function dir(int $roomId): string
    {
        return storage_path('app/live/' . $roomId);
    }

    private function playlistCachePath(int $roomId, string $which): string
    {
        return $this->dir($roomId) . DIRECTORY_SEPARATOR . 'playlist-' . $which . '.m3u8';
    }

    /** Serve the last good playlist for about 1s so overlapping players do not stampede the camera. */
    private function readPlaylistCache(int $roomId, string $which): ?string
    {
        $path = $this->playlistCachePath($roomId, $which);
        if (!is_file($path)) {
            return null;
        }
        if ((microtime(true) - filemtime($path)) > 1.2) {
            return null;
        }
        $cached = @file_get_contents($path);
        if (!is_string($cached) || !str_contains($cached, '#EXTINF')) {
            return null;
        }

        return $cached;
    }

    private function writePlaylistCache(int $roomId, string $which, string $playlist): void
    {
        $dir = $this->dir($roomId);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        @file_put_contents($this->playlistCachePath($roomId, $which), $playlist);
    }

    private function absoluteMediaUrl(string $playlistUrl, string $ref): string
    {
        $ref = trim($ref);
        if (preg_match('#^https?://#i', $ref)) {
            return $ref;
        }

        $parts = parse_url($playlistUrl) ?: [];
        $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
        if (!empty($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }
        $dir = isset($parts['path']) ? (preg_replace('#/[^/]*$#', '/', $parts['path']) ?: '/') : '/';
        $abs = str_starts_with($ref, '/') ? $origin . $ref : $origin . $dir . $ref;
        if (!str_contains($ref, '?') && !empty($parts['query'])) {
            $abs .= '?' . $parts['query'];
        }

        return $abs;
    }

    /** Download the newest segment while building the playlist so the first player request is already cached. */
    private function prefetchNewest(array $rawSegments): void
    {
        $latest = $rawSegments === [] ? null : $rawSegments[count($rawSegments) - 1];
        if (is_string($latest) && $latest !== '') {
            $this->fetchSegment($latest);
        }
    }

    public function fetchSegment(string $url, ?string $range = null): ?array
    {
        $cacheKey = substr(hash('sha256', $url), 0, 24);
        $cacheFile = storage_path('app/live/segs/' . $cacheKey . '.bin');
        $typeFile = $cacheFile . '.type';
        if ($range === null && is_file($cacheFile) && filesize($cacheFile) > 32 && (time() - filemtime($cacheFile)) < 30) {
            $type = is_file($typeFile) ? trim((string) file_get_contents($typeFile)) : $this->guessSegmentType($url, null);

            return ['body' => (string) file_get_contents($cacheFile), 'type' => $type, 'status' => 200];
        }

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept' => '*/*',
            'Referer' => $this->originOf($url) . '/',
        ];
        if ($range) {
            $headers['Range'] = $range;
        }

        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $response = Http::timeout(12)
                    ->connectTimeout(4)
                    ->withOptions([
                        'verify' => false,
                        'allow_redirects' => true,
                        'cookies' => $this->cookieJar($url),
                    ])
                    ->withHeaders($headers)
                    ->get($url);
                $status = $response->status();
                $body = $response->body();
                if ($status >= 200 && $status < 300 && $body !== '') {
                    $type = $this->guessSegmentType($url, $response->header('Content-Type'));
                    if ($status === 200 && $range === null) {
                        $dir = dirname($cacheFile);
                        if (!is_dir($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        file_put_contents($cacheFile, $body);
                        file_put_contents($typeFile, $type);
                    }

                    return ['body' => $body, 'type' => $type, 'status' => $status];
                }
            } catch (\Throwable $e) {
                // retry once
            }
            usleep(150000);
        }

        return null;
    }

    private function originOf(string $url): string
    {
        $parts = parse_url($url) ?: [];
        $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
        if (!empty($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }

        return $origin;
    }

    private function cookieJar(string $url): \GuzzleHttp\Cookie\FileCookieJar
    {
        $host = preg_replace('/[^a-z0-9.-]/i', '_', (string) parse_url($url, PHP_URL_HOST));
        $dir = storage_path('app/live/cookies');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return new \GuzzleHttp\Cookie\FileCookieJar($dir . DIRECTORY_SEPARATOR . ($host ?: 'camera') . '.txt', true);
    }

    private function guessSegmentType(string $url, ?string $header): string
    {
        if (is_string($header) && $header !== '' && stripos($header, 'text/html') === false && stripos($header, 'application/xml') === false) {
            return trim(explode(';', $header)[0]);
        }
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        if (str_ends_with($path, '.mp4') || str_ends_with($path, '.m4s') || str_ends_with($path, '.cmfv')) {
            return 'video/mp4';
        }
        if (str_ends_with($path, '.aac')) {
            return 'audio/aac';
        }
        if (str_ends_with($path, '.vtt')) {
            return 'text/vtt';
        }

        return 'video/mp2t';
    }
}
