@extends('layouts.admin')

@section('page-title', 'Game & Room Management')

@section('content')
<div class="space-y-6">
    <!-- Top Summary & Quick Actions -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-panel p-4 border-slate-800 bg-[#0c1324]/90 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-bold text-slate-400 block">Total Games</span>
                <span class="text-2xl font-black font-royal text-white">{{ $stats['total_games'] }}</span>
            </div>
            <span class="text-2xl">🎲</span>
        </div>
        <div class="glass-panel p-4 border-slate-800 bg-[#0c1324]/90 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-bold text-emerald-400 block">Active Games</span>
                <span class="text-2xl font-black font-royal text-emerald-300">{{ $stats['active_games'] }}</span>
            </div>
            <span class="text-2xl">🟢</span>
        </div>
        <div class="glass-panel p-4 border-slate-800 bg-[#0c1324]/90 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-bold text-slate-400 block">Total Rooms / Tables</span>
                <span class="text-2xl font-black font-royal text-white">{{ $stats['total_rooms'] }}</span>
            </div>
            <span class="text-2xl">🃏</span>
        </div>
        <div class="glass-panel p-4 border-slate-800 bg-[#0c1324]/90 rounded-2xl flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-bold text-amber-400 block">Live Tables</span>
                <span class="text-2xl font-black font-royal text-amber-300">{{ $stats['live_rooms'] }}</span>
            </div>
            <span class="text-2xl">⚡</span>
        </div>
    </div>

    <!-- Header Action Bar -->
    <div class="glass-panel p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
        <div>
            <h2 class="text-lg font-bold font-royal text-white flex items-center gap-2">
                <span>Game Engine & Room Configuration</span>
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Manage games, tables, betting durations, cancellation windows, and denominations.</p>
        </div>
        <button type="button" onclick="openCreateGameModal()" class="btn-gold px-4 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl flex items-center gap-2 shadow-lg shadow-amber-500/20">
            <span>+</span> Create New Game
        </button>
    </div>

    <!-- Games & Rooms List -->
    <div class="space-y-6">
        @forelse($games as $game)
            <div class="glass-panel rounded-2xl border-slate-800 bg-[#0c1324]/90 shadow-xl overflow-hidden">
                <!-- Game Header Card -->
                <div class="p-5 border-b border-slate-800 bg-[#090e1a]/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center text-slate-950 font-black text-lg shadow-md">
                            🎮
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h3 class="text-base font-bold font-royal text-white">{{ $game->name }}</h3>
                                @if($game->status === 'open')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-950 text-emerald-300 border border-emerald-500/40">
                                        Active / Open
                                    </span>
                                @elseif($game->status === 'scheduled')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-950 text-blue-300 border border-blue-500/40">
                                        Scheduled
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">
                                        Deactivated / Closed
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-xs text-slate-400 mt-1 flex-wrap">
                                <span>Game ID: <strong class="text-slate-300 font-mono">#{{ $game->id }}</strong></span>
                                <span>&bull;</span>
                                <span>Start Time: <strong class="text-amber-400 font-mono">{{ $game->start_time ? $game->start_time->format('d M Y, h:i A') : 'Immediate' }}</strong></span>
                                <span>&bull;</span>
                                <span>Tables: <strong class="text-slate-200 font-bold">{{ $game->rooms->count() }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Game Level Action Buttons -->
                    <div class="flex items-center gap-2 flex-wrap w-full md:w-auto justify-end">
                        <!-- Add Room / Table -->
                        <button type="button" 
                                onclick="openCreateRoomModal({{ $game->id }}, '{{ addslashes($game->name) }}')"
                                class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold transition flex items-center gap-1.5">
                            <span>+</span> Add Room / Table
                        </button>

                        <!-- Edit Game -->
                        <button type="button" 
                                onclick="openEditGameModal({{ $game->id }}, '{{ addslashes($game->name) }}', '{{ $game->status }}', '{{ $game->start_time ? $game->start_time->format('Y-m-d\TH:i') : '' }}')"
                                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition">
                            Edit Game
                        </button>

                        <!-- Toggle Game Status -->
                        <form method="POST" action="{{ route('admin.games.toggle', $game->id) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $game->status === 'open' ? 'bg-red-950/70 hover:bg-red-900 text-red-300 border border-red-800/50' : 'bg-emerald-950/70 hover:bg-emerald-900 text-emerald-300 border border-emerald-800/50' }}">
                                {{ $game->status === 'open' ? 'Deactivate Game' : 'Activate Game' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Rooms / Tables Sub-Table -->
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs uppercase font-bold text-slate-400 tracking-wider flex items-center gap-1.5">
                            <span>🃏</span> Active Tables / Rooms ({{ $game->rooms->count() }})
                        </span>
                    </div>

                    @if($game->rooms->isEmpty())
                        <div class="p-6 text-center rounded-xl bg-slate-900/60 border border-slate-800 text-slate-400 text-xs">
                            No tables or rooms configured under this game yet. Click <strong>"+ Add Room / Table"</strong> above to launch one.
                        </div>
                    @else
                        <!-- Mobile Card View (Shows ALL room fields clearly on mobile) -->
                        <div class="block md:hidden divide-y divide-slate-800/80">
                            @foreach($game->rooms as $room)
                                @php
                                    $denoms = $room->allowed_denominations ?: [100, 500, 1000, 2000, 5000];
                                @endphp
                                <div class="p-4 space-y-3 hover:bg-slate-800/30 transition">
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <span class="font-bold text-white text-sm block">{{ $room->name }}</span>
                                            <span class="text-[10px] text-slate-500 font-mono">Room ID: #{{ $room->id }}</span>
                                        </div>
                                        @if($room->status === 'live')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-950 text-emerald-300 border border-emerald-500/40 flex items-center gap-1 w-fit">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> LIVE
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">
                                                CLOSED
                                            </span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-900/70 p-3 rounded-xl border border-slate-800/70">
                                        <div>
                                            <span class="text-[10px] text-slate-500 block uppercase">Bet Duration</span>
                                            <span class="font-mono font-bold text-amber-300">⏱️ {{ $room->betting_duration }}s</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-500 block uppercase">Cancel Window</span>
                                            <span class="font-mono text-slate-300">↩️ {{ $room->cancellation_duration }}s</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-500 block uppercase">Start Time</span>
                                            <span class="text-slate-300 font-mono text-[11px]">{{ $room->start_time ? $room->start_time->format('d M, h:i A') : 'Immediate' }}</span>
                                        </div>
                                        <div class="col-span-2 pt-1 border-t border-slate-800/70">
                                            <span class="text-[10px] text-slate-500 block uppercase mb-1">Denominations</span>
                                            <div class="flex items-center gap-1 flex-wrap">
                                                @foreach($denoms as $denom)
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-800 text-amber-400 font-mono text-[10px] font-semibold border border-slate-700">
                                                        {{ $denom }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                        <a href="{{ route('admin.game.control', $room->id) }}" 
                                           class="flex-1 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black uppercase tracking-wider transition text-center shadow-md flex items-center justify-center gap-1">
                                            <span>🎮</span> Control
                                        </a>

                                        <button type="button" 
                                                onclick="openEditRoomModal({{ $room->id }}, '{{ addslashes($room->name) }}', {{ $room->betting_duration }}, {{ $room->cancellation_duration }}, '{{ implode(', ', $denoms) }}', '{{ addslashes($room->live_stream_url ?? '') }}', '{{ $room->status }}', '{{ $room->start_time ? $room->start_time->format('Y-m-d\TH:i') : '' }}', '{{ addslashes($room->opening_time ?? '11:15 AM') }}', '{{ addslashes($room->closing_time ?? '10:00 PM') }}')"
                                                class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('admin.rooms.toggle', $room->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-3 py-2 rounded-lg text-xs font-bold border transition {{ $room->status === 'live' ? 'bg-red-950/70 hover:bg-red-900 text-red-300 border-red-800/60' : 'bg-emerald-950/70 hover:bg-emerald-900 text-emerald-300 border-emerald-800/60' }}">
                                                {{ $room->status === 'live' ? 'Close' : 'Go Live' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[#090e1a]/95 text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                                    <tr>
                                        <th class="py-3 px-4 font-bold">Room Name</th>
                                        <th class="py-3 px-4 font-bold">Status</th>
                                        <th class="py-3 px-4 font-bold">Betting Duration</th>
                                        <th class="py-3 px-4 font-bold">Cancel Window</th>
                                        <th class="py-3 px-4 font-bold">Bet Denominations</th>
                                        <th class="py-3 px-4 font-bold">Start Time</th>
                                        <th class="py-3 px-4 font-bold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/50">
                                    @foreach($game->rooms as $room)
                                        <tr class="hover:bg-slate-850/40 transition">
                                            <!-- Room Name -->
                                            <td class="py-3.5 px-4 font-bold text-white whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-amber-400">♠</span>
                                                    <span>{{ $room->name }}</span>
                                                </div>
                                                <span class="text-[10px] text-slate-500 font-mono">Room ID: #{{ $room->id }}</span>
                                            </td>

                                            <!-- Status -->
                                            <td class="py-3.5 px-4 whitespace-nowrap">
                                                @if($room->status === 'live')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-950 text-emerald-300 border border-emerald-500/40 flex items-center gap-1 w-fit">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> LIVE
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">
                                                        CLOSED
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Betting Duration -->
                                            <td class="py-3.5 px-4 font-mono font-bold text-amber-300 whitespace-nowrap">
                                                ⏱️ {{ $room->betting_duration }}s
                                            </td>

                                            <!-- Cancellation Window -->
                                            <td class="py-3.5 px-4 font-mono text-slate-300 whitespace-nowrap">
                                                ↩️ {{ $room->cancellation_duration }}s
                                            </td>

                                            <!-- Denominations -->
                                            <td class="py-3.5 px-4">
                                                <div class="flex items-center gap-1 flex-wrap">
                                                    @php
                                                        $denoms = $room->allowed_denominations ?: [100, 500, 1000, 2000, 5000];
                                                    @endphp
                                                    @foreach($denoms as $denom)
                                                        <span class="px-1.5 py-0.5 rounded bg-slate-800 text-amber-400 font-mono text-[10px] font-semibold border border-slate-700">
                                                            {{ $denom }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>

                                            <!-- Start Time -->
                                            <td class="py-3.5 px-4 text-slate-400 whitespace-nowrap font-mono">
                                                {{ $room->start_time ? $room->start_time->format('d M, h:i A') : 'Immediate' }}
                                            </td>

                                            <!-- Actions -->
                                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <!-- Live Game Control Panel Link -->
                                                    <a href="{{ route('admin.game.control', $room->id) }}" 
                                                       class="px-2.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-[11px] font-black uppercase tracking-wider transition shadow-md flex items-center gap-1" title="Open Live Control Room">
                                                        <span>🎮</span> Control Room
                                                    </a>

                                                    <!-- Edit Room -->
                                                    <button type="button" 
                                                            onclick="openEditRoomModal({{ $room->id }}, '{{ addslashes($room->name) }}', {{ $room->betting_duration }}, {{ $room->cancellation_duration }}, '{{ implode(', ', $denoms) }}', '{{ addslashes($room->live_stream_url ?? '') }}', '{{ $room->status }}', '{{ $room->start_time ? $room->start_time->format('Y-m-d\TH:i') : '' }}', '{{ addslashes($room->opening_time ?? '11:15 AM') }}', '{{ addslashes($room->closing_time ?? '10:00 PM') }}')"
                                                            class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold border border-slate-700 transition">
                                                        Edit
                                                    </button>

                                                    <!-- Toggle Room Live/Closed -->
                                                    <form method="POST" action="{{ route('admin.rooms.toggle', $room->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="px-2 py-1.5 rounded-lg text-[10px] font-bold border transition {{ $room->status === 'live' ? 'bg-red-950/70 hover:bg-red-900 text-red-300 border-red-800/60' : 'bg-emerald-950/70 hover:bg-emerald-900 text-emerald-300 border-emerald-800/60' }}">
                                                            {{ $room->status === 'live' ? 'Close Table' : 'Go Live' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="glass-panel p-12 text-center rounded-2xl border-slate-800 bg-[#0c1324]/90 text-slate-400">
                <span class="text-4xl block mb-2">🎲</span>
                <p class="text-sm font-semibold text-white">No Games Found</p>
                <p class="text-xs text-slate-500 mt-1">Get started by creating your first game module.</p>
                <button type="button" onclick="openCreateGameModal()" class="btn-gold px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl mt-4">
                    + Create Game
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Create Game Modal -->
<div id="modal-create-game" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="w-full max-w-md bg-[#0c1324] border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-base font-bold font-royal text-white flex items-center gap-2">
                <span>🎮</span> Create New Game
            </h3>
            <button type="button" onclick="closeModal('modal-create-game')" class="text-slate-400 hover:text-white text-lg">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.games.store') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Game Title / Name</label>
                <input type="text" name="name" required placeholder="e.g. Royal Andar Bahar" class="form-input-custom">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Initial Status</label>
                <select name="status" class="form-input-custom">
                    <option value="open">Active / Open</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="closed">Closed / Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Configured Start Time (Optional)</label>
                <input type="datetime-local" name="start_time" class="form-input-custom">
                <span class="text-[10px] text-slate-500 mt-1 block">Leave empty to launch immediately.</span>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                <button type="button" onclick="closeModal('modal-create-game')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold">
                    Cancel
                </button>
                <button type="submit" class="btn-gold px-4 py-2 font-bold uppercase rounded-xl">
                    Create Game
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Edit Game Modal -->
<div id="modal-edit-game" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="w-full max-w-md bg-[#0c1324] border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-base font-bold font-royal text-white flex items-center gap-2">
                <span>✏️</span> Edit Game
            </h3>
            <button type="button" onclick="closeModal('modal-edit-game')" class="text-slate-400 hover:text-white text-lg">✕</button>
        </div>
        <form id="form-edit-game" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Game Title / Name</label>
                <input type="text" id="edit-game-name" name="name" required class="form-input-custom">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Status</label>
                <select id="edit-game-status" name="status" class="form-input-custom">
                    <option value="open">Active / Open</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="closed">Closed / Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Configured Start Time</label>
                <input type="datetime-local" id="edit-game-start-time" name="start_time" class="form-input-custom">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                <button type="button" onclick="closeModal('modal-edit-game')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold">
                    Cancel
                </button>
                <button type="submit" class="btn-gold px-4 py-2 font-bold uppercase rounded-xl">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Create Room Modal -->
<div id="modal-create-room" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="w-full max-w-lg bg-[#0c1324] border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <h3 class="text-base font-bold font-royal text-white flex items-center gap-2">
                    <span>🃏</span> Add Room / Table
                </h3>
                <span id="create-room-game-title" class="text-xs text-amber-400 font-semibold"></span>
            </div>
            <button type="button" onclick="closeModal('modal-create-room')" class="text-slate-400 hover:text-white text-lg">✕</button>
        </div>
        <form id="form-create-room" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Table / Room Name</label>
                <input type="text" name="name" required placeholder="e.g. VIP Lounge Table 2" class="form-input-custom">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Betting Duration (Sec)</label>
                    <input type="number" name="betting_duration" value="30" min="5" max="300" required class="form-input-custom">
                    <span class="text-[10px] text-slate-500 mt-1 block">Default: 30 seconds</span>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Cancellation Window (Sec)</label>
                    <input type="number" name="cancellation_duration" value="30" min="0" max="300" required class="form-input-custom">
                    <span class="text-[10px] text-slate-500 mt-1 block">Default: 30 seconds</span>
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Betting Denominations (Comma Separated)</label>
                <input type="text" name="allowed_denominations" value="100, 500, 1000, 2000, 5000" placeholder="100, 500, 1000, 2000, 5000" class="form-input-custom">
                <span class="text-[10px] text-slate-500 mt-1 block">Chips available for players to bet.</span>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Live Stream Source URL (Optional)</label>
                <input type="text" name="live_stream_url" placeholder="YouTube Embed URL, HLS/m3u8, or MP4 URL" class="form-input-custom">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Table Status</label>
                    <select name="status" class="form-input-custom">
                        <option value="live">Live / Active</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Start Time (Optional)</label>
                    <input type="datetime-local" name="start_time" class="form-input-custom">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Opening Time</label>
                    <input type="text" name="opening_time" value="11:15 AM" placeholder="e.g. 11:15 AM" class="form-input-custom">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Closing Time</label>
                    <input type="text" name="closing_time" value="10:00 PM" placeholder="e.g. 10:00 PM" class="form-input-custom">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                <button type="button" onclick="closeModal('modal-create-room')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold">
                    Cancel
                </button>
                <button type="submit" class="btn-gold px-4 py-2 font-bold uppercase rounded-xl">
                    Create Table
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 4. Edit Room Modal -->
<div id="modal-edit-room" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="w-full max-w-lg bg-[#0c1324] border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-base font-bold font-royal text-white flex items-center gap-2">
                <span>⚙️</span> Edit Table / Room Configurations
            </h3>
            <button type="button" onclick="closeModal('modal-edit-room')" class="text-slate-400 hover:text-white text-lg">✕</button>
        </div>
        <form id="form-edit-room" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Table / Room Name</label>
                <input type="text" id="edit-room-name" name="name" required class="form-input-custom">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Betting Duration (Sec)</label>
                    <input type="number" id="edit-room-betting-duration" name="betting_duration" min="5" max="300" required class="form-input-custom">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Cancellation Window (Sec)</label>
                    <input type="number" id="edit-room-cancel-duration" name="cancellation_duration" min="0" max="300" required class="form-input-custom">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Betting Denominations (Comma Separated)</label>
                <input type="text" id="edit-room-denominations" name="allowed_denominations" class="form-input-custom">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 uppercase">Live Stream Source URL</label>
                <input type="text" id="edit-room-stream-url" name="live_stream_url" class="form-input-custom">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Table Status</label>
                    <select id="edit-room-status" name="status" class="form-input-custom">
                        <option value="live">Live / Active</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Start Time</label>
                    <input type="datetime-local" id="edit-room-start-time" name="start_time" class="form-input-custom">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Opening Time</label>
                    <input type="text" id="edit-room-opening-time" name="opening_time" placeholder="e.g. 11:15 AM" class="form-input-custom">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5 uppercase">Closing Time</label>
                    <input type="text" id="edit-room-closing-time" name="closing_time" placeholder="e.g. 10:00 PM" class="form-input-custom">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                <button type="button" onclick="closeModal('modal-edit-room')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold">
                    Cancel
                </button>
                <button type="submit" class="btn-gold px-4 py-2 font-bold uppercase rounded-xl">
                    Save Configurations
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    }

    function openModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.remove('hidden');
            el.classList.add('flex');
        }
    }

    function openCreateGameModal() {
        openModal('modal-create-game');
    }

    function openEditGameModal(id, name, status, startTime) {
        document.getElementById('form-edit-game').action = `/admin/games/${id}`;
        document.getElementById('edit-game-name').value = name;
        document.getElementById('edit-game-status').value = status;
        document.getElementById('edit-game-start-time').value = startTime;
        openModal('modal-edit-game');
    }

    function openCreateRoomModal(gameId, gameName) {
        document.getElementById('form-create-room').action = `/admin/games/${gameId}/rooms`;
        document.getElementById('create-room-game-title').textContent = `Game: ${gameName}`;
        openModal('modal-create-room');
    }

    function openEditRoomModal(id, name, bettingDur, cancelDur, denoms, streamUrl, status, startTime, openingTime, closingTime) {
        document.getElementById('form-edit-room').action = `/admin/rooms/${id}`;
        document.getElementById('edit-room-name').value = name;
        document.getElementById('edit-room-betting-duration').value = bettingDur;
        document.getElementById('edit-room-cancel-duration').value = cancelDur;
        document.getElementById('edit-room-denominations').value = denoms;
        document.getElementById('edit-room-stream-url').value = streamUrl;
        document.getElementById('edit-room-status').value = status;
        document.getElementById('edit-room-start-time').value = startTime;
        document.getElementById('edit-room-opening-time').value = openingTime || '11:15 AM';
        document.getElementById('edit-room-closing-time').value = closingTime || '10:00 PM';
        openModal('modal-edit-room');
    }
</script>
@endsection
