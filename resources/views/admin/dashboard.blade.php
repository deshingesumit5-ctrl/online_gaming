@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- 10 Core Dashboard KPIs Matching Image 2 Section 29 -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <x-stat-card title="Total Users" :value="number_format($totalUsers)" icon="👥" accent="emerald" />
        <x-stat-card title="Active Users" :value="number_format($activeUsers)" icon="🟢" accent="emerald" />
        <x-stat-card title="Inactive Users" :value="number_format($inactiveUsers)" icon="⚪" accent="purple" />
        <x-stat-card title="Pending Approvals" :value="number_format($pendingApprovals)" icon="⏳" accent="amber" />
        <x-stat-card title="Active Games" :value="number_format($activeGames)" icon="🎮" accent="blue" />
        
        <x-stat-card title="Current Round" :value="'#' . $currentRound" icon="🔄" accent="blue" />
        <x-stat-card title="Total Bets" :value="number_format($totalBets)" icon="🎲" accent="amber" />
        <x-stat-card title="Total Points Bet" :value="number_format($totalPointsBet) . ' PTS'" icon="💰" accent="amber" />
        <x-stat-card title="Winning Points" :value="number_format($winningPoints) . ' PTS'" icon="🏆" accent="emerald" />
        <x-stat-card title="Pending Withdrawals" :value="number_format($pendingWithdrawals)" icon="⚠️" accent="red" />
    </div>

    @if($pendingPointRequests > 0)
        <!-- Alert Banner for Pending Points Requests -->
        <div class="p-4 rounded-2xl bg-amber-950/60 border border-amber-500/50 flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🔔</span>
                <div>
                    <span class="text-xs font-bold text-amber-300 uppercase tracking-wider block">Action Required</span>
                    <span class="text-sm font-semibold text-white">There are <strong>{{ $pendingPointRequests }}</strong> pending user point deposit requests awaiting review.</span>
                </div>
            </div>
            <a href="{{ route('admin.wallet.index') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black uppercase tracking-wider rounded-xl transition shadow-md">
                Review Requests
            </a>
        </div>
    @endif



    <!-- Bottom 2-Column Section: Pending Approvals Queue & Live Bet Monitor -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Pending Users Queue (6 cols) -->
        <div class="lg:col-span-6 rounded-2xl bg-[#0c1322]/85 backdrop-blur border border-slate-800 p-5 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span>⏳</span> Pending User Approvals
                </h3>
                <a href="{{ route('admin.users.index', ['tab' => 'pending']) }}" class="text-xs text-amber-400 hover:underline">View All ({{ $pendingApprovals }})</a>
            </div>

            <div class="space-y-3">
                @forelse($recentPendingUsers as $pendingUser)
                    <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-slate-200 font-bold block">{{ $pendingUser->name }} ({{ '@' . $pendingUser->username }})</span>
                            <span class="text-slate-400 text-[11px]">{{ $pendingUser->mobile }} &bull; {{ $pendingUser->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.users.status', $pendingUser->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded font-semibold text-xs transition">
                                    Approve
                                </button>
                            </form>
                            <a href="{{ route('admin.users.show', $pendingUser->id) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs transition">
                                Details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs">No pending user registrations! All caught up.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Bets (6 cols) -->
        <div class="lg:col-span-6 rounded-2xl bg-[#0c1322]/85 backdrop-blur border border-slate-800 p-5 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span>🎲</span> Live Bets Stream
                </h3>
                <span class="text-xs text-slate-400">Latest Bets</span>
            </div>

            <div class="space-y-2.5">
                @forelse($recentBets as $bet)
                    <div class="flex items-center justify-between p-2.5 bg-slate-900/90 rounded-lg border border-slate-800 text-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2 py-0.5 rounded font-bold uppercase {{ $bet->selection === 'andar' ? 'badge-andar' : 'badge-bahar' }}">
                                {{ $bet->selection }}
                            </span>
                            <div>
                                <span class="text-slate-200 font-semibold">{{ $bet->user->username ?? 'User' }}</span>
                                <span class="text-slate-500 text-[10px] block">Round #{{ $bet->round->round_number ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="text-right font-bold">
                            <span class="text-white block">{{ number_format($bet->amount, 0) }} pts</span>
                            <span class="text-[10px] uppercase font-semibold {{ $bet->status === 'won' ? 'text-emerald-400' : ($bet->status === 'lost' ? 'text-slate-500' : 'text-amber-400') }}">
                                {{ $bet->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs">No bets placed recently.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
