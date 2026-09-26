<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\BettingWindow;
use App\Models\GameRound;
use App\Models\Room;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\AuditLogService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminGameController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public static function ensureSchema(): void
    {
        try {
            if (!Schema::hasTable('betting_windows') || 
                !Schema::hasTable('audit_logs') || 
                !Schema::hasColumn('game_rounds', 'payout_mode') ||
                !Schema::hasColumn('bets', 'betting_window_id')) {
                
                try {
                    Artisan::call('migrate', ['--force' => true]);
                } catch (\Throwable $e) {
                    Log::warning('Artisan migrate call in ensureSchema: ' . $e->getMessage());
                }

                if (!Schema::hasTable('betting_windows')) {
                    Schema::create('betting_windows', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->id();
                        $table->foreignId('game_round_id')->constrained('game_rounds')->cascadeOnDelete();
                        $table->unsignedInteger('window_number')->default(1);
                        $table->string('status', 20)->default('open');
                        $table->timestamp('started_at')->nullable();
                        $table->timestamp('ended_at')->nullable();
                        $table->timestamps();
                    });
                }

                if (!Schema::hasTable('audit_logs')) {
                    Schema::create('audit_logs', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->id();
                        $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
                        $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
                        $table->foreignId('game_round_id')->nullable()->constrained('game_rounds')->nullOnDelete();
                        $table->string('action', 80);
                        $table->text('previous_state')->nullable();
                        $table->text('new_state')->nullable();
                        $table->timestamps();
                    });
                }

                Schema::table('game_rounds', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!Schema::hasColumn('game_rounds', 'payout_mode')) {
                        $table->unsignedTinyInteger('payout_mode')->nullable()->after('winning_side');
                    }
                    if (!Schema::hasColumn('game_rounds', 'first_card_matched')) {
                        $table->boolean('first_card_matched')->nullable()->after('payout_mode');
                    }
                    if (!Schema::hasColumn('game_rounds', 'payout_locked')) {
                        $table->boolean('payout_locked')->default(false)->after('first_card_matched');
                    }
                });

                Schema::table('bets', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!Schema::hasColumn('bets', 'betting_window_id')) {
                        $table->foreignId('betting_window_id')->nullable()->after('game_round_id');
                    }
                    if (!Schema::hasColumn('bets', 'cancelled_at')) {
                        $table->timestamp('cancelled_at')->nullable()->after('status');
                    }
                    if (!Schema::hasColumn('bets', 'profit_amount')) {
                        $table->decimal('profit_amount', 12, 2)->default(0)->after('payout_amount');
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Schema self-healing notice: ' . $e->getMessage());
        }
    }

    public function roomsList(): View
    {
        self::ensureSchema();

        $rooms = Room::with(['game'])
            ->withCount(['gameRounds'])
            ->orderBy('id', 'asc')
            ->get();

        foreach ($rooms as $room) {
            try {
                $latestRound = GameRound::where('room_id', $room->id)->latest('id')->first();
                $room->latest_round = $latestRound;
                if ($latestRound) {
                    $room->active_bets_count = Bet::where('game_round_id', $latestRound->id)
                        ->where('status', '!=', 'cancelled')
                        ->count();
                    $room->active_bets_pool = Bet::where('game_round_id', $latestRound->id)
                        ->where('status', '!=', 'cancelled')
                        ->sum('amount');
                } else {
                    $room->active_bets_count = 0;
                    $room->active_bets_pool = 0;
                }
            } catch (\Throwable $e) {
                $room->latest_round = null;
                $room->active_bets_count = 0;
                $room->active_bets_pool = 0;
            }
        }

        return view('admin.game-control-rooms', compact('rooms'));
    }

    public function controlPanel(int $roomId): View
    {
        self::ensureSchema();

        $room = Room::with(['game'])->findOrFail($roomId);
        $allRooms = Room::all(['id', 'name', 'status']);
        $currentRound = GameRound::where('room_id', $roomId)->latest('id')->first();

        if (!$currentRound) {
            $currentRound = GameRound::create([
                'room_id' => $roomId,
                'round_number' => 1,
                'status' => 'open',
                'first_card' => null,
            ]);
        }

        $activeBets = Bet::with('user')
            ->where('game_round_id', $currentRound->id)
            ->where('status', '!=', 'cancelled')
            ->latest('id')
            ->get();

        $andarBets = $activeBets->where('selection', 'andar');
        $baharBets = $activeBets->where('selection', 'bahar');

        $totalAndarAmount = $andarBets->sum('amount');
        $totalBaharAmount = $baharBets->sum('amount');

        $recentRounds = GameRound::where('room_id', $roomId)
            ->whereIn('status', ['result_declared', 'round_closed'])
            ->latest('id')
            ->take(8)
            ->get();

        try {
            $bettingWindows = $currentRound->bettingWindows()->orderBy('window_number')->get();
        } catch (\Throwable $e) {
            $bettingWindows = collect();
        }

        try {
            $currentWindow = $currentRound->currentBettingWindow();
        } catch (\Throwable $e) {
            $currentWindow = null;
        }

        try {
            $auditLogs = \App\Models\AuditLog::where('game_round_id', $currentRound->id)
                ->latest('id')
                ->take(12)
                ->get();
        } catch (\Throwable $e) {
            $auditLogs = collect();
        }

        $sessionWinners = Bet::where('game_round_id', $currentRound->id)->where('status', 'won')->count();
        $sessionLosers = Bet::where('game_round_id', $currentRound->id)->where('status', 'lost')->count();
        $sessionProcessed = (float) Bet::where('game_round_id', $currentRound->id)->whereIn('status', ['won', 'lost'])->sum('amount');

        $cardDeck = $this->getCardDeck();

        return view('admin.game-control', compact(
            'room',
            'allRooms',
            'currentRound',
            'activeBets',
            'andarBets',
            'baharBets',
            'totalAndarAmount',
            'totalBaharAmount',
            'recentRounds',
            'cardDeck',
            'bettingWindows',
            'currentWindow',
            'auditLogs',
            'sessionWinners',
            'sessionLosers',
            'sessionProcessed'
        ));
    }

    public function handleAction(Request $request, int $roomId): JsonResponse|RedirectResponse
    {
        self::ensureSchema();

        $request->validate([
            'action' => ['required', 'in:start_round,open_betting,close_betting,declare_result,create_new_round,update_stream,update_room_timings,start_stream,end_stream,update_first_card,hide_card_overlay,confirm_first_card'],
            'first_card' => ['nullable', 'string'],
            'winning_side' => ['nullable', 'in:andar,bahar'],
            'first_card_matched' => ['nullable', 'boolean'],
            'live_stream_url' => ['nullable', 'string', 'max:500'],
            'betting_duration' => ['nullable', 'integer', 'min:5', 'max:300'],
            'cancellation_duration' => ['nullable', 'integer', 'min:0', 'max:300'],
            'x' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'y' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'scale' => ['nullable', 'numeric', 'min:0.2', 'max:3'],
        ]);

        $room = Room::findOrFail($roomId);
        $action = $request->action;

        if ($action === 'start_stream') {
            $room->update(['is_streaming' => true]);
            try {
                app(\App\Services\LowLatencyStreamService::class)->start($room);
            } catch (\Throwable $e) {
            }
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'is_streaming' => true, 'message' => 'Live stream broadcast started.']);
            }
            return back()->with('success', 'Live stream broadcast started.');
        }

        if ($action === 'end_stream') {
            $room->update(['is_streaming' => false]);
            \Illuminate\Support\Facades\Cache::forget("room_pen_position_{$roomId}");
            try {
                app(\App\Services\LowLatencyStreamService::class)->stop($room);
            } catch (\Throwable $e) {
            }
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'is_streaming' => false, 'message' => 'Live stream broadcast ended.']);
            }
            return back()->with('success', 'Live stream broadcast ended.');
        }

        if ($action === 'update_room_timings') {
            $room->update([
                'betting_duration' => $request->betting_duration ?? 30,
                'cancellation_duration' => $request->cancellation_duration ?? 30,
            ]);
            return back()->with('success', 'Betting and Cancellation timings updated successfully.');
        }

        if ($action === 'update_stream') {
            $room->update(['live_stream_url' => $request->live_stream_url]);
            return back()->with('success', 'Stream URL updated successfully.');
        }

        $currentRound = GameRound::where('room_id', $roomId)->latest()->first();

        if (!$currentRound) {
            $currentRound = GameRound::create([
                'room_id' => $roomId,
                'round_number' => 1,
                'status' => 'open',
            ]);
        }

        if ($action === 'start_round') {
            $firstCard = $request->first_card ?: 'king_hearts';
            $currentRound->update([
                'first_card' => $firstCard,
                'status' => 'open',
                'started_at' => $currentRound->started_at ?: now(),
            ]);

            AuditLogService::record('Session Started', $currentRound, null, 'open', $roomId);

            \Illuminate\Support\Facades\Cache::forget("room_card_overlay_{$roomId}");

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Round #{$currentRound->round_number} started with card {$firstCard}."]);
            }
            return back()->with('success', "Round #{$currentRound->round_number} started with card {$firstCard}.");
        }

        if ($action === 'hide_card_overlay') {
            $this->hideCardOverlay($roomId);
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Card overlay removed from live camera.']);
            }
            return back()->with('success', 'Card overlay removed from live camera.');
        }

        if ($action === 'update_first_card') {
            $firstCard = $request->first_card;
            if (!$firstCard) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Card value is required.'], 422);
                }
                return back()->with('error', 'Card value is required.');
            }

            $currentRound->update([
                'first_card' => $firstCard,
            ]);

            $this->storeCardOverlay($roomId, $firstCard, $request->input('x'), $request->input('y'), $request->input('scale'));

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'first_card' => $firstCard,
                    'message' => 'First card updated to ' . strtoupper(str_replace('_', ' ', $firstCard)) . '.',
                ]);
            }
            return back()->with('success', 'First card updated.');
        }

        if ($action === 'confirm_first_card') {
            if ($currentRound->payout_locked) {
                $msg = 'Payout mode is already locked for this session.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg);
            }

            $matched = filter_var($request->input('first_card_matched'), FILTER_VALIDATE_BOOLEAN);
            $mode = $matched ? 25 : 100;
            $prev = $currentRound->payoutLabel();
            $currentRound->update([
                'first_card_matched' => $matched,
                'payout_mode' => $mode,
                'payout_locked' => true,
            ]);
            AuditLogService::record(
                $matched ? 'First Card Matched' : 'First Card Not Matched',
                $currentRound,
                $prev,
                $mode . '% profit locked',
                $roomId
            );

            $msg = $matched
                ? 'First card matched. Session payout locked at 25% profit.'
                : 'First card not matched. Session payout locked at 100% profit.';
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'payout_mode' => $mode, 'message' => $msg]);
            }
            return back()->with('success', $msg);
        }

        if ($action === 'open_betting') {
            if (in_array($currentRound->status, ['result_declared', 'round_closed'], true)) {
                $msg = 'This session is completed. Start the next session before opening betting.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg);
            }

            $duration = $room->betting_duration ?: 10;
            $openWindow = $currentRound->bettingWindows()->where('status', 'open')->latest('id')->first();
            if ($openWindow) {
                $openWindow->update(['status' => 'closed', 'ended_at' => now()]);
            }
            $nextNumber = ((int) $currentRound->bettingWindows()->max('window_number')) + 1;
            $window = BettingWindow::create([
                'game_round_id' => $currentRound->id,
                'window_number' => max(1, $nextNumber),
                'status' => 'open',
                'started_at' => now(),
            ]);

            $currentRound->update([
                'status' => 'betting_open',
                'betting_ends_at' => now()->addSeconds($duration),
                'started_at' => $currentRound->started_at ?: now(),
            ]);

            AuditLogService::record('Betting Open', $currentRound, 'closed', 'window #' . $window->window_number . ' open', $roomId);

            // Dispatch real dynamic notification to all players
            try {
                $roomName = $room->name ?: ("ROOM " . $room->id);
                app(\App\Services\NotificationService::class)->sendToPlayers(
                    type: 'betting_open',
                    title: "{$roomName} Game starts",
                    message: "{$roomName} Game starts, You can place bet now!",
                    link: route('game.play', ['roomId' => $room->id]),
                    icon: '🎲'
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to broadcast open betting notification: " . $e->getMessage());
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Betting window opened for {$duration} seconds."]);
            }
            return back()->with('success', "Betting window opened for {$duration} seconds.");
        }

        if ($action === 'close_betting') {
            $openWindow = $currentRound->bettingWindows()->where('status', 'open')->latest('id')->first();
            if ($openWindow) {
                $openWindow->update(['status' => 'closed', 'ended_at' => now()]);
            }
            $currentRound->update([
                'status' => 'betting_closed',
            ]);
            AuditLogService::record('Betting Closed', $currentRound, 'betting_open', 'betting_closed', $roomId);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Betting closed.']);
            }
            return back()->with('success', 'Betting closed.');
        }

        if ($action === 'declare_result') {
            $winningSide = $request->winning_side;

            if (!in_array($winningSide, ['andar', 'bahar'])) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please choose a valid winning side (Andar or Bahar).'], 422);
                }
                return back()->with('error', 'Please choose a valid winning side (Andar or Bahar).');
            }

            if ($currentRound->status === 'result_declared' || $currentRound->status === 'round_closed') {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Result has already been declared for this session.'], 422);
                }
                return back()->with('error', 'Result has already been declared for this session.');
            }

            if (!$currentRound->payout_locked || !$currentRound->payout_mode) {
                $msg = 'Confirm first card condition (25% or 100%) before declaring the result.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg);
            }

            $openWindow = $currentRound->bettingWindows()->where('status', 'open')->latest('id')->first();
            if ($openWindow) {
                $openWindow->update(['status' => 'closed', 'ended_at' => now()]);
            }

            // Run Settlement Engine
            DB::transaction(function () use ($currentRound, $winningSide, $roomId) {
                $currentRound->update([
                    'status' => 'result_declared',
                    'winning_side' => $winningSide,
                    'closed_at' => now(),
                ]);

                $bets = Bet::where('game_round_id', $currentRound->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->get();

                foreach ($bets as $bet) {
                    if ($bet->selection === $winningSide) {
                        $stake = (float) $bet->amount;
                        $payout = $currentRound->totalReturnForBet($stake);
                        $profit = $currentRound->profitForBet($stake);
                        $bet->status = 'won';
                        $bet->payout_amount = $payout;
                        $bet->profit_amount = $profit;
                        $bet->save();

                        $player = User::findOrFail($bet->user_id);
                        $this->walletService->addWinnings(
                            user: $player,
                            amount: $payout,
                            remarks: "Won " . strtoupper($winningSide) . " session #{$currentRound->round_number} ({$currentRound->payout_mode}% profit on {$bet->amount} pts)",
                            referenceType: Bet::class,
                            referenceId: $bet->id,
                            performedByAdminId: auth()->id()
                        );

                        try {
                            app(\App\Services\NotificationService::class)->sendToUser(
                                user: $player,
                                type: 'game_result',
                                title: 'Game Result',
                                message: strtoupper($winningSide) . " WON. You won " . number_format($profit) . " points profit. Total return " . number_format($payout) . " points.",
                                link: route('dashboard')
                            );
                        } catch (\Throwable $e) {
                        }
                    } else {
                        $bet->status = 'lost';
                        $bet->payout_amount = 0.00;
                        $bet->profit_amount = 0.00;
                        $bet->save();

                        try {
                            app(\App\Services\NotificationService::class)->sendToUser(
                                user: $bet->user_id,
                                type: 'game_result',
                                title: 'Game Result',
                                message: strtoupper($winningSide) . " WON.",
                                link: route('dashboard')
                            );
                        } catch (\Throwable $e) {
                        }
                    }
                }

                AuditLogService::record('Result Declared – ' . strtoupper($winningSide), $currentRound, 'active', $winningSide . ' / ' . $currentRound->payout_mode . '%', $roomId);
                AuditLogService::record('Payout Processed', $currentRound, null, 'wallets updated', $roomId);
                AuditLogService::record('Session Completed', $currentRound, 'active', 'result_declared', $roomId);

                try {
                    app(\App\Services\NotificationService::class)->sendToAdmin(
                        type: 'admin_game_result',
                        title: 'Game Result Settled',
                        message: "Session #{$currentRound->round_number} settled: " . strtoupper($winningSide) . " WON ({$currentRound->payout_mode}%).",
                        link: route('admin.game.control.index')
                    );
                } catch (\Throwable $e) {
                }
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Result declared: " . strtoupper($winningSide) . " WON! Payouts credited automatically.",
                ]);
            }

            return back()->with('success', "Result declared: " . strtoupper($winningSide) . " WON! Payouts credited automatically.");
        }

        if ($action === 'create_new_round') {
            // Close old round if open
            if ($currentRound->status !== 'result_declared') {
                $currentRound->update(['status' => 'round_closed', 'closed_at' => now()]);
            }

            $newRoundNumber = $currentRound->round_number + 1;
            $newRound = GameRound::create([
                'room_id' => $roomId,
                'round_number' => $newRoundNumber,
                'status' => 'open',
                'first_card' => null,
                'payout_mode' => null,
                'first_card_matched' => null,
                'payout_locked' => false,
                'winning_side' => 'none',
            ]);
            AuditLogService::record('Start Next Session', $newRound, 'completed', 'open', $roomId);

            return back()->with('success', "New Session #{$newRoundNumber} started. Payout condition reset.");
        }

        return back();
    }

    private function hideCardOverlay(int $roomId): void
    {
        $prev = \Illuminate\Support\Facades\Cache::get("room_card_overlay_{$roomId}");
        \Illuminate\Support\Facades\Cache::put("room_card_overlay_{$roomId}", [
            'first_card' => null,
            'hidden' => true,
            'x' => (float) (is_array($prev) ? ($prev['x'] ?? 0.48) : 0.48),
            'y' => (float) (is_array($prev) ? ($prev['y'] ?? 0.58) : 0.58),
            'scale' => (float) (is_array($prev) ? ($prev['scale'] ?? 1) : 1),
            't' => (int) round(microtime(true) * 1000),
        ], 3600);
    }

    private function storeCardOverlay(int $roomId, string $firstCard, $x = null, $y = null, $scale = null): void
    {
        $prev = \Illuminate\Support\Facades\Cache::get("room_card_overlay_{$roomId}");
        \Illuminate\Support\Facades\Cache::put("room_card_overlay_{$roomId}", [
            'first_card' => $firstCard,
            'hidden' => false,
            'x' => $x !== null ? (float) $x : (float) (is_array($prev) ? ($prev['x'] ?? 0.48) : 0.48),
            'y' => $y !== null ? (float) $y : (float) (is_array($prev) ? ($prev['y'] ?? 0.58) : 0.58),
            'scale' => $scale !== null ? (float) $scale : (float) (is_array($prev) ? ($prev['scale'] ?? 1) : 1),
            't' => (int) round(microtime(true) * 1000),
        ], 3600);
    }

    private function getCardDeck(): array
    {
        $suits = ['spades' => '♠', 'hearts' => '♥', 'diamonds' => '♦', 'clubs' => '♣'];
        $values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'jack', 'queen', 'king', 'ace'];
        $deck = [];

        foreach ($suits as $suitKey => $suitSymbol) {
            foreach ($values as $val) {
                $deck[] = [
                    'code' => "{$val}_{$suitKey}",
                    'label' => ucfirst($val) . " of " . ucfirst($suitKey) . " " . $suitSymbol,
                    'color' => in_array($suitKey, ['hearts', 'diamonds']) ? 'text-red-500' : 'text-slate-100',
                ];
            }
        }

        return $deck;
    }

    public function uploadStreamFrame(Request $request, int $roomId): JsonResponse
    {
        $frame = $request->input('frame');
        if ($frame) {
            \Illuminate\Support\Facades\Cache::put("room_stream_frame_{$roomId}", $frame, 15);
        }
        return response()->json(['success' => true]);
    }

    public function updatePenPosition(Request $request, int $roomId): JsonResponse
    {
        $request->validate([
            'x' => ['required', 'numeric', 'min:0', 'max:1'],
            'y' => ['required', 'numeric', 'min:0', 'max:1'],
            'visible' => ['nullable', 'boolean'],
        ]);

        \Illuminate\Support\Facades\Cache::put("room_pen_position_{$roomId}", [
            'x' => (float) $request->input('x'),
            'y' => (float) $request->input('y'),
            'visible' => $request->boolean('visible', true),
            't' => (int) round(microtime(true) * 1000),
        ], 30);

        return response()->json(['success' => true]);
    }

    // -----------------------------------------------------------------------
    // Admin HLS / JPEG Proxy Methods
    // These mirror GameController::livePlaylist / liveSegment / liveJpeg but
    // live under the admin middleware so the admin panel stream works without
    // depending on the player auth guard.
    // -----------------------------------------------------------------------

    public function adminLivePlaylist(int $roomId, \App\Services\LowLatencyStreamService $liveStream)
    {
        $room = Room::findOrFail($roomId);
        $playlist = $liveStream->rewrittenPlaylistForAdmin($room);
        if (!$playlist || !str_contains($playlist, '#EXTM3U')) {
            return response('', 204, [
                'Content-Type'  => 'application/vnd.apple.mpegurl',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }

        return response($playlist, 200, [
            'Content-Type'  => 'application/vnd.apple.mpegurl',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
        ]);
    }

    public function adminLiveSegment(Request $request, int $roomId, \App\Services\LowLatencyStreamService $liveStream)
    {
        $url      = (string) $request->query('u', '');
        $sig      = (string) $request->query('s', '');
        $resolved = $liveStream->resolveSegmentUrl($roomId, $url, $sig);
        if (!$resolved) {
            abort(403);
        }

        $range = $request->headers->get('Range');
        $fetched = $liveStream->fetchSegment($resolved, is_string($range) ? $range : null);
        if (!$fetched) {
            return response('', 404, [
                'Cache-Control' => 'no-store, no-cache, max-age=0',
            ]);
        }

        return response($fetched['body'], $fetched['status'], [
            'Content-Type' => $fetched['type'],
            'Cache-Control' => 'public, max-age=15',
            'Accept-Ranges' => 'bytes',
        ]);
    }

    public function adminLiveJpeg(int $roomId, \App\Services\LowLatencyStreamService $liveStream)
    {
        // Fast path 1: local file fresh within 2 seconds
        $dir  = storage_path('app/live/' . $roomId);
        $path = $dir . DIRECTORY_SEPARATOR . 'latest.jpg';
        if (is_file($path) && filesize($path) > 100 && (time() - filemtime($path)) <= 2) {
            return response()->file($path, [
                'Content-Type'  => 'image/jpeg',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'        => 'no-cache',
            ]);
        }

        // Fast path 2: stored snapshot URL (avoids re-discovery on every call)
        $snapFile = $dir . DIRECTORY_SEPARATOR . 'snapshot.url';
        if (is_file($snapFile)) {
            $snapUrl = trim((string) file_get_contents($snapFile));
            if ($snapUrl !== '') {
                try {
                    $resp = \Illuminate\Support\Facades\Http::timeout(3)
                        ->withOptions(['verify' => false, 'allow_redirects' => true])
                        ->withHeaders(['User-Agent' => 'Fun2WinLive/1.0'])
                        ->get($snapUrl);
                    $bytes = $resp->successful() ? $resp->body() : null;
                    if (is_string($bytes) && strlen($bytes) > 100 && str_starts_with($bytes, "\xFF\xD8")) {
                        if (!is_dir($dir)) mkdir($dir, 0777, true);
                        file_put_contents($path, $bytes);
                        return response($bytes, 200, [
                            'Content-Type'  => 'image/jpeg',
                            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                            'Pragma'        => 'no-cache',
                        ]);
                    }
                } catch (\Throwable $e) {
                    // snapshot URL unreachable — camera is offline
                }
            }
        }

        // No fresh frame available — camera is offline
        return response('', 204, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
