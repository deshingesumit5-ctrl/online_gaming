@extends('layouts.admin')

@section('page-title', 'Live Control Room: ' . $room->name)

@section('content')
<div class="space-y-5">
    <!-- Top Status Bar -->
    <div class="glass-panel p-4 sm:p-5 flex flex-wrap items-center justify-between gap-4 border-amber-500/20">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center text-slate-950 font-black text-lg sm:text-xl font-royal shadow-lg shrink-0">
                🎮
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg font-bold font-royal text-white">{{ $room->name }}</h1>
                    <span class="badge-live px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">LIVE</span>
                </div>
                <span class="text-xs text-slate-400">Current Active Round: <strong class="text-amber-400 font-bold">#{{ $currentRound->round_number }}</strong> &bull; Status: <strong class="text-slate-200 uppercase font-mono">{{ $currentRound->status }}</strong></span>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <a href="{{ route('admin.game.control.index') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-bold border border-slate-700 transition flex items-center gap-1.5">
                <span>&larr;</span> Back to Rooms Table
            </a>

            @if(isset($allRooms) && count($allRooms) > 1)
                <select onchange="window.location.href = this.value" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-amber-300 font-bold focus:outline-none focus:border-amber-500 cursor-pointer">
                    @foreach($allRooms as $r)
                        <option value="{{ route('admin.game.control', $r->id) }}" {{ $r->id === $room->id ? 'selected' : '' }}>
                            {{ $r->name }} (#{{ $r->id }})
                        </option>
                    @endforeach
                </select>
            @endif

            <form method="POST" action="{{ route('admin.game.action', $room->id) }}">
                @csrf
                <input type="hidden" name="action" value="create_new_round">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold border border-slate-700 transition">
                    + New Round Sequence
                </button>
            </form>
        </div>
    </div>

    <!-- PRIMARY 4-STEP CONTROL MATRIX (Exact Ordered Sequence 1 -> 2 -> 3 -> 4) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        
        <!-- STEP 1: Assign Open First Card (Joker) -->
        <div class="glass-panel p-5 border-slate-800 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-black text-xs shrink-0">1</span>
                    <span>Assign Open First Card (Joker)</span>
                </h3>
                @if($currentRound->first_card)
                    <span class="text-xs font-bold text-emerald-400">Card Set: {{ strtoupper(str_replace('_', ' ', $currentRound->first_card)) }}</span>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.game.action', $room->id) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="action" value="start_round">

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 max-h-44 overflow-y-auto p-2 bg-slate-900/80 rounded-xl border border-slate-800 scrollbar-thin">
                    @foreach($cardDeck as $card)
                        <label class="cursor-pointer">
                            <input type="radio" name="first_card" value="{{ $card['code'] }}" class="peer hidden" {{ $currentRound->first_card === $card['code'] ? 'checked' : '' }}>
                            <div class="p-2 text-center rounded-lg bg-slate-800 peer-checked:bg-amber-500 peer-checked:text-slate-950 hover:bg-slate-700 text-xs font-bold transition">
                                <span class="{{ $card['color'] }} peer-checked:text-slate-950">{{ $card['label'] }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="btn-gold w-full py-2.5 text-xs font-bold uppercase tracking-wider">
                    Set Dealer First Card & Start Round
                </button>
            </form>
        </div>

        <!-- STEP 2: Start Live Stream and below Start and End button -->
        <div class="glass-panel p-5 border-slate-800 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-black text-xs shrink-0">2</span>
                    <span>Start Live Stream</span>
                </h3>
                <div class="flex items-center gap-1.5">
                    @if($room->live_stream_url)
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-indigo-900/60 text-indigo-300 border border-indigo-700/50" title="{{ $room->live_stream_url }}">
                            📹 CCTV LINK
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700" title="Webcam will be used">
                            📷 WEBCAM
                        </span>
                    @endif
                    <span id="admin-stream-status-badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $room->is_streaming ? 'bg-red-600 text-white animate-pulse' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                        {{ $room->is_streaming ? '🔴 LIVE STREAMING' : '⚪ STREAM OFFLINE' }}
                    </span>
                </div>
            </div>

            <!-- Live Camera Screen (Matching User Panel: Video when running, White Screen when ended) -->
            <div class="relative w-full rounded-xl overflow-hidden border border-slate-700/80 bg-white shadow-inner flex items-center justify-center" style="height: 175px;">
                <!-- Live Camera Video Element (Local Device Webcam) -->
                <video id="admin-live-camera" class="w-full h-full object-cover hidden" autoplay muted playsinline></video>

                <!-- External / CCTV Live Stream Player Container -->
                <div id="admin-cctv-stream-container" class="w-full h-full absolute inset-0 hidden bg-black">
                    <video id="admin-cctv-video" class="w-full h-full object-cover hidden" autoplay muted loop playsinline></video>
                    <iframe id="admin-cctv-iframe" class="w-full h-full border-0 hidden" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>

                <!-- Live Stream Canvas for Frame Capture -->
                <canvas id="admin-stream-canvas" class="hidden" width="480" height="270"></canvas>

                <!-- White Screen (Shown when stream is ended/offline as like User Panel) -->
                <div id="admin-stream-white-screen" class="absolute inset-0 bg-white flex flex-col items-center justify-center text-slate-700 select-none p-4 {{ $room->is_streaming ? 'hidden' : '' }}">
                    <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-300 flex items-center justify-center text-lg mb-1 shadow-sm">
                        🎥
                    </div>
                    <span class="text-xs font-black uppercase tracking-wider text-slate-800">Live Camera Standby</span>
                    <span class="text-[10px] text-slate-500 mt-0.5">
                        @if($room->live_stream_url)
                            Click Start below to broadcast CCTV link to players
                        @else
                            Click Start below to stream dealer camera to players
                        @endif
                    </span>
                </div>
            </div>

            <!-- Below Start and End button -->
            <div class="grid grid-cols-2 gap-3 mt-3">
                <button type="button" id="btn-stream-start" onclick="startLiveCameraStream()" class="w-full py-2.5 px-3 rounded-xl font-black text-xs uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-500 active:scale-95 shadow-md shadow-emerald-600/30 transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>▶ START</span>
                </button>
                <button type="button" id="btn-stream-end" onclick="endLiveCameraStream()" class="w-full py-2.5 px-3 rounded-xl font-black text-xs uppercase tracking-wider text-white bg-red-600 hover:bg-red-500 active:scale-95 shadow-md shadow-red-600/30 transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>⏹ END</span>
                </button>
            </div>
        </div>

        <!-- STEP 3: Betting Window Control -->
        <div class="glass-panel p-5 border-slate-800 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-black text-xs shrink-0">3</span>
                    <span>Betting Window Control</span>
                </h3>
                <span class="text-xs text-slate-400 font-mono">Status: <strong class="text-amber-400">{{ strtoupper($currentRound->status) }}</strong></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 my-auto">
                <form method="POST" action="{{ route('admin.game.action', $room->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="open_betting">
                    <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-xs sm:text-sm uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition flex flex-col items-center justify-center gap-0.5">
                        <span>⏱️ OPEN BETTING ({{ $room->betting_duration }}s)</span>
                        <small class="text-[9px] sm:text-[10px] font-normal opacity-80">Triggers live countdown for players</small>
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.game.action', $room->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="close_betting">
                    <button type="submit" class="w-full py-3.5 bg-amber-700 hover:bg-amber-600 text-white rounded-xl font-black text-xs sm:text-sm uppercase tracking-wider shadow-lg transition flex flex-col items-center justify-center gap-0.5">
                        <span>🔒 LOCK / CLOSE BETTING</span>
                        <small class="text-[9px] sm:text-[10px] font-normal opacity-80">Immediately locks placed bets</small>
                    </button>
                </form>
            </div>
        </div>

        <!-- STEP 4: Declare Result and Execute (Andar and Bahar) -->
        <div class="glass-panel p-5 border-amber-500/30 bg-amber-950/10 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-black text-xs shrink-0">4</span>
                    <span>Declare Result & Execute (Andar & Bahar)</span>
                </h3>
                @if($currentRound->status === 'result_declared')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $currentRound->winning_side === 'andar' ? 'badge-andar' : 'badge-bahar' }}">
                        {{ $currentRound->winning_side }} WON
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-3 my-auto">
                <!-- ANDAR WON -->
                <form method="POST" action="{{ route('admin.game.action', $room->id) }}" id="form-andar" onsubmit="return false;">
                    @csrf
                    <input type="hidden" name="action" value="declare_result">
                    <input type="hidden" name="winning_side" value="andar">
                    <button type="button" onclick="openConfirmModal('form-andar','Confirm declaration: ANDAR WON?','This will immediately credit 1:1 winnings to all ANDAR bettors.','andar')" class="btn-andar w-full py-3.5 text-center flex flex-col items-center justify-center gap-0.5" {{ $currentRound->status === 'result_declared' ? 'disabled' : '' }}>
                        <span class="text-base sm:text-lg font-black font-royal tracking-widest text-white">ANDAR WON</span>
                        <span class="text-[10px] font-normal text-indigo-200">1:1 Auto Payout</span>
                    </button>
                </form>

                <!-- BAHAR WON -->
                <form method="POST" action="{{ route('admin.game.action', $room->id) }}" id="form-bahar" onsubmit="return false;">
                    @csrf
                    <input type="hidden" name="action" value="declare_result">
                    <input type="hidden" name="winning_side" value="bahar">
                    <button type="button" onclick="openConfirmModal('form-bahar','Confirm declaration: BAHAR WON?','This will immediately credit 1:1 winnings to all BAHAR bettors.','bahar')" class="btn-bahar w-full py-3.5 text-center flex flex-col items-center justify-center gap-0.5" {{ $currentRound->status === 'result_declared' ? 'disabled' : '' }}>
                        <span class="text-base sm:text-lg font-black font-royal tracking-widest text-white">BAHAR WON</span>
                        <span class="text-[10px] font-normal text-red-200">1:1 Auto Payout</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- BELOW: RIGHT SIDE TABLES & CONTROLS (Bet Book & Volume, Active Bets, Recent Closed Rounds, Settings) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 pt-1">
        
        <!-- Table 1: Round # Bet Book & Volume -->
        <div class="glass-panel p-5 border-slate-800 flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-3">Round #{{ $currentRound->round_number }} Bet Book & Volume</h3>
                
                <div class="grid grid-cols-2 gap-3 text-center mb-3">
                    <div class="p-3 bg-indigo-950/60 rounded-xl border border-indigo-700/50">
                        <span class="text-[10px] uppercase font-bold text-indigo-300 block">Andar Total</span>
                        <span class="text-base sm:text-lg font-black text-white">{{ number_format($totalAndarAmount, 0) }}</span>
                        <span class="text-[10px] text-slate-400 block">{{ count($andarBets) }} bet(s)</span>
                    </div>

                    <div class="p-3 bg-red-950/60 rounded-xl border border-red-700/50">
                        <span class="text-[10px] uppercase font-bold text-red-300 block">Bahar Total</span>
                        <span class="text-base sm:text-lg font-black text-white">{{ number_format($totalBaharAmount, 0) }}</span>
                        <span class="text-[10px] text-slate-400 block">{{ count($baharBets) }} bet(s)</span>
                    </div>
                </div>
            </div>

            <div class="text-xs text-slate-400 flex justify-between border-t border-slate-800 pt-3">
                <span>Total Round Pool:</span>
                <strong class="text-amber-300 font-bold">{{ number_format($totalAndarAmount + $totalBaharAmount, 0) }} pts</strong>
            </div>
        </div>

        <!-- Table 2: Active Bets Placed in This Round -->
        <div class="glass-panel p-5 border-slate-800 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold font-royal text-white">Active Bets ({{ count($activeBets) }})</h3>
                <span class="text-xs font-normal text-slate-400">Auto-Refreshes</span>
            </div>

            <div class="space-y-2 max-h-56 overflow-y-auto pr-1 scrollbar-thin">
                @forelse($activeBets as $bet)
                    <div class="p-2.5 bg-slate-900 rounded-lg border border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded font-bold uppercase {{ $bet->selection === 'andar' ? 'badge-andar' : 'badge-bahar' }}">
                                {{ $bet->selection }}
                            </span>
                            <span class="text-slate-200 font-semibold truncate max-w-[110px]">{{ $bet->user->name ?? 'Player' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-white font-bold block">{{ number_format($bet->amount, 0) }} pts</span>
                            <span class="text-[10px] uppercase font-bold {{ $bet->status === 'won' ? 'text-emerald-400' : ($bet->status === 'lost' ? 'text-slate-500' : 'text-amber-400') }}">
                                {{ $bet->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500 text-xs">No bets placed in this round yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Table 3: Recent Rounds History & Settings -->
        <div class="glass-panel p-5 border-slate-800 flex flex-col justify-between space-y-4">
            <div>
                <h4 class="text-xs font-bold uppercase text-slate-400 mb-2.5">Recent Closed Rounds</h4>
                <div class="space-y-1.5 max-h-32 overflow-y-auto pr-1 scrollbar-thin">
                    @forelse($recentRounds as $r)
                        <div class="p-2 bg-slate-900 rounded-lg border border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-300 font-bold">Round #{{ $r->round_number }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $r->winning_side === 'andar' ? 'badge-andar' : 'badge-bahar' }}">
                                {{ $r->winning_side }} WON
                            </span>
                            <span class="text-slate-500 text-[10px]">{{ $r->closed_at ? $r->closed_at->format('h:i A') : '-' }}</span>
                        </div>
                    @empty
                        <div class="text-slate-500 text-xs text-center py-2">No historical rounds yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Timing Controls Mini Row -->
            <div class="border-t border-slate-800 pt-3">
                <form method="POST" action="{{ route('admin.game.action', $room->id) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="action" value="update_room_timings">
                    <div class="flex-1">
                        <input type="number" name="betting_duration" value="{{ $room->betting_duration }}" min="5" max="300" required placeholder="Bet Sec" class="form-input-custom text-xs py-1.5" title="Betting duration seconds">
                    </div>
                    <div class="flex-1">
                        <input type="number" name="cancellation_duration" value="{{ $room->cancellation_duration }}" min="0" max="300" required placeholder="Cancel Sec" class="form-input-custom text-xs py-1.5" title="Cancel window seconds">
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-slate-950 font-black rounded-lg text-xs uppercase tracking-wider transition shrink-0">
                        Save
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Live Camera Source Link Section (CCTV / External Stream URL) -->
    <div class="glass-panel p-5 border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <span class="text-base">📹</span>
                <span>Live Camera Source Link (CCTV / HLS / YouTube)</span>
            </h4>
            @if($room->live_stream_url)
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-emerald-400 font-mono truncate max-w-md bg-emerald-950/40 px-2.5 py-1 rounded border border-emerald-800/50" title="{{ $room->live_stream_url }}">
                        Active Link: {{ $room->live_stream_url }}
                    </span>
                    <form method="POST" action="{{ route('admin.game.action', $room->id) }}" class="inline">
                        @csrf
                        <input type="hidden" name="action" value="update_stream">
                        <input type="hidden" name="live_stream_url" value="">
                        <button type="submit" class="text-[10px] font-bold px-2 py-1 bg-red-950/80 hover:bg-red-900 text-red-300 rounded border border-red-800/60 transition cursor-pointer" title="Remove link and switch back to laptop webcam">
                            ✕ Clear Link
                        </button>
                    </form>
                </div>
            @endif
        </div>
        <form method="POST" action="{{ route('admin.game.action', $room->id) }}" class="space-y-2">
            @csrf
            <input type="hidden" name="action" value="update_stream">
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" id="admin-cctv-link-input" name="live_stream_url" value="{{ $room->live_stream_url }}" placeholder="Enter Live Camera / CCTV Link (e.g. https://.../stream.m3u8, YouTube Live URL, or MP4 URL)"
                       class="form-input-custom text-xs flex-1" oninput="checkStreamInputType(this.value)">
                <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-slate-950 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition shadow shrink-0">
                    Save Link
                </button>
            </div>
        
        </form>
    </div>
</div>

{{-- Custom Square Confirm Modal --}}
<div id="confirmModal" class="fixed inset-0 z-50 flex items-center justify-center hidden" style="background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);">
    <div id="confirmBox" class="relative flex flex-col items-center justify-center gap-5 rounded-2xl shadow-2xl border border-slate-700"
         style="width:420px;height:420px;background:linear-gradient(135deg,#0f172a 60%,#1e293b 100%);padding:2.5rem;">

        {{-- Icon --}}
        <div id="confirmIcon" class="w-16 h-16 rounded-2xl flex items-center justify-center text-4xl shadow-lg"></div>

        {{-- Title --}}
        <h2 id="confirmTitle" class="text-xl font-black font-royal text-white text-center leading-tight"></h2>

        {{-- Body --}}
        <p id="confirmBody" class="text-sm text-slate-300 text-center"></p>

        {{-- Buttons --}}
        <div class="flex gap-4 mt-2 w-full">
            <button onclick="closeConfirmModal()" class="flex-1 py-3 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-sm transition">
                Cancel
            </button>
            <button id="confirmOkBtn" onclick="submitConfirmedForm()" class="flex-1 py-3 rounded-xl font-bold text-sm text-white transition shadow-lg">
                ✓ Confirm
            </button>
        </div>
    </div>
</div>

{{-- Custom Square Result Declaration Banner in Center of Laptop --}}
<div id="resultBannerModal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4" style="background:rgba(0,0,0,0.65);backdrop-filter:blur(5px);">
    <div class="relative flex flex-col items-center justify-between p-7 rounded-3xl shadow-2xl border transition-all"
         style="width:360px;height:360px;max-width:92vw;max-height:92vw;background:radial-gradient(circle at 50% 20%,#064e3b 0%,#022c22 60%,#061814 100%);border-color:#10b981;box-shadow:0 0 50px rgba(16,185,129,0.4);">
        
        {{-- Close X Button at Top-Right --}}
        <button onclick="closeResultBannerModal()" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-lg font-bold transition border border-white/20 hover:scale-110 active:scale-95" title="Close (X)">
            ✕
        </button>

        {{-- Icon --}}
        <div class="mt-2">
            <div id="resultModalIcon" class="w-16 h-16 rounded-2xl bg-emerald-500/20 border-2 border-emerald-400 flex items-center justify-center text-emerald-400 text-3xl font-black shadow-lg shadow-emerald-500/30">
                ✓
            </div>
        </div>

        {{-- Title & Body --}}
        <div class="text-center px-2 my-2 flex flex-col items-center justify-center flex-grow">
            <h3 class="text-xs font-bold uppercase tracking-widest text-emerald-400 mb-1.5 font-royal">
                Result Declared Successfully
            </h3>
            <p id="resultModalMessage" class="text-white text-sm sm:text-base font-semibold leading-relaxed">
                Result declared: Bahar Won! Payouts credited automatically.
            </p>
        </div>

        {{-- Bottom OK Button --}}
        <button onclick="closeResultBannerModal()" class="w-full py-2.5 rounded-xl font-black text-xs uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-500 transition shadow-lg shadow-emerald-600/40 hover:brightness-110 active:scale-95">
            OK
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.7/dist/hls.min.js"></script>
<script>
    let _pendingFormId = null;
    let _pendingWinningSide = null;

    // Live Camera Streaming Setup
    let adminMediaStream = null;
    let frameBroadcastInterval = null;
    let adminHls = null;
    const currentRoomId = {{ $room->id }};
    const configuredStreamUrl = @json($room->live_stream_url ?? '');
    const streamChannel = ('BroadcastChannel' in window) ? new BroadcastChannel('fun2win_room_' + currentRoomId) : null;
    const canvasForFrames = document.getElementById('admin-stream-canvas');
    const canvasCtx = canvasForFrames ? canvasForFrames.getContext('2d') : null;

    function checkStreamInputType(val) {
        const warningEl = document.getElementById('rtsp-warning-badge');
        if (!warningEl) return;
        if (val && val.trim().toLowerCase().startsWith('rtsp://')) {
            warningEl.classList.remove('hidden');
        } else {
            warningEl.classList.add('hidden');
        }
    }

    function parseStreamUrl(url) {
        if (!url) return null;
        url = url.trim();
        const ytMatch = /(?:youtube\.com\/(?:watch\?v=|embed\/|live\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/i.exec(url);
        if (ytMatch) {
            return {
                type: 'youtube',
                embedUrl: 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1&mute=1&playsinline=1&enablejsapi=1&rel=0'
            };
        }
        if (url.toLowerCase().includes('.m3u8')) {
            return { type: 'hls', streamUrl: url };
        }
        return { type: 'video', streamUrl: url };
    }

    async function startLiveCameraStream() {
        try {
            const whiteScreen = document.getElementById('admin-stream-white-screen');
            const badge = document.getElementById('admin-stream-status-badge');
            const cctvContainer = document.getElementById('admin-cctv-stream-container');
            const cctvVideo = document.getElementById('admin-cctv-video');
            const cctvIframe = document.getElementById('admin-cctv-iframe');
            const webcamVideo = document.getElementById('admin-live-camera');

            const parsed = parseStreamUrl(configuredStreamUrl);

            if (parsed) {
                // 1. CCTV / External Stream Link Mode (No webcam permission needed)
                if (webcamVideo) webcamVideo.classList.add('hidden');
                if (cctvContainer) cctvContainer.classList.remove('hidden');

                if (parsed.type === 'youtube') {
                    if (cctvVideo) cctvVideo.classList.add('hidden');
                    if (cctvIframe) {
                        cctvIframe.src = parsed.embedUrl;
                        cctvIframe.classList.remove('hidden');
                    }
                } else if (parsed.type === 'hls') {
                    if (cctvIframe) cctvIframe.classList.add('hidden');
                    if (cctvVideo) {
                        cctvVideo.classList.remove('hidden');
                        if (Hls.isSupported()) {
                            if (adminHls) adminHls.destroy();
                            adminHls = new Hls({ enableWorker: true, lowLatencyMode: true });
                            adminHls.loadSource(parsed.streamUrl);
                            adminHls.attachMedia(cctvVideo);
                            adminHls.on(Hls.Events.MANIFEST_PARSED, () => {
                                cctvVideo.play().catch(() => {});
                            });
                        } else if (cctvVideo.canPlayType('application/vnd.apple.mpegurl')) {
                            cctvVideo.src = parsed.streamUrl;
                            cctvVideo.play().catch(() => {});
                        }
                    }
                } else {
                    // Direct MP4 / WebM video link
                    if (cctvIframe) cctvIframe.classList.add('hidden');
                    if (cctvVideo) {
                        cctvVideo.classList.remove('hidden');
                        cctvVideo.src = parsed.streamUrl;
                        cctvVideo.play().catch(() => {});
                    }
                }

                if (badge) {
                    badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-red-600 text-white animate-pulse';
                    badge.textContent = '🔴 LIVE (CCTV LINK)';
                }
            } else {
                // 2. Laptop Webcam Mode (Fallback when no stream link is configured)
                if (cctvContainer) cctvContainer.classList.add('hidden');
                if (!adminMediaStream) {
                    adminMediaStream = await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' },
                        audio: false
                    });
                }
                if (webcamVideo) {
                    webcamVideo.srcObject = adminMediaStream;
                    webcamVideo.classList.remove('hidden');
                    webcamVideo.play().catch(() => {});
                }
                if (badge) {
                    badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-red-600 text-white animate-pulse';
                    badge.textContent = '🔴 LIVE (WEBCAM)';
                }
            }

            if (whiteScreen) whiteScreen.classList.add('hidden');

            // Signal server to set is_streaming = true
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.content : '';
            await fetch("{{ route('admin.game.action', $room->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ action: 'start_stream' })
            });

            // Start broadcasting frames to user panel if video element is active
            const activeVideoEl = parsed ? (parsed.type !== 'youtube' ? cctvVideo : null) : webcamVideo;
            if (frameBroadcastInterval) clearInterval(frameBroadcastInterval);

            if (activeVideoEl) {
                frameBroadcastInterval = setInterval(() => {
                    if (!activeVideoEl || activeVideoEl.paused || activeVideoEl.ended || !canvasCtx) return;
                    try {
                        canvasCtx.drawImage(activeVideoEl, 0, 0, canvasForFrames.width, canvasForFrames.height);
                        const frameData = canvasForFrames.toDataURL('image/jpeg', 0.55);

                        if (streamChannel) {
                            streamChannel.postMessage({ type: 'stream_frame', frame: frameData, is_streaming: true, live_stream_url: configuredStreamUrl });
                        }

                        fetch("{{ route('admin.game.stream.frame.upload', $room->id) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({ frame: frameData })
                        }).catch(() => {});
                    } catch (e) {}
                }, 300);
            }

            if (streamChannel) {
                streamChannel.postMessage({ type: 'stream_started', is_streaming: true, live_stream_url: configuredStreamUrl });
            }
        } catch (err) {
            console.error('Camera/Stream start error:', err);
            alert('Could not start stream: ' + (err.message || 'Please check stream link or camera permission.'));
        }
    }

    async function endLiveCameraStream() {
        const whiteScreen = document.getElementById('admin-stream-white-screen');
        const badge = document.getElementById('admin-stream-status-badge');
        const cctvContainer = document.getElementById('admin-cctv-stream-container');
        const cctvVideo = document.getElementById('admin-cctv-video');
        const cctvIframe = document.getElementById('admin-cctv-iframe');
        const webcamVideo = document.getElementById('admin-live-camera');

        if (frameBroadcastInterval) {
            clearInterval(frameBroadcastInterval);
            frameBroadcastInterval = null;
        }

        if (adminHls) {
            adminHls.destroy();
            adminHls = null;
        }

        if (adminMediaStream) {
            adminMediaStream.getTracks().forEach(track => track.stop());
            adminMediaStream = null;
        }

        if (cctvVideo) {
            cctvVideo.pause();
            cctvVideo.removeAttribute('src');
            cctvVideo.load();
            cctvVideo.classList.add('hidden');
        }
        if (cctvIframe) {
            cctvIframe.src = 'about:blank';
            cctvIframe.classList.add('hidden');
        }
        if (cctvContainer) cctvContainer.classList.add('hidden');

        if (webcamVideo) {
            webcamVideo.pause();
            webcamVideo.srcObject = null;
            webcamVideo.classList.add('hidden');
        }

        if (whiteScreen) whiteScreen.classList.remove('hidden');
        if (badge) {
            badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700';
            badge.textContent = '⚪ STREAM OFFLINE';
        }

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.content : '';
        await fetch("{{ route('admin.game.action', $room->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ action: 'end_stream' })
        });

        if (streamChannel) {
            streamChannel.postMessage({ type: 'stream_ended', is_streaming: false });
        }
    }

    function openConfirmModal(formId, title, body, side) {
        _pendingFormId = formId;
        _pendingWinningSide = side;
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmBody').textContent  = body;

        const icon = document.getElementById('confirmIcon');
        const btn  = document.getElementById('confirmOkBtn');
        btn.disabled = false;
        btn.innerHTML = '✓ Confirm';

        if (side === 'andar') {
            icon.style.background = 'linear-gradient(135deg,#4338ca,#6366f1)';
            icon.textContent = '♠';
            btn.style.background  = 'linear-gradient(135deg,#4338ca,#6366f1)';
            btn.style.boxShadow   = '0 4px 20px rgba(99,102,241,0.5)';
        } else {
            icon.style.background = 'linear-gradient(135deg,#991b1b,#ef4444)';
            icon.textContent = '♥';
            btn.style.background  = 'linear-gradient(135deg,#991b1b,#ef4444)';
            btn.style.boxShadow   = '0 4px 20px rgba(239,68,68,0.5)';
        }

        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
        const submitBtn = document.getElementById('confirmOkBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '✓ Confirm';
        }
        _pendingFormId = null;
    }

    function showResultBannerModal(message, side) {
        document.getElementById('resultModalMessage').textContent = message;
        const icon = document.getElementById('resultModalIcon');
        if (icon) {
            icon.textContent = side === 'andar' ? '♠' : (side === 'bahar' ? '♥' : '✓');
        }
        document.getElementById('resultBannerModal').classList.remove('hidden');
    }

    function closeResultBannerModal() {
        document.getElementById('resultBannerModal').classList.add('hidden');
        window.location.reload();
    }

    async function submitConfirmedForm() {
        if (!_pendingFormId) return;
        const form = document.getElementById(_pendingFormId);
        const submitBtn = document.getElementById('confirmOkBtn');
        const winningSide = _pendingWinningSide;

        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Processing...';

        try {
            const formActionUrl = form.getAttribute('action');
            const formData = new FormData(form);
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.content : '';

            const res = await fetch(formActionUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await res.json();
            closeConfirmModal();

            if (res.ok && data && data.success) {
                const btnAndar = document.querySelector('#form-andar button');
                const btnBahar = document.querySelector('#form-bahar button');
                if (btnAndar) btnAndar.disabled = true;
                if (btnBahar) btnBahar.disabled = true;

                showResultBannerModal(data.message, winningSide);
            } else {
                alert((data && data.message) ? data.message : 'Declaration failed. Please try again.');
            }
        } catch (err) {
            console.error('AJAX declare error, falling back to standard submit:', err);
            form.onsubmit = null;
            HTMLFormElement.prototype.submit.call(form);
        }
    }

    // Close on backdrop click
    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if (e.target === this) closeConfirmModal();
    });
    document.getElementById('resultBannerModal').addEventListener('click', function(e) {
        if (e.target === this) closeResultBannerModal();
    });

    // If room is already streaming on page load, auto-initialize camera
    @if($room->is_streaming)
        document.addEventListener('DOMContentLoaded', () => {
            startLiveCameraStream();
        });
    @endif
</script>
@endsection
