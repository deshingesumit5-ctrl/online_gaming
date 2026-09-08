<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\GameRound;
use App\Models\Room;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminGameController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public function roomsList(): View
    {
        $rooms = Room::with(['game'])
            ->withCount(['gameRounds'])
            ->orderBy('id', 'asc')
            ->get();

        foreach ($rooms as $room) {
            $latestRound = GameRound::where('room_id', $room->id)->latest()->first();
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
        }

        return view('admin.game-control-rooms', compact('rooms'));
    }

    public function controlPanel(int $roomId): View
    {
        $room = Room::with(['game'])->findOrFail($roomId);
        $allRooms = Room::all(['id', 'name', 'status']);
        $currentRound = GameRound::where('room_id', $roomId)->latest()->first();

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
            ->latest()
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
            'cardDeck'
        ));
    }

    public function handleAction(Request $request, int $roomId): JsonResponse|RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:start_round,open_betting,close_betting,declare_result,create_new_round,update_stream,update_room_timings,start_stream,end_stream'],
            'first_card' => ['nullable', 'string'],
            'winning_side' => ['nullable', 'in:andar,bahar'],
            'live_stream_url' => ['nullable', 'string', 'max:500'],
            'betting_duration' => ['nullable', 'integer', 'min:5', 'max:300'],
            'cancellation_duration' => ['nullable', 'integer', 'min:0', 'max:300'],
        ]);

        $room = Room::findOrFail($roomId);
        $action = $request->action;

        if ($action === 'start_stream') {
            $room->update(['is_streaming' => true]);
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'is_streaming' => true, 'message' => 'Live stream broadcast started.']);
            }
            return back()->with('success', 'Live stream broadcast started.');
        }

        if ($action === 'end_stream') {
            $room->update(['is_streaming' => false]);
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
                'started_at' => now(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Round #{$currentRound->round_number} started with card {$firstCard}."]);
            }
            return back()->with('success', "Round #{$currentRound->round_number} started with card {$firstCard}.");
        }

        if ($action === 'open_betting') {
            $duration = $room->betting_duration ?: 30;
            $currentRound->update([
                'status' => 'betting_open',
                'betting_ends_at' => now()->addSeconds($duration),
            ]);

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
            $currentRound->update([
                'status' => 'betting_closed',
            ]);

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
                    return response()->json(['success' => false, 'message' => 'Result has already been declared for this round.'], 422);
                }
                return back()->with('error', 'Result has already been declared for this round.');
            }

            // Run Settlement Engine
            DB::transaction(function () use ($currentRound, $winningSide) {
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
                        // 1:1 Return (Original Bet + Equal Winning Profit = 2x Bet Amount)
                        $payout = (int) round((float) $bet->amount * 2);
                        $bet->status = 'won';
                        $bet->payout_amount = $payout;
                        $bet->save();

                        // Credit Player Wallet using WalletService
                        $player = User::findOrFail($bet->user_id);
                        $this->walletService->addWinnings(
                            user: $player,
                            amount: $payout,
                            remarks: "Won " . strtoupper($winningSide) . " in Round #{$currentRound->round_number} (1:1 payout on {$bet->amount} pts)",
                            referenceType: Bet::class,
                            referenceId: $bet->id,
                            performedByAdminId: auth()->id()
                        );

                        // Send Game Result won notification
                        try {
                            app(\App\Services\NotificationService::class)->sendToUser(
                                user: $player,
                                type: 'game_result',
                                title: 'Game Result',
                                message: strtoupper($winningSide) . " WON. You won " . number_format($payout) . " points.",
                                link: route('dashboard')
                            );
                        } catch (\Throwable $e) {
                        }
                    } else {
                        // Lost bet
                        $bet->status = 'lost';
                        $bet->payout_amount = 0.00;
                        $bet->save();

                        // Send Game Result notification
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

                // Notify admin of result declaration
                try {
                    app(\App\Services\NotificationService::class)->sendToAdmin(
                        type: 'admin_game_result',
                        title: 'Game Result Settled',
                        message: "Round #{$currentRound->round_number} settled: " . strtoupper($winningSide) . " WON.",
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
            GameRound::create([
                'room_id' => $roomId,
                'round_number' => $newRoundNumber,
                'status' => 'open',
                'first_card' => null,
            ]);

            return back()->with('success', "New Round #{$newRoundNumber} created and ready.");
        }

        return back();
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
}
