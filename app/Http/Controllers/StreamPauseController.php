<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StreamPauseController extends Controller
{
    public function set(Request $request, int $room): JsonResponse
    {
        $paused = $request->boolean('paused');
        Cache::put('stream_paused_' . $room, $paused ? now()->timestamp : 0, now()->addHours(12));

        return response()->json(['paused' => $paused]);
    }

    public function get(int $room): JsonResponse
    {
        $t = (int) Cache::get('stream_paused_' . $room, 0);

        return response()->json(['paused' => $t > 0, 't' => $t])->header('Cache-Control', 'no-store');
    }
}