<?php

namespace App\Http\Controllers;

use App\Http\Requests\BetRequest;
use App\Models\Bet;
use App\Models\GameRound;
use App\Models\Room;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GameController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }
    public function play(int $roomId): View
    {
        $room = Room::with(['game'])->findOrFail($roomId);
        $user = Auth::user();

        // Get latest round or create one if none exists
        $currentRound = GameRound::where('room_id', $roomId)->latest()->first();

        if (!$currentRound) {
            $currentRound = GameRound::create([
                'room_id' => $roomId,
                'round_number' => 1,
                'status' => 'open',
                'first_card' => null,
            ]);
        }

        $denominations = $room->allowed_denominations ?: [500, 1000, 2000, 5000, 10000];

        $recentRounds = GameRound::where('room_id', $roomId)
            ->whereIn('status', ['result_declared', 'round_closed'])
            ->latest('id')
            ->take(10)
            ->get();

        return view('game.play', compact('room', 'currentRound', 'denominations', 'recentRounds', 'user'));
    }

    public function getState(int $roomId): JsonResponse
    {
        $user = Auth::user();
        $room = Room::findOrFail($roomId);
        $currentRound = GameRound::where('room_id', $roomId)->latest()->first();

        if (!$currentRound) {
            return response()->json([
                'status' => 'waiting',
                'round_number' => 0,
                'remaining_seconds' => 0,
                'first_card' => null,
                'winning_side' => 'none',
                'user_bets' => [],
                'wallet_balance' => (float) $user->wallet_balance,
            ]);
        }

        // Auto-close betting window if timer expired
        if ($currentRound->status === 'betting_open' && $currentRound->betting_ends_at && now()->greaterThan($currentRound->betting_ends_at)) {
            $currentRound->update(['status' => 'betting_closed']);
        }

        $userBets = Bet::where('game_round_id', $currentRound->id)
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($bet) use ($room, $currentRound) {
                $secondsSince = now()->diffInSeconds($bet->created_at);
                $remainingCancel = max(0, $room->cancellation_duration - (int)$secondsSince);
                $canCancel = ($bet->status === 'active' && $remainingCancel > 0 && $currentRound->status === 'betting_open');

                return [
                    'id' => $bet->id,
                    'selection' => $bet->selection,
                    'amount' => (float) $bet->amount,
                    'status' => $bet->status,
                    'payout_amount' => (float) $bet->payout_amount,
                    'can_cancel' => $canCancel,
                    'remaining_cancel_seconds' => $remainingCancel,
                ];
            });

        // Calculate total bets on Andar vs Bahar in this round for live odds visualization
        $totalAndar = (float) Bet::where('game_round_id', $currentRound->id)->where('selection', 'andar')->where('status', '!=', 'cancelled')->sum('amount');
        $totalBahar = (float) Bet::where('game_round_id', $currentRound->id)->where('selection', 'bahar')->where('status', '!=', 'cancelled')->sum('amount');

        $recentRounds = GameRound::where('room_id', $roomId)
            ->whereIn('status', ['result_declared', 'round_closed'])
            ->latest('id')
            ->take(12)
            ->get(['id', 'round_number', 'first_card', 'winning_side']);

        // Refresh user to get fresh balance
        $freshUser = User::find($user->id);

        return response()->json([
            'round_id' => $currentRound->id,
            'round_number' => $currentRound->round_number,
            'round_status' => $currentRound->status,
            'first_card' => $currentRound->first_card,
            'winning_side' => $currentRound->winning_side,
            'remaining_seconds' => $currentRound->remainingBettingSeconds(),
            'betting_duration' => $room->betting_duration,
            'cancellation_duration' => (int) $room->cancellation_duration,
            'user_bets' => $userBets,
            'total_andar' => $totalAndar,
            'total_bahar' => $totalBahar,
            'wallet_balance' => (float) $freshUser->wallet_balance,
            'recent_history' => $recentRounds,
            'is_streaming' => (bool) $room->is_streaming,
            'live_stream_url' => $room->live_stream_url,
        ]);
    }

    public function placeBet(BetRequest $request, int $roomId): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $amount = (float) $validated['amount'];
        $selection = $validated['selection'];

        $room = Room::findOrFail($roomId);
        $currentRound = GameRound::where('room_id', $roomId)->latest()->first();

        if (!$currentRound || !$currentRound->isBettingOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Betting is currently closed for this round.',
            ], 422);
        }

        // Deduct points using WalletService and record Bet inside transaction
        try {
            $result = DB::transaction(function () use ($user, $amount, $selection, $currentRound, $room) {
                // Create Bet first
                $bet = Bet::create([
                    'game_round_id' => $currentRound->id,
                    'user_id' => $user->id,
                    'selection' => $selection,
                    'amount' => $amount,
                    'status' => 'active',
                    'payout_amount' => 0,
                ]);

                // Deduct points via WalletService
                $transaction = $this->walletService->deductPoints(
                    user: $user,
                    amount: (int) $amount,
                    remarks: "Placed bet of {$amount} on " . strtoupper($selection) . " (Round #{$currentRound->round_number})",
                    referenceType: Bet::class,
                    referenceId: $bet->id
                );

                // Send Bet Confirmation notification
                try {
                    app(\App\Services\NotificationService::class)->sendToUser(
                        user: $user,
                        type: 'bet_confirmation',
                        title: 'Bet Confirmation',
                        message: "Your " . number_format($amount) . "-point bet on " . strtoupper($selection) . " has been placed successfully.",
                        link: route('dashboard')
                    );
                } catch (\Throwable $e) {
                }

                return [
                    'success' => true,
                    'bet' => $bet,
                    'wallet_balance' => $transaction->balance_after,
                    'cancel_duration' => $room->cancellation_duration,
                    'message' => "Bet of {$amount} points placed on " . strtoupper($selection) . " successfully!",
                ];
            });

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancelBet(int $betId): JsonResponse
    {
        $user = Auth::user();

        try {
            $result = DB::transaction(function () use ($betId, $user) {
                $bet = Bet::with(['round.room'])->where('id', $betId)->where('user_id', $user->id)->lockForUpdate()->first();

                if (!$bet) {
                    throw new Exception('Bet not found.');
                }

                if ($bet->status !== 'active') {
                    throw new Exception('This bet cannot be cancelled anymore.');
                }

                $round = $bet->round;
                if (!$round || !$round->isBettingOpen()) {
                    throw new Exception('Betting has closed. You can no longer cancel this bet.');
                }

                $cancelWindow = $round->room->cancellation_duration ?? 30;
                $secondsElapsed = now()->diffInSeconds($bet->created_at);

                if ($secondsElapsed > $cancelWindow) {
                    throw new Exception("Cancellation window ({$cancelWindow} seconds) has expired.");
                }

                $amount = (int) $bet->amount;
                $bet->status = 'cancelled';
                $bet->save();

                // Refund points using WalletService
                $transaction = $this->walletService->refundPoints(
                    user: $user,
                    amount: $amount,
                    remarks: "Cancelled bet #{$bet->id} and refunded {$amount} points (Round #{$round->round_number})",
                    referenceType: Bet::class,
                    referenceId: $bet->id
                );

                return [
                    'success' => true,
                    'message' => 'Bet cancelled successfully',
                    'wallet_balance' => $transaction->balance_after,
                ];
            });

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function getStreamFrame(int $roomId): JsonResponse
    {
        $frame = \Illuminate\Support\Facades\Cache::get("room_stream_frame_{$roomId}");
        return response()->json(['frame' => $frame]);
    }
}
