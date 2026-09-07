<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\GameRound;
use App\Models\Room;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $rawTab = $request->query('tab', 'user_reports');
        $tab = match ($rawTab) {
            'users', 'user', 'user_reports' => 'user_reports',
            'games', 'game', 'game_reports' => 'game_reports',
            'points', 'point', 'points_reports' => 'points_reports',
            'withdrawals', 'withdrawal', 'withdrawal_reports' => 'withdrawal_reports',
            default => 'user_reports',
        };

        // Tab 1: User Reports Metrics
        $userMetrics = [
            'total_users' => User::where('role', 'player')->count(),
            'active_users' => User::where('role', 'player')->whereIn('status', ['active', 'approved'])->count(),
            'inactive_users' => User::where('role', 'player')->where('status', 'inactive')->count(),
            'pending_users' => User::where('role', 'player')->where('status', 'pending')->count(),
            'rejected_users' => User::where('role', 'player')->whereIn('status', ['rejected', 'blocked'])->count(),
        ];

        // Tab 2: Game Reports Metrics
        $totalRoundsCount = GameRound::count();
        $gamesPlayedCount = Room::where('status', 'live')->count();
        if ($gamesPlayedCount === 0) {
            $gamesPlayedCount = GameRound::distinct('room_id')->count('room_id');
        }
        $gameMetrics = [
            'games_played' => $gamesPlayedCount,
            'rounds_played' => $totalRoundsCount,
            'total_bets' => Bet::where('status', '!=', 'cancelled')->count(),
            'andar_bets' => Bet::where('selection', 'andar')->where('status', '!=', 'cancelled')->count(),
            'bahar_bets' => Bet::where('selection', 'bahar')->where('status', '!=', 'cancelled')->count(),
        ];

        // Tab 3: Points Reports Metrics
        $pointsAdded = (float) WalletTransaction::whereIn('type', [
            'deposit', 'points_added', 'manual_credit', 'point_request_approved', 'adjustment'
        ])->where('amount', '>', 0)->sum('amount');

        $pointsBet = (float) abs(WalletTransaction::where('type', 'bet_deducted')->sum('amount'));
        if ($pointsBet == 0) {
            $pointsBet = (float) Bet::where('status', '!=', 'cancelled')->sum('amount');
        }

        $pointsWon = (float) WalletTransaction::where('type', 'winning_points_added')->sum('amount');
        if ($pointsWon == 0) {
            $pointsWon = (float) Bet::where('status', 'won')->sum('payout_amount');
        }

        $pointsWithdrawn = (float) abs(WalletTransaction::where('type', 'withdrawal')->sum('amount'));
        if ($pointsWithdrawn == 0) {
            $pointsWithdrawn = (float) Withdrawal::whereIn('status', ['approved', 'processed', 'settled'])->sum('amount_requested');
        }

        $currentOutstandingPoints = (float) User::where('role', 'player')->sum('wallet_balance');

        $pointsMetrics = [
            'points_added' => $pointsAdded,
            'points_bet' => $pointsBet,
            'points_won' => $pointsWon,
            'points_withdrawn' => $pointsWithdrawn,
            'current_outstanding_points' => $currentOutstandingPoints,
        ];

        // Tab 4: Withdrawal Reports Metrics
        $withdrawalMetrics = [
            'pending' => Withdrawal::where('status', 'pending')->count(),
            'approved' => Withdrawal::where('status', 'approved')->count(),
            'rejected' => Withdrawal::where('status', 'rejected')->count(),
            'processed' => Withdrawal::whereIn('status', ['processed', 'settled'])->count(),
        ];

        $search = $request->query('search');

        // Load real database records based on active tab
        $usersData = null;
        $gameRoundsData = null;
        $pointsData = null;
        $withdrawalsData = null;

        if ($tab === 'user_reports') {
            $statusFilter = $request->query('user_status', 'all');
            $userQuery = User::where('role', 'player');

            if ($statusFilter === 'active') {
                $userQuery->whereIn('status', ['active', 'approved']);
            } elseif ($statusFilter === 'inactive') {
                $userQuery->where('status', 'inactive');
            } elseif ($statusFilter === 'pending') {
                $userQuery->where('status', 'pending');
            } elseif ($statusFilter === 'rejected') {
                $userQuery->whereIn('status', ['rejected', 'blocked']);
            }

            if ($search) {
                $userQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('id', $search);
                });
            }

            $usersData = $userQuery->withCount('bets')->latest('created_at')->orderByDesc('id')->paginate(15)->withQueryString();
        } elseif ($tab === 'game_reports') {
            $gameRoundsData = GameRound::with(['room.game'])
                ->withCount([
                    'bets as total_bets_count',
                    'bets as andar_bets_count' => function ($q) {
                        $q->where('selection', 'andar')->where('status', '!=', 'cancelled');
                    },
                    'bets as bahar_bets_count' => function ($q) {
                        $q->where('selection', 'bahar')->where('status', '!=', 'cancelled');
                    },
                ])
                ->withSum(['bets as total_bet_amount' => function ($q) {
                    $q->where('status', '!=', 'cancelled');
                }], 'amount')
                ->withSum(['bets as total_payout_amount' => function ($q) {
                    $q->where('status', 'won');
                }], 'payout_amount')
                ->latest('id')
                ->paginate(15)
                ->withQueryString();
        } elseif ($tab === 'points_reports') {
            $pQuery = WalletTransaction::with(['user', 'performer']);
            if ($search) {
                $pQuery->where(function ($q) use ($search) {
                    $q->where('transaction_id', 'like', "%{$search}%")
                        ->orWhere('transaction_code', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhere('user_id', $search)
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('username', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            }
            $pointsData = $pQuery->latest('id')->paginate(15)->withQueryString();
        } elseif ($tab === 'withdrawal_reports') {
            $wStatus = $request->query('w_status', 'all');
            $wQuery = Withdrawal::with(['user', 'processor']);

            if ($wStatus !== 'all') {
                if ($wStatus === 'processed') {
                    $wQuery->whereIn('status', ['processed', 'settled']);
                } else {
                    $wQuery->where('status', $wStatus);
                }
            }

            if ($search) {
                $wQuery->where(function ($q) use ($search) {
                    $q->where('request_id', 'like', "%{$search}%")
                        ->orWhere('settlement_details', 'like', "%{$search}%")
                        ->orWhere('rejection_remark', 'like', "%{$search}%")
                        ->orWhere('user_id', $search)
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('username', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            }

            $withdrawalsData = $wQuery->latest('id')->paginate(15)->withQueryString();
        }

        return view('admin.reports.index', compact(
            'tab',
            'search',
            'userMetrics',
            'gameMetrics',
            'pointsMetrics',
            'withdrawalMetrics',
            'usersData',
            'gameRoundsData',
            'pointsData',
            'withdrawalsData'
        ));
    }
}
