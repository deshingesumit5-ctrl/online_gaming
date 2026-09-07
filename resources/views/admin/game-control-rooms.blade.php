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

    <!-- Rooms Table -->
    <div class="glass-panel overflow-hidden border-slate-800">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                <span>🎲</span> Game Rooms & Live Control Tables
            </h2>
            <span class="text-xs text-slate-400">Real-time room status</span>
        </div>

        <!-- Mobile Card View (Shows ALL fields clearly without clipping) -->
        <div class="block md:hidden divide-y divide-slate-800/80">
            @forelse($rooms as $room)
                <div class="p-4 space-y-3 hover:bg-slate-800/30 transition">
                    <!-- Top Bar: Room Badge & Status -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-xs">
                                {{ $room->id }}
                            </span>
                            <div>
                                <span class="font-bold text-white text-sm block">{{ $room->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $room->game->name ?? 'Standard Game' }}</span>
                            </div>
                        </div>
                        <div>
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
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-900/70 p-3 rounded-xl border border-slate-800/70">
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase">Active Round</span>
                            @if($room->latest_round)
                                <span class="font-mono font-bold text-amber-400">#{{ $room->latest_round->round_number }}</span>
                                <span class="text-[10px] text-slate-300">({{ str_replace('_', ' ', $room->latest_round->status) }})</span>
                            @else
                                <span class="text-slate-500 italic">No rounds yet</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase">Bets Pool</span>
                            <span class="font-bold text-white font-mono">{{ number_format($room->active_bets_pool ?? 0) }} pts</span>
                            <span class="text-[10px] text-slate-400 block">({{ $room->active_bets_count ?? 0 }} bets)</span>
                        </div>
                        <div class="col-span-2 pt-1.5 border-t border-slate-800/70 flex items-center justify-between text-slate-300 font-mono text-[11px]">
                            <span>⏱️ Bet: {{ $room->betting_duration }}s</span>
                            <span class="text-amber-400">↩️ Cancel: {{ $room->cancellation_duration }}s</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div>
                        <a href="{{ route('admin.game.control', $room->id) }}"
                           class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold text-slate-950 bg-amber-500 hover:bg-amber-400 shadow-md transition active:scale-95">
                            <span>🎮</span> Control Room
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-slate-500 text-xs">
                    No game rooms found.
                </div>
            @endforelse
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
</div>
@endsection
