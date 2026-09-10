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
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 max-w-6xl mx-auto">
            @forelse($rooms as $room)
                <div class="flex flex-col items-center">
                    {{-- Room Card with White Border & Dealer Thumbnail --}}
                    <a href="{{ route('admin.game.control', $room->id) }}" class="casino-room-card block w-full aspect-[4/3] rounded-xl border-2 border-white overflow-hidden shadow-2xl group cursor-pointer relative" title="Enter Operator Panel: {{ $room->name }}">
                        <img src="{{ asset('images/room-thumb.jpg') }}" alt="{{ $room->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <div class="absolute inset-0 bg-black/40 group-hover:bg-black/20 transition flex items-center justify-center p-2">
                            <span class="px-3 py-1 rounded-md bg-black/70 border border-white/30 text-white font-black text-xs sm:text-sm tracking-wider uppercase text-center shadow-lg font-royal">
                                {{ $room->name }}
                            </span>
                        </div>
                    </a>

                    {{-- Status & Metadata (Online/Offline, Users, Opening/Closing Hours) --}}
                    <div class="w-full text-center mt-2.5">
                        @if($room->status === 'live')
                            <div class="inline-flex items-center justify-center gap-1.5 text-emerald-400 font-black text-xs sm:text-sm">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_#10b981] animate-pulse"></span>
                                <span>Online</span>
                            </div>
                            <div class="text-[10px] sm:text-xs text-slate-300 mt-1 font-medium space-y-0.5">
                                <div>Users: {{ $room->active_bets_count > 0 ? $room->active_bets_count * 2 : '26' }}</div>
                                <div>Opening: 11:15 AM</div>
                                <div>Closing: 10:00 PM</div>
                            </div>
                        @else
                            <div class="inline-flex items-center justify-center gap-1.5 text-red-500 font-black text-xs sm:text-sm">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_8px_#ef4444]"></span>
                                <span>Offline</span>
                            </div>
                            <div class="text-[10px] sm:text-xs text-slate-400 mt-1 font-medium space-y-0.5">
                                <div>Users: 0</div>
                                <div>Opening: 11:15 AM</div>
                                <div>Closing: 10:00 PM</div>
                            </div>
                        @endif

                        {{-- Admin Quick Control Button --}}
                        <div class="mt-2">
                            <a href="{{ route('admin.game.control', $room->id) }}" class="inline-block px-3 py-1 bg-amber-500 hover:bg-amber-400 text-slate-950 text-[11px] font-black uppercase rounded-lg shadow transition active:scale-95">
                                🎮 Control Room
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-10 text-center text-slate-500 text-xs">
                    No game rooms found.
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
</div>
@endsection
