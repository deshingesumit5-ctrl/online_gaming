@extends('layouts.admin')

@section('page-title', 'Live Game Control - Select Table')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="glass-panel p-5 flex flex-wrap items-center justify-between gap-4 border-amber-500/20">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center text-slate-950 font-black text-xl font-royal shadow-lg">
                🎮
            </div>
            <div>
                <h1 class="text-lg font-bold font-royal text-white">Live Game Control - Active Rooms</h1>
                <p class="text-xs text-slate-400">Select any room table below to enter its live operator control room and manage rounds, cards, and results.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-lg bg-slate-800 text-xs text-slate-300 font-semibold border border-slate-700">
                Total Rooms: <strong class="text-amber-400 font-bold">{{ count($rooms) }}</strong>
            </span>
            <a href="{{ route('admin.games.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold border border-slate-700 transition flex items-center gap-1.5">
                <span>⚙️</span> Manage Rooms & Games
            </a>
        </div>
    </div>

    <!-- Rooms Grid Directly Shown -->
    <div class="w-full my-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 max-w-7xl mx-auto">
            @forelse($rooms as $room)
                @php
                    $round = $room->latest_round;
                    $window = null;
                    try {
                        $window = $round ? $round->currentBettingWindow() : null;
                    } catch (\Throwable $e) {
                        $window = null;
                    }
                    
                    // Possible Table Statuses: Not Started, Ready, Live, Betting Open, Betting Closed, Result Pending, Completed, Offline
                    if ($room->status !== 'live') {
                        $tableStatus = 'Offline';
                        $statusColor = 'bg-red-950 text-red-400 border-red-800';
                    } elseif (!$round) {
                        $tableStatus = 'Not Started';
                        $statusColor = 'bg-slate-800 text-slate-300 border-slate-700';
                    } elseif ($round->status === 'betting_open') {
                        $tableStatus = 'Betting Open';
                        $statusColor = 'bg-emerald-950 text-emerald-400 border-emerald-700 animate-pulse';
                    } elseif ($round->status === 'betting_closed') {
                        $tableStatus = 'Betting Closed';
                        $statusColor = 'bg-amber-950 text-amber-400 border-amber-800';
                    } elseif ($round->status === 'result_declared' || $round->status === 'round_closed') {
                        $tableStatus = 'Completed';
                        $statusColor = 'bg-slate-800 text-slate-300 border-slate-700';
                    } elseif ($round->status === 'result_pending') {
                        $tableStatus = 'Result Pending';
                        $statusColor = 'bg-amber-900 text-amber-300 border-amber-600';
                    } elseif ($round->first_card) {
                        $tableStatus = 'Live';
                        $statusColor = 'bg-emerald-900 text-emerald-300 border-emerald-600';
                    } else {
                        $tableStatus = 'Ready';
                        $statusColor = 'bg-blue-950 text-blue-300 border-blue-800';
                    }

                    $videoStatus = $room->is_streaming ? 'Live Video Online' : 'Video Offline';
                    $videoColor = $room->is_streaming ? 'text-emerald-400' : 'text-slate-400';
                @endphp
                <div class="glass-panel p-4 flex flex-col justify-between border-slate-800 hover:border-amber-500/50 transition duration-300 shadow-xl rounded-2xl">
                    <div>
                        {{-- Top Badges: Status & Video --}}
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $statusColor }}">
                                {{ $tableStatus }}
                            </span>
                            <span class="text-[10px] font-bold flex items-center gap-1 {{ $videoColor }}">
                                <span class="w-2 h-2 rounded-full {{ $room->is_streaming ? 'bg-emerald-400 animate-ping' : 'bg-slate-500' }}"></span>
                                {{ $videoStatus }}
                            </span>
                        </div>

                        {{-- Table Thumbnail & Title --}}
                        <a href="{{ route('admin.game.control', $room->id) }}" class="casino-room-card block w-full aspect-[16/10] rounded-xl border border-white/20 overflow-hidden shadow-lg group cursor-pointer relative mb-3">
                            <img src="{{ asset('images/room-thumb.jpg') }}" alt="{{ $room->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent flex flex-col justify-end p-2.5">
                                <span class="text-white font-black text-xs sm:text-sm font-royal drop-shadow">
                                    {{ $room->name }} (#{{ $room->id }})
                                </span>
                                <span class="text-[10px] text-amber-300 font-semibold">
                                    {{ $room->game->name ?? 'Fun2Win Game' }}
                                </span>
                            </div>
                        </a>

                        {{-- Required Specifications (PDF Pages 2 & 3) --}}
                        <div class="space-y-1.5 text-xs text-slate-300 py-1 border-t border-b border-slate-800/80 my-2">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Current Session:</span>
                                <strong class="text-white font-mono">
                                    {{ $round ? ('#' . $round->round_number) : 'None' }}
                                </strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Session ID:</span>
                                <strong class="text-amber-300 font-mono">
                                    {{ $round ? $round->id : '-' }}
                                </strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Active Users:</span>
                                <strong class="text-slate-200">
                                    👥 {{ $room->active_users_count ?? 0 }}
                                </strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Betting Status:</span>
                                <strong class="{{ ($round && $round->status === 'betting_open') ? 'text-emerald-400 font-black' : 'text-amber-400 font-bold' }}">
                                    {{ ($round && $round->status === 'betting_open') ? 'OPEN' : 'CLOSED' }}
                                </strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Betting Window:</span>
                                <strong class="text-slate-300">
                                    {{ $window ? ('#' . $window->window_number . ' (' . strtoupper($window->status) . ')') : '-' }}
                                </strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Current Pool:</span>
                                <strong class="text-white font-mono">
                                    {{ number_format($room->active_bets_pool ?? 0) }} pts
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{-- Control Room Button --}}
                    <div class="pt-2">
                        <a href="{{ route('admin.game.control', $room->id) }}" class="w-full py-2.5 px-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl shadow-lg transition flex items-center justify-center gap-1.5 active:scale-95">
                            <span>🎮</span>
                            <span>Enter Control Room</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-500 text-xs glass-panel">
                    No gaming tables found.
                </div>
            @endforelse
        </div>
    </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 uppercase text-[11px] font-bold text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5">#</th>
                        <th class="px-4 py-3.5">Room Table</th>
                        <th class="px-4 py-3.5">Game</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Active Round</th>
                        <th class="px-4 py-3.5">Current Bets Pool</th>
                        <th class="px-4 py-3.5">Timings (Bet / Cancel)</th>
                        <th class="px-4 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($rooms as $room)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-4 py-4 font-mono font-bold text-slate-500">
                                #{{ $room->id }}
                            </td>
                            <td class="px-4 py-4">
                                <a href="{{ route('admin.game.control', $room->id) }}" class="flex items-center gap-2.5 group">
                                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-xs group-hover:bg-amber-500 group-hover:text-slate-950 transition">
                                        {{ $room->id }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-white group-hover:text-amber-400 transition block text-sm">
                                            {{ $room->name }}
                                        </span>
                                        <span class="text-[10px] text-slate-500">
                                            Created {{ $room->created_at ? $room->created_at->diffForHumans() : '' }}
                                        </span>
                                    </div>
                                </a>
                            </td>
                            <td class="px-4 py-4">
                                <span class="text-slate-200 font-semibold">{{ $room->game->name ?? 'Standard Game' }}</span>
                            </td>
                            <td class="px-4 py-4">
                                @if($room->status === 'live')
                                    <span class="badge-live px-2 py-0.5 rounded text-[10px] font-extrabold uppercase inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span> LIVE
                                    </span>
                                @elseif($room->status === 'upcoming')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase">
                                        UPCOMING
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700 uppercase">
                                        {{ $room->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($room->latest_round)
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-amber-400">Round #{{ $room->latest_round->round_number }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $room->latest_round->status === 'betting_open' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-300' }}">
                                            {{ str_replace('_', ' ', $room->latest_round->status) }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-500 italic">No rounds yet</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-white">
                                    {{ number_format($room->active_bets_pool ?? 0) }} pts
                                </div>
                                <span class="text-[10px] text-slate-500">{{ $room->active_bets_count ?? 0 }} active bet(s)</span>
                            </td>
                            <td class="px-4 py-4 text-slate-300 font-mono text-[11px]">
                                <div class="flex items-center gap-2">
                                    <span title="Betting Timer">⏱️ {{ $room->betting_duration }}s</span>
                                    <span class="text-slate-600">&bull;</span>
                                    <span title="Cancellation Window" class="text-amber-400">↩️ {{ $room->cancellation_duration }}s</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('admin.game.control', $room->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-950 bg-amber-500 hover:bg-amber-400 shadow-md transition active:scale-95">
                                    <span>🎮</span> Control Room
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-500">
                                No game rooms found. Create rooms in <a href="{{ route('admin.games.index') }}" class="text-amber-400 hover:underline font-bold">Game Management</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
