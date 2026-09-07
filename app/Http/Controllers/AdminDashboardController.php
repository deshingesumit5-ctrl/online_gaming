<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\Game;
use App\Models\GameRound;
use App\Models\PointRequest;
use App\Models\Room;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        // 10 Core KPIs matching specification:
        $totalUsers = User::where('role', 'player')->count();
        $activeUsers = User::where('role', 'player')->whereIn('status', ['active', 'approved'])->count();
        $inactiveUsers = User::where('role', 'player')->whereIn('status', ['inactive', 'blocked', 'rejected'])->count();
        $pendingApprovals = User::where('role', 'player')->where('status', 'pending')->count();

        $activeGames = Game::where('status', 'open')->count();
        $currentRound = GameRound::where('status', 'open')->latest()->first()?->round_number ?? 1;

        $totalBets = Bet::where('status', '!=', 'cancelled')->count();
        $totalPointsBet = (int) Bet::where('status', '!=', 'cancelled')->sum('amount');
        $winningPoints = (int) Bet::where('status', 'won')->sum('payout_amount');
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $pendingWithdrawalsAmount = (int) Withdrawal::where('status', 'pending')->sum('amount_requested');

        // Points Requests
        $pendingPointRequests = PointRequest::where('status', 'pending')->count();

        $activeRooms = Room::with(['game', 'currentRound'])->where('status', 'live')->get();

        $recentPendingUsers = User::where('role', 'player')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentBets = Bet::with(['user', 'round.room'])
            ->latest()
            ->take(8)
            ->get();

        $recentWithdrawals = Withdrawal::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentPointRequests = PointRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'pendingApprovals',
            'activeGames',
            'currentRound',
            'totalBets',
            'totalPointsBet',
            'winningPoints',
            'pendingWithdrawals',
            'pendingWithdrawalsAmount',
            'pendingPointRequests',
            'activeRooms',
            'recentPendingUsers',
            'recentBets',
            'recentWithdrawals',
            'recentPointRequests'
        ));
    }
}
