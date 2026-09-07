@extends('layouts.admin')

@section('page-title', 'Reports & Analytics')

@section('content')
<div class="space-y-6">
    <!-- Reports Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-800/80 no-scrollbar">
        <a href="{{ route('admin.reports.index', ['tab' => 'user_reports']) }}"
           class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'user_reports' ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-500/20' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <span>👥</span> User Reports
        </a>
        <a href="{{ route('admin.reports.index', ['tab' => 'game_reports']) }}"
           class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'game_reports' ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-500/20' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <span>🎮</span> Game Reports
        </a>
        <a href="{{ route('admin.reports.index', ['tab' => 'points_reports']) }}"
           class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'points_reports' ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-500/20' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <span>💳</span> Points Reports
        </a>
        <a href="{{ route('admin.reports.index', ['tab' => 'withdrawal_reports']) }}"
           class="px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2 whitespace-nowrap {{ $tab === 'withdrawal_reports' ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-500/20' : 'bg-slate-900/80 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
            <span>🏦</span> Withdrawal Reports
        </a>
    </div>

    {{-- ==================== TAB 1: USER REPORTS ==================== --}}
    @if($tab === 'user_reports')
        <!-- User Metrics Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="glass-panel p-4 sm:p-5 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-slate-400 block mb-1">Total Users</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-white">{{ number_format($userMetrics['total_users']) }}</span>
                <span class="text-[10px] text-slate-500 block mt-1">All Registered Players</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-emerald-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-emerald-400 block mb-1">Active Users</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-emerald-300">{{ number_format($userMetrics['active_users']) }}</span>
                <span class="text-[10px] text-emerald-500/80 block mt-1">Approved & Verified</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-slate-400 block mb-1">Inactive Users</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-slate-300">{{ number_format($userMetrics['inactive_users']) }}</span>
                <span class="text-[10px] text-slate-500 block mt-1">Dormant Accounts</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-amber-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-amber-400 block mb-1">Pending Users</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-amber-300">{{ number_format($userMetrics['pending_users']) }}</span>
                <span class="text-[10px] text-amber-500/80 block mt-1">Awaiting Approval</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-red-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-red-400 block mb-1">Rejected Users</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-red-400">{{ number_format($userMetrics['rejected_users']) }}</span>
                <span class="text-[10px] text-red-500/80 block mt-1">Declined or Blocked</span>
            </div>
        </div>

        <!-- Header & Search Tabs Matching User Approvals -->
        <div class="glass-panel p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
            @php $curFilter = request('user_status', 'all'); @endphp
            <!-- Tabs -->
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.reports.index', ['tab' => 'user_reports', 'user_status' => 'all', 'search' => $search]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $curFilter === 'all' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    All Users ({{ $userMetrics['total_users'] }})
                </a>
                <a href="{{ route('admin.reports.index', ['tab' => 'user_reports', 'user_status' => 'pending', 'search' => $search]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $curFilter === 'pending' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Pending ({{ $userMetrics['pending_users'] }})
                </a>
                <a href="{{ route('admin.reports.index', ['tab' => 'user_reports', 'user_status' => 'active', 'search' => $search]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $curFilter === 'active' ? 'bg-emerald-600 text-white font-black shadow-lg shadow-emerald-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Active ({{ $userMetrics['active_users'] }})
                </a>
                <a href="{{ route('admin.reports.index', ['tab' => 'user_reports', 'user_status' => 'inactive', 'search' => $search]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $curFilter === 'inactive' ? 'bg-slate-700 text-white font-black' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Inactive ({{ $userMetrics['inactive_users'] }})
                </a>
                <a href="{{ route('admin.reports.index', ['tab' => 'user_reports', 'user_status' => 'rejected', 'search' => $search]) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $curFilter === 'rejected' ? 'bg-red-600 text-white font-black shadow-lg shadow-red-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    Blocked ({{ $userMetrics['rejected_users'] }})
                </a>
            </div>

            <!-- Search Box -->
            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2 w-full md:w-auto">
                <input type="hidden" name="tab" value="user_reports">
                <input type="hidden" name="user_status" value="{{ $curFilter }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, name, username, mobile..."
                       class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs w-full md:w-64 focus:border-amber-400">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition border border-slate-700">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('admin.reports.index', ['tab' => 'user_reports', 'user_status' => $curFilter]) }}" class="px-3 py-2 bg-red-950 hover:bg-red-900 text-red-300 text-xs font-bold rounded-xl transition border border-red-800/80">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Users Table Matching User Approvals Table Design -->
        <div class="glass-panel overflow-hidden border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
            <!-- Mobile Card View (Matching User Approvals) -->
            <div class="block md:hidden divide-y divide-slate-800/80">
                @forelse($usersData as $u)
                    <div class="p-4 space-y-3 hover:bg-slate-850/40 transition">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-xs font-bold text-amber-400">#{{ $u->id }}</span>
                            <x-status-badge :status="$u->status" />
                        </div>

                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="font-bold text-slate-100 text-sm">{{ $u->name }}</div>
                                <span class="text-slate-400 font-mono text-xs">{{ '@' . $u->username }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block uppercase">Balance</span>
                                <span class="font-black text-sm text-amber-300 font-mono">{{ number_format($u->wallet_balance, 0) }} PTS</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs bg-slate-900/70 p-3 rounded-xl border border-slate-800/70">
                            <div>
                                <span class="text-[10px] text-slate-500 block uppercase">Mobile</span>
                                <span class="font-mono text-slate-200">{{ $u->mobile ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 block uppercase">Registered</span>
                                <span class="text-slate-300">{{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}</span>
                            </div>
                            <div class="col-span-2 pt-1.5 border-t border-slate-800/70 flex items-center justify-between">
                                <span class="text-[10px] text-slate-500 uppercase">Bets Placed</span>
                                <span class="font-mono font-bold text-slate-200">{{ number_format($u->bets_count ?? 0) }}</span>
                            </div>
                        </div>

                        <div class="pt-1">
                            <a href="{{ route('admin.users.show', $u->id) }}" 
                               class="block w-full py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition border border-slate-700 text-center">
                                View Profile
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-500 text-xs">No players found.</div>
                @endforelse
            </div>

            <!-- Desktop Table View Matching User Approvals -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#090e1a]/95 text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                        <tr>
                            <th class="py-4 px-4 font-bold">User ID</th>
                            <th class="py-4 px-4 font-bold">Name</th>
                            <th class="py-4 px-4 font-bold">Mobile</th>
                            <th class="py-4 px-4 font-bold">Registration Date</th>
                            <th class="py-4 px-4 font-bold">Status</th>
                            <th class="py-4 px-4 font-bold text-right">Wallet Balance</th>
                            <th class="py-4 px-4 font-bold text-center">Bets Placed</th>
                            <th class="py-4 px-4 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-sans">
                        @forelse($usersData as $u)
                            <tr class="hover:bg-slate-850/40 transition">
                                <td class="py-4 px-4 font-mono font-bold text-amber-400">
                                    #{{ $u->id }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-100 text-sm">{{ $u->name }}</div>
                                    <span class="text-slate-400 font-mono text-[11px]">{{ '@' . $u->username }}</span>
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-300">
                                    {{ $u->mobile ?: '-' }}
                                </td>
                                <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                                    {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <x-status-badge :status="$u->status" />
                                </td>
                                <td class="py-4 px-4 font-black text-amber-300 text-sm text-right whitespace-nowrap">
                                    {{ number_format($u->wallet_balance, 0) }} PTS
                                </td>
                                <td class="py-4 px-4 text-center font-mono font-bold text-slate-200">
                                    {{ number_format($u->bets_count ?? 0) }}
                                </td>
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.users.show', $u->id) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold transition border border-slate-700" title="View Full Profile">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400 font-semibold">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($usersData && $usersData->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $usersData->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- ==================== TAB 2: GAME REPORTS ==================== --}}
    @if($tab === 'game_reports')
        <!-- Game Metrics Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="glass-panel p-4 sm:p-5 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-slate-400 block mb-1">Games Played</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-white">{{ number_format($gameMetrics['games_played']) }}</span>
                <span class="text-[10px] text-slate-500 block mt-1">Live Rooms Active</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-indigo-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-indigo-400 block mb-1">Rounds Played</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-indigo-300">{{ number_format($gameMetrics['rounds_played']) }}</span>
                <span class="text-[10px] text-indigo-500/80 block mt-1">Total Game Rounds</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-amber-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-amber-400 block mb-1">Total Bets</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-amber-300">{{ number_format($gameMetrics['total_bets']) }}</span>
                <span class="text-[10px] text-amber-500/80 block mt-1">Bets Placed</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-indigo-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-indigo-400 block mb-1">ANDAR Bets</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-indigo-300">{{ number_format($gameMetrics['andar_bets']) }}</span>
                <span class="text-[10px] text-indigo-500/80 block mt-1">Side A Distribution</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-red-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-red-400 block mb-1">BAHAR Bets</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-red-300">{{ number_format($gameMetrics['bahar_bets']) }}</span>
                <span class="text-[10px] text-red-500/80 block mt-1">Side B Distribution</span>
            </div>
        </div>

        <!-- Game Rounds Table Matching User Approvals Card Style -->
        <div class="glass-panel overflow-hidden border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                        <span>🎮</span> Game Round Performance & Settlement Audit
                    </h3>
                    <span class="text-xs text-slate-400">All completed & active game rounds</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#090e1a]/95 text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                        <tr>
                            <th class="py-4 px-4 font-bold">Round #</th>
                            <th class="py-4 px-4 font-bold">Table / Game</th>
                            <th class="py-4 px-4 font-bold">First Card</th>
                            <th class="py-4 px-4 font-bold">Winning Side</th>
                            <th class="py-4 px-4 font-bold">Status</th>
                            <th class="py-4 px-4 font-bold">Bets Count</th>
                            <th class="py-4 px-4 font-bold">Total Bets (pts)</th>
                            <th class="py-4 px-4 font-bold">Payout Amount (pts)</th>
                            <th class="py-4 px-4 font-bold">Date / Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-sans">
                        @forelse($gameRoundsData as $r)
                            <tr class="hover:bg-slate-850/40 transition">
                                <td class="py-4 px-4 font-mono font-bold text-amber-400">#{{ $r->round_number }}</td>
                                <td class="py-4 px-4 font-bold text-slate-200">{{ $r->room->name ?? 'Room' }}</td>
                                <td class="py-4 px-4">
                                    @if($r->first_card)
                                        <span class="font-mono text-amber-400 uppercase font-semibold">{{ str_replace('_', ' ', $r->first_card) }}</span>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    @if($r->winning_side === 'andar')
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase badge-andar">ANDAR</span>
                                    @elseif($r->winning_side === 'bahar')
                                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase badge-bahar">BAHAR</span>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-400">{{ $r->status }}</span>
                                </td>
                                <td class="py-4 px-4 text-slate-300">
                                    {{ $r->total_bets_count }} <span class="text-slate-500 text-[10px]">(A: {{ $r->andar_bets_count }}, B: {{ $r->bahar_bets_count }})</span>
                                </td>
                                <td class="py-4 px-4 font-bold text-white">
                                    {{ number_format($r->total_bet_amount ?: 0, 0) }}
                                </td>
                                <td class="py-4 px-4 font-bold text-emerald-400">
                                    {{ number_format($r->total_payout_amount ?: 0, 0) }}
                                </td>
                                <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                                    {{ $r->created_at ? $r->created_at->format('d M Y, h:i A') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400 font-semibold">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($gameRoundsData && $gameRoundsData->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $gameRoundsData->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- ==================== TAB 3: POINTS REPORTS ==================== --}}
    @if($tab === 'points_reports')
        <!-- Points Metrics Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="glass-panel p-4 sm:p-5 border-emerald-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-emerald-400 block mb-1">Points Added</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-emerald-300">{{ number_format($pointsMetrics['points_added'], 0) }}</span>
                <span class="text-[10px] text-emerald-500/80 block mt-1">Deposits & Credits</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-amber-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-amber-400 block mb-1">Points Bet</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-amber-300">{{ number_format($pointsMetrics['points_bet'], 0) }}</span>
                <span class="text-[10px] text-amber-500/80 block mt-1">Total Points Wagered</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-indigo-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-indigo-400 block mb-1">Points Won</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-indigo-300">{{ number_format($pointsMetrics['points_won'], 0) }}</span>
                <span class="text-[10px] text-indigo-500/80 block mt-1">Total Payouts Credited</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-red-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-red-400 block mb-1">Points Withdrawn</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-red-300">{{ number_format($pointsMetrics['points_withdrawn'], 0) }}</span>
                <span class="text-[10px] text-red-500/80 block mt-1">Settled Withdrawals</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-slate-400 block mb-1">Current Outstanding Points</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-white">{{ number_format($pointsMetrics['current_outstanding_points'], 0) }}</span>
                <span class="text-[10px] text-slate-500 block mt-1">Active Wallet Points Pool</span>
            </div>
        </div>

        <!-- Filter & Search Bar Matching User Approvals -->
        <div class="glass-panel p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
            <div>
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span>💳</span> Points & Transactions Audit Ledger
                </h3>
                <span class="text-xs text-slate-400">All real point movements, deductions, payouts, and adjustments</span>
            </div>

            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2 w-full md:w-auto">
                <input type="hidden" name="tab" value="points_reports">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, player, remarks..."
                       class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs w-full md:w-64 focus:border-amber-400">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition border border-slate-700">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('admin.reports.index', ['tab' => 'points_reports']) }}" class="px-3 py-2 bg-red-950 hover:bg-red-900 text-red-300 text-xs font-bold rounded-xl transition border border-red-800/80">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Points Transactions Ledger Table Matching User Approvals -->
        <div class="glass-panel overflow-hidden border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#090e1a]/95 text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                        <tr>
                            <th class="py-4 px-4 font-bold">User ID</th>
                            <th class="py-4 px-4 font-bold">Txn ID</th>
                            <th class="py-4 px-4 font-bold">Player</th>
                            <th class="py-4 px-4 font-bold">Transaction Type</th>
                            <th class="py-4 px-4 font-bold">Amount (pts)</th>
                            <th class="py-4 px-4 font-bold">Balance After</th>
                            <th class="py-4 px-4 font-bold">Remarks</th>
                            <th class="py-4 px-4 font-bold">Date / Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-sans">
                        @forelse($pointsData as $t)
                            <tr class="hover:bg-slate-850/40 transition">
                                <td class="py-4 px-4 font-mono font-bold text-amber-400">
                                    #{{ $t->user_id }}
                                </td>
                                <td class="py-4 px-4 font-mono text-amber-400 font-semibold">{{ $t->transaction_id ?: $t->id }}</td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-100 text-sm">{{ $t->user->name ?? 'User #' . $t->user_id }}</div>
                                    <span class="text-slate-400 font-mono text-[11px]">{{ '@' . ($t->user->username ?? '') }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300">
                                        {{ str_replace('_', ' ', $t->type) }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-bold {{ $t->amount >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $t->amount >= 0 ? '+' : '' }}{{ number_format($t->amount, 0) }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-200">
                                    {{ number_format($t->balance_after, 0) }}
                                </td>
                                <td class="py-4 px-4 text-slate-300 max-w-xs truncate" title="{{ $t->remarks }}">
                                    {{ $t->remarks ?: '-' }}
                                </td>
                                <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                                    {{ $t->created_at ? $t->created_at->format('d M Y, h:i A') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400 font-semibold">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pointsData && $pointsData->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $pointsData->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- ==================== TAB 4: WITHDRAWAL REPORTS ==================== --}}
    @if($tab === 'withdrawal_reports')
        <!-- Withdrawal Metrics Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="glass-panel p-4 sm:p-5 border-amber-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-amber-400 block mb-1">Pending</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-amber-300">{{ number_format($withdrawalMetrics['pending']) }}</span>
                <span class="text-[10px] text-amber-500/80 block mt-1">Awaiting Settlement</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-emerald-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-emerald-400 block mb-1">Approved</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-emerald-300">{{ number_format($withdrawalMetrics['approved']) }}</span>
                <span class="text-[10px] text-emerald-500/80 block mt-1">Confirmed Withdrawals</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-red-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-red-400 block mb-1">Rejected</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-red-400">{{ number_format($withdrawalMetrics['rejected']) }}</span>
                <span class="text-[10px] text-red-500/80 block mt-1">Declined Requests</span>
            </div>

            <div class="glass-panel p-4 sm:p-5 border-blue-500/30 rounded-2xl bg-[#0c1324]/90 shadow-xl">
                <span class="text-[11px] uppercase font-bold text-blue-400 block mb-1">Processed</span>
                <span class="text-xl sm:text-2xl font-black font-royal text-blue-300">{{ number_format($withdrawalMetrics['processed']) }}</span>
                <span class="text-[10px] text-blue-500/80 block mt-1">Completed Transfers</span>
            </div>
        </div>

        <!-- Filter & Search Bar Matching User Approvals -->
        <div class="glass-panel p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
            @php $curWStatus = request('w_status', 'all'); @endphp
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.reports.index', ['tab' => 'withdrawal_reports', 'w_status' => 'all', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $curWStatus === 'all' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">All</a>
                <a href="{{ route('admin.reports.index', ['tab' => 'withdrawal_reports', 'w_status' => 'pending', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $curWStatus === 'pending' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">Pending</a>
                <a href="{{ route('admin.reports.index', ['tab' => 'withdrawal_reports', 'w_status' => 'approved', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $curWStatus === 'approved' ? 'bg-emerald-600 text-white font-black shadow-lg shadow-emerald-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">Approved</a>
                <a href="{{ route('admin.reports.index', ['tab' => 'withdrawal_reports', 'w_status' => 'processed', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $curWStatus === 'processed' ? 'bg-blue-600 text-white font-black shadow-lg shadow-blue-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">Processed</a>
                <a href="{{ route('admin.reports.index', ['tab' => 'withdrawal_reports', 'w_status' => 'rejected', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $curWStatus === 'rejected' ? 'bg-red-600 text-white font-black shadow-lg shadow-red-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">Rejected</a>
            </div>

            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2 w-full md:w-auto">
                <input type="hidden" name="tab" value="withdrawal_reports">
                <input type="hidden" name="w_status" value="{{ $curWStatus }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, player, bank..."
                       class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs w-full md:w-64 focus:border-amber-400">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition border border-slate-700">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('admin.reports.index', ['tab' => 'withdrawal_reports', 'w_status' => $curWStatus]) }}" class="px-3 py-2 bg-red-950 hover:bg-red-900 text-red-300 text-xs font-bold rounded-xl transition border border-red-800/80">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Withdrawals Table Matching User Approvals -->
        <div class="glass-panel overflow-hidden border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#090e1a]/95 text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                        <tr>
                            <th class="py-4 px-4 font-bold">User ID</th>
                            <th class="py-4 px-4 font-bold">Request ID</th>
                            <th class="py-4 px-4 font-bold">Player</th>
                            <th class="py-4 px-4 font-bold">Amount (pts)</th>
                            <th class="py-4 px-4 font-bold">Settlement Details</th>
                            <th class="py-4 px-4 font-bold">Status</th>
                            <th class="py-4 px-4 font-bold">Rejection Remark</th>
                            <th class="py-4 px-4 font-bold">Processed By</th>
                            <th class="py-4 px-4 font-bold">Date / Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-sans">
                        @forelse($withdrawalsData as $w)
                            <tr class="hover:bg-slate-850/40 transition">
                                <td class="py-4 px-4 font-mono font-bold text-amber-400">
                                    #{{ $w->user_id }}
                                </td>
                                <td class="py-4 px-4 font-mono text-amber-400 font-semibold">{{ $w->request_id }}</td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-100 text-sm">{{ $w->user->name ?? 'User #' . $w->user_id }}</div>
                                    <span class="text-slate-400 font-mono text-[11px]">{{ '@' . ($w->user->username ?? '') }}</span>
                                </td>
                                <td class="py-4 px-4 font-bold text-white">
                                    ₹{{ number_format($w->amount_requested, 0) }}
                                </td>
                                <td class="py-4 px-4 text-slate-300 max-w-xs truncate" title="{{ $w->settlement_details }}">
                                    {{ $w->settlement_details ?: '-' }}
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if($w->status === 'approved')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950/80 text-emerald-400 border border-emerald-800/60 uppercase">APPROVED</span>
                                    @elseif(in_array($w->status, ['processed', 'settled']))
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-950/80 text-blue-400 border border-blue-800/60 uppercase">PROCESSED</span>
                                    @elseif($w->status === 'pending')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-950/80 text-amber-400 border border-amber-800/60 uppercase">PENDING</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-950/80 text-red-400 border border-red-800/60 uppercase">{{ strtoupper($w->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-red-300 max-w-xs truncate" title="{{ $w->rejection_remark }}">
                                    {{ $w->rejection_remark ?: '-' }}
                                </td>
                                <td class="py-4 px-4 text-slate-400">
                                    {{ $w->processor->name ?? '-' }}
                                </td>
                                <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                                    {{ $w->created_at ? $w->created_at->format('d M Y, h:i A') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400 font-semibold">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($withdrawalsData && $withdrawalsData->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $withdrawalsData->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
