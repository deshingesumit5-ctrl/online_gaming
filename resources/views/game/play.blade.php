@extends('layouts.app')

@section('title', $room->name . ' - Live Table')

@push('styles')
<style>
    /* Image 5 Style Custom Tokens */
    .casino-felt-table {
        background: radial-gradient(circle at 50% 30%, #525862 0%, #3a3f47 55%, #2a2d34 100%);
        box-shadow: inset 0 0 100px rgba(0,0,0,0.8);
    }
    
    /* Poker Chips Matching Image 5 */
    .poker-chip {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 10px;
        color: #f1f5f9;
        cursor: pointer;
        position: relative;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        border: 2px dashed rgba(255,255,255,0.4);
        box-shadow: 0 4px 10px rgba(0,0,0,0.6), inset 0 0 0 2px rgba(0,0,0,0.3);
        user-select: none;
    }
    @media (min-width: 640px) {
        .poker-chip {
            width: 40px;
            height: 40px;
            font-size: 11px;
        }
    }
    .poker-chip:hover {
        transform: translateY(-2px) scale(1.05);
    }
    .poker-chip.selected {
        transform: translateY(-4px) scale(1.1);
        box-shadow: 0 0 15px #f59e0b, 0 6px 15px rgba(0,0,0,0.8);
        border-color: #fef08a;
    }
    .chip-100   { background: radial-gradient(circle, #2563eb, #1e3a8a); }
    .chip-500   { background: radial-gradient(circle, #475569, #1e293b); }
    .chip-1000  { background: radial-gradient(circle, #2563eb, #1e3a8a); }
    .chip-2000  { background: radial-gradient(circle, #7c3aed, #4c1d95); }
    .chip-5000  { background: radial-gradient(circle, #dc2626, #7f1d1d); }
    .chip-10000 { background: radial-gradient(circle, #d97706, #78350f); }

    /* Fanned Deck Representation */
    .fanned-card {
        width: 12px;
        height: 40px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 2px;
        position: relative;
        box-shadow: -1px 2px 4px rgba(0,0,0,0.3);
    }

    /* History Beads (Image 5) */
    .bead-a-green { background-color: #10b981; color: white; }
    .bead-a-blue  { background-color: #0284c7; color: white; }
    .bead-b-red   { background-color: #ef4444; color: white; }

    /* Fullscreen HUD Container: Perfectly fits laptop & mobile screens without page scrolling */
    .game-viewport {
        height: 100vh;
        max-height: 100vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .game-viewport .felt-surface {
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    /* Horizontal Aadavi Mode for Mobile & Landscape Viewports */
    @media (orientation: landscape) and (max-height: 550px) {
        .landscape-compact-bar {
            padding-top: 3px !important;
            padding-bottom: 3px !important;
        }
        .landscape-compact-hud {
            padding: 4px 10px !important;
            gap: 8px !important;
        }
        .landscape-compact-hud .poker-chip {
            width: 32px !important;
            height: 32px !important;
            font-size: 9px !important;
        }
        .landscape-compact-hud .btn-hud-action {
            padding: 4px 12px !important;
            font-size: 11px !important;
        }
        .hud-andar-bahar-box {
            width: 250px !important;
        }
        .hud-andar-bahar-box #btn-bet-andar,
        .hud-andar-bahar-box #btn-bet-bahar {
            padding: 6px 12px !important;
            font-size: 13px !important;
        }
    }
    @media (orientation: landscape) {
        #mobile-aadavi-prompt {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="w-full h-full max-w-none mx-auto flex flex-col justify-between overflow-hidden relative select-none game-viewport" style="background: #000;">

    <!-- Mobile Portrait Helper Prompt (Tapping enters Fullscreen Landscape / Aadavi) -->
    <div id="mobile-aadavi-prompt" onclick="enterFullscreenLandscape()"
         class="hidden fixed top-14 left-1/2 -translate-x-1/2 z-50 bg-amber-500 text-slate-950 font-black px-4 py-2 rounded-full text-xs uppercase tracking-wider shadow-2xl flex items-center gap-2 cursor-pointer border-2 border-white animate-pulse">
        <span>🔄⛶</span>
        <span>Tap for Fullscreen Aadavi</span>
    </div>

    <!-- Top Bar (Matching Image 4: ← TABLE 1 : MIN BET 500, Center (✕) Close, Right Action Icons) -->
    <div class="relative z-30 px-3 sm:px-5 py-2.5 flex items-center justify-between text-white bg-black/50 backdrop-blur-md border-b border-white/10 shrink-0">
        <!-- Left: Back Navigation & Table Title -->
        <div class="flex items-center gap-3 font-royal">
            <a href="{{ route('dashboard', ['tab' => 'lobby']) }}" class="text-white hover:text-amber-400 transition text-base font-bold flex items-center gap-1.5" title="Back to Lobby">
                <span>&larr;</span>
            </a>
            <h1 class="text-xs sm:text-sm md:text-base font-black tracking-wider uppercase">
                {{ strtoupper($room->name) }} : MIN BET {{ $denominations[0] ?? 500 }}
            </h1>
        </div>

        <!-- Center: (✕) Circular Close Button (Matching Image 4) -->
        <div>
            <a href="{{ route('dashboard', ['tab' => 'lobby']) }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-black/60 hover:bg-black/90 border border-white/25 text-white flex items-center justify-center text-xs sm:text-sm font-bold transition hover:scale-110 active:scale-95 shadow-lg" title="Close / Return to Lobby">
                ✕
            </a>
        </div>

        <!-- Right: 3 Round Icon Buttons (History/Refresh, Sound, Fullscreen) -->
        <div class="flex items-center gap-2">
            <button type="button" id="btn-refresh-state" class="w-7 h-7 rounded-full bg-black/60 hover:bg-black/80 border border-white/20 text-white flex items-center justify-center text-xs transition active:scale-95" title="Refresh Live State">
                ↻
            </button>
            <button type="button" id="btn-toggle-sound" class="w-7 h-7 rounded-full bg-black/60 hover:bg-black/80 border border-white/20 text-white flex items-center justify-center text-xs transition active:scale-95" title="Toggle Sound">
                🔊
            </button>
            <button type="button" id="btn-toggle-fullscreen" class="w-7 h-7 rounded-full bg-black/60 hover:bg-black/80 border border-white/20 text-white flex items-center justify-center text-xs transition active:scale-95" title="Fullscreen">
                ⛶
            </button>
        </div>
    </div>

    <!-- Main Live Table Surface (Matching Image 4 Dealer Table Camera Stream) -->
    <div class="felt-surface relative flex-grow min-h-0 flex items-center justify-center overflow-hidden"
         style="background: #1e1a17 url('{{ asset('images/live-table-bg.jpg') }}') center center / cover no-repeat;">
        
        <!-- Live Stream Video / Camera Broadcast Container (Overlaid when stream is active) -->
        <div id="player-live-stream-box" class="absolute inset-0 z-0 bg-black flex items-center justify-center overflow-hidden {{ $room->is_streaming ? '' : 'hidden' }}">
            <!-- Live Camera Frame Image (broadcasted from Admin Live Camera) -->
            <img id="player-live-camera-img" class="w-full h-full object-cover" alt="Live Dealer Stream" src="">

            <!-- External / CCTV Live Stream Player Container -->
            <div id="player-external-stream-wrap" class="hidden absolute inset-0 bg-black">
                <video id="live-cctv-stream" class="w-full h-full object-cover hidden" autoplay muted loop playsinline></video>
                <iframe id="live-youtube-stream" class="w-full h-full border-0 hidden pointer-events-auto"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>

            <!-- Live Streaming Indicator Badge -->
            <div class="absolute top-3 left-3 z-10 flex items-center gap-2 bg-black/60 backdrop-blur-sm border border-red-500/40 px-2.5 py-1 rounded-full">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                <span class="text-[10px] font-black uppercase tracking-wider text-red-400">LIVE DEALER</span>
            </div>
        </div>

        <!-- Joker First Card Slot Overlaid on Felt (Hidden / Clean) -->
        <div class="hidden">
            <span id="first-card-val-top">{{ $currentRound->first_card ? strtoupper(explode('_', $currentRound->first_card)[0]) : '4' }}</span>
            <span id="first-card-suit-top">♣</span>
            <span id="first-card-val-bottom">{{ $currentRound->first_card ? strtoupper(explode('_', $currentRound->first_card)[0]) : '4' }}</span>
            <span id="first-card-suit-bottom">♣</span>
        </div>

    </div>

    <!-- Bottom Casino Cockpit HUD Bar (Matching Image 4 Overlaid HUD) -->
    <div class="relative z-30 p-2 sm:p-3.5 bg-black/90 backdrop-blur-md border-t border-white/15 text-white shrink-0">
        <div class="flex flex-col landscape:flex-row lg:flex-row items-center justify-between gap-2.5 sm:gap-4 landscape-compact-hud">
            
            <!-- LEFT SECTION: Chips, Undo + Place Bet, Balance + First Bet / Second Bet -->
            <div class="flex flex-col gap-2 w-full lg:w-auto landscape:w-auto">
                
                <!-- Chips Row -->
                <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                    @php
                        $chipColorClasses = [
                            500   => 'chip-green',
                            1000  => 'chip-silver',
                            2000  => 'chip-purple',
                            5000  => 'chip-pink',
                            10000 => 'chip-gold',
                        ];
                    @endphp
                    @foreach($denominations as $idx => $denom)
                        @php
                            $label = $denom >= 1000 ? ($denom / 1000) . 'k' : $denom;
                            $colorClass = $chipColorClasses[$denom] ?? 'chip-green';
                        @endphp
                        <div class="poker-chip {{ $colorClass }} {{ $idx === 0 ? 'selected' : '' }}" 
                             data-value="{{ $denom }}" onclick="selectPokerChip({{ $denom }}, this)">
                            <span>{{ $label }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Action Buttons: Undo & Place Bet (and Cancel Bet) -->
                <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
                    <button type="button" id="btn-hud-undo" onclick="handleUndoBet()"
                            class="btn-hud-action px-4 sm:px-6 py-1.5 sm:py-2 rounded-lg bg-[#991b1b] hover:bg-[#b91c1c] active:scale-95 text-white font-black text-xs sm:text-sm uppercase tracking-wider transition shadow-md cursor-pointer">
                        UNDO
                    </button>

                    <button type="button" id="btn-hud-place-bet" onclick="handleConfirmBet()"
                            class="btn-hud-action px-5 sm:px-8 py-1.5 sm:py-2 rounded-lg bg-[#16a34a] hover:bg-[#22c55e] active:scale-95 text-white font-black text-xs sm:text-sm uppercase tracking-wider transition shadow-md shadow-emerald-700/40 cursor-pointer">
                        PLACE BET
                    </button>

                    <button type="button" id="btn-hud-cancel-bet" onclick="handleCancelActiveBet()"
                            class="btn-hud-action hidden px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-red-700 hover:bg-red-600 border border-red-500 text-white font-black text-xs uppercase tracking-wider transition active:scale-95 shadow-lg shadow-red-700/50 flex items-center gap-1.5 animate-pulse">
                        <span>↩ CANCEL</span>
                        <span id="cancel-timer-countdown" class="px-1.5 py-0.5 rounded-full bg-black/60 text-[10px] font-bold text-amber-300">{{ $room->cancellation_duration }}s</span>
                    </button>
                </div>

                <!-- Readouts: Balance on left, First Bet & Second Bet stacked on right -->
                <div class="flex items-center gap-3 sm:gap-4 pt-0.5">
                    <!-- Balance -->
                    <div class="px-3 sm:px-3.5 py-1 sm:py-1.5 rounded-lg bg-black/80 border border-white/20 text-xs sm:text-sm font-bold shrink-0">
                        <span class="text-slate-300">BALANCE: <strong class="text-white font-black">₹<span class="user-wallet-balance">{{ number_format($user->wallet_balance, 0) }}</span></strong></span>
                    </div>

                    <!-- Stacked First Bet & Second Bet -->
                    <div class="flex flex-col text-[10px] sm:text-xs font-bold leading-tight space-y-0.5 sm:space-y-1">
                        <div class="text-slate-300">
                            FIRST BET: <strong class="text-white font-black" id="status-first-bet">₹0</strong>
                        </div>
                        <div class="text-slate-300">
                            SECOND BET: <strong class="text-white font-black" id="status-second-bet">₹0</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CENTER SECTION: ANDAR (Black) / BAHAR (Red) (Big Buttons, Matching Image 4) -->
            <div class="relative flex items-center justify-center w-full sm:w-80 md:w-96 lg:w-[400px] landscape:w-[260px] sm:landscape:w-[320px] hud-andar-bahar-box shrink-0 my-1 lg:my-0">
                <div class="w-full rounded-2xl overflow-hidden border-2 border-slate-700 bg-black shadow-2xl relative">
                    <!-- ANDAR Area (Black Bar, Big Button) -->
                    <div id="btn-bet-andar" onclick="selectBetSide('andar')"
                         class="px-5 py-3 sm:py-3.5 md:py-4 bg-[#181a22] border-b border-slate-700/80 flex items-center justify-between cursor-pointer hover:bg-slate-800 transition group select-none">
                        <span class="text-sm sm:text-base md:text-lg font-black font-royal tracking-widest text-white group-hover:text-indigo-300">
                            ANDAR
                        </span>
                        <div class="flex items-center gap-2 pr-10">
                            <span id="andar-bet-badge" class="text-xs sm:text-sm font-black text-amber-300"></span>
                        </div>
                    </div>

                    <!-- BAHAR Area (Red Bar, Big Button) -->
                    <div id="btn-bet-bahar" onclick="selectBetSide('bahar')"
                         class="px-5 py-3 sm:py-3.5 md:py-4 bg-[#dc2626] flex items-center justify-between cursor-pointer hover:bg-red-700 transition group select-none">
                        <span class="text-sm sm:text-base md:text-lg font-black font-royal tracking-widest text-white group-hover:text-red-100">
                            BAHAR
                        </span>
                        <div class="flex items-center gap-2 pr-10">
                            <span id="bahar-bet-badge" class="text-xs sm:text-sm font-black text-amber-300"></span>
                        </div>
                    </div>

                    <!-- Right Capsule Indicator (Matching Image 4) -->
                    <div class="absolute right-0 top-0 bottom-0 w-12 sm:w-14 bg-gradient-to-r from-transparent via-black/40 to-black/80 flex items-center justify-center pointer-events-none">
                        <div class="w-8 sm:w-9 h-14 sm:h-16 rounded-xl bg-gradient-to-b from-slate-900 via-slate-800 to-red-950 border border-white/20 flex flex-col items-center justify-center text-[11px] font-bold text-white shadow-inner">
                            <span>{{ $currentRound->first_card ? strtoupper(explode('_', $currentRound->first_card)[0]) : '4' }}</span>
                            <span class="text-red-400 text-xs sm:text-sm leading-none mt-0.5">★</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SECTION: Red Timer Bar, Bead Road Matrix, Limits (Matching Image 4) -->
            <div class="flex flex-col justify-between w-full sm:w-64 md:w-72 landscape:w-52 sm:landscape:w-64 shrink-0 space-y-1 sm:space-y-1.5">
                <!-- Red Countdown Timer Bar (Matching Image 4) -->
                <div class="w-full bg-slate-900 h-2 rounded-full overflow-hidden border border-white/10">
                    <div id="hud-timer-bar" class="h-full bg-red-600 transition-all duration-1000 ease-linear shadow-[0_0_8px_#dc2626]" style="width: 100%;"></div>
                </div>

                <!-- Bead Road Grid Matrix (Matching Image 4) -->
                <div class="bg-black/60 p-2 rounded-xl border border-white/10">
                    <!-- Row 1 of beads & dots -->
                    <div class="flex items-center gap-1.5 overflow-x-auto py-0.5 scrollbar-none">
                        <div class="bead-b-circle shrink-0">B</div>
                        <div class="bead-b-circle shrink-0">B</div>
                        <div class="bead-a-circle shrink-0">A</div>
                        <div class="bead-a-circle shrink-0">A</div>
                        <div class="bead-b-circle shrink-0">B</div>
                        <div class="bead-a-circle shrink-0">A</div>
                        <div class="bead-b-circle shrink-0">B</div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                    </div>
                    <!-- Row 2 of beads & dots -->
                    <div class="flex items-center gap-1.5 overflow-x-auto py-0.5 scrollbar-none mt-1.5">
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                    </div>
                </div>

                <!-- Limits Display (Matching Image 4) -->
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 font-medium">
                        Bet: 0/500,000
                    </span>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Custom Square Alert/Warning Banner Modal Centered in Middle of Screen --}}
<div id="squareAlertModal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4" style="background:rgba(0,0,0,0.65);backdrop-filter:blur(5px);">
    <div class="relative flex flex-col items-center justify-between p-6 sm:p-7 rounded-3xl shadow-2xl border transition-all"
         style="width:340px;height:340px;max-width:92vw;max-height:92vw;background:radial-gradient(circle at 50% 20%,#1e293b 0%,#0f172a 60%,#050811 100%);border-color:#f59e0b;box-shadow:0 0 45px rgba(245,158,11,0.35);">
        
        {{-- Close X Button at Top-Right --}}
        <button onclick="closeSquareAlertModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-sm font-bold transition border border-white/20 hover:scale-110 active:scale-95" title="Close (X)">
            ✕
        </button>

        {{-- Icon --}}
        <div class="mt-2">
            <div id="squareAlertIcon" class="w-14 h-14 rounded-2xl bg-amber-500/20 border-2 border-amber-400 flex items-center justify-center text-amber-400 text-3xl font-black shadow-lg shadow-amber-500/30">
                ⚠️
            </div>
        </div>

        {{-- Title & Body --}}
        <div class="text-center px-2 my-2 flex flex-col items-center justify-center flex-grow">
            <h3 id="squareAlertTitle" class="text-xs font-bold uppercase tracking-widest text-amber-400 mb-2 font-royal">
                Selection Required
            </h3>
            <p id="squareAlertMessage" class="text-white text-sm font-semibold leading-relaxed">
                Please select ANDAR or BAHAR before placing your bet.
            </p>
        </div>

        {{-- Bottom OK Button --}}
        <button onclick="closeSquareAlertModal()" class="w-full py-2.5 rounded-xl font-black text-xs uppercase tracking-wider text-slate-950 bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-300 hover:to-yellow-300 transition shadow-lg shadow-amber-500/40 hover:brightness-110 active:scale-95">
            OK
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.7/dist/hls.min.js"></script>
<script src="{{ asset('js/game-engine.js') }}"></script>
<script>
    let activeSelectedChip = {{ $denominations[0] ?? 500 }};
    let activeSelectedSide = null;
    let gameEngineInstance = null;

    function selectPokerChip(val, el) {
        activeSelectedChip = parseInt(val, 10);
        document.querySelectorAll('.poker-chip').forEach(c => c.classList.remove('selected'));
        if (el) el.classList.add('selected');

        if (activeSelectedSide) {
            updateSideBadge(activeSelectedSide, activeSelectedChip);
        }
    }

    function selectBetSide(side) {
        activeSelectedSide = side;
        const andarBox = document.getElementById('btn-bet-andar');
        const baharBox = document.getElementById('btn-bet-bahar');

        if (side === 'andar') {
            andarBox.classList.add('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
            baharBox.classList.remove('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
            document.getElementById('status-first-bet').textContent = `₹${activeSelectedChip.toLocaleString()}`;
            document.getElementById('status-second-bet').textContent = `₹0`;
        } else {
            baharBox.classList.add('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
            andarBox.classList.remove('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
            document.getElementById('status-second-bet').textContent = `₹${activeSelectedChip.toLocaleString()}`;
            document.getElementById('status-first-bet').textContent = `₹0`;
        }

        updateSideBadge(side, activeSelectedChip);
    }

    function updateSideBadge(side, amount) {
        const andarBadge = document.getElementById('andar-bet-badge');
        const baharBadge = document.getElementById('bahar-bet-badge');
        if (side === 'andar') {
            andarBadge.textContent = `₹${amount.toLocaleString()}`;
            baharBadge.textContent = '';
        } else {
            baharBadge.textContent = `₹${amount.toLocaleString()}`;
            andarBadge.textContent = '';
        }
    }

    function handleUndoBet() {
        activeSelectedSide = null;
        const andarBox = document.getElementById('btn-bet-andar');
        const baharBox = document.getElementById('btn-bet-bahar');
        andarBox.classList.remove('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
        baharBox.classList.remove('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
        document.getElementById('andar-bet-badge').textContent = '';
        document.getElementById('bahar-bet-badge').textContent = '';
        document.getElementById('status-first-bet').textContent = '₹0';
        document.getElementById('status-second-bet').textContent = '₹0';
    }

    function showSquareBanner(title, message) {
        if (title) document.getElementById('squareAlertTitle').textContent = title;
        if (message) document.getElementById('squareAlertMessage').textContent = message;
        document.getElementById('squareAlertModal').classList.remove('hidden');
    }

    function closeSquareAlertModal() {
        document.getElementById('squareAlertModal').classList.add('hidden');
    }

    async function handleConfirmBet() {
        if (!activeSelectedSide) {
            showSquareBanner('Selection Required', 'Please select ANDAR or BAHAR before placing your bet.');
            return;
        }

        if (gameEngineInstance) {
            gameEngineInstance.selectedChip = activeSelectedChip;
            await gameEngineInstance.placeBet(activeSelectedSide);
        }
    }

    const roomCancelDuration = {{ (int) $room->cancellation_duration }};
    let activeCancelBetId = null;
    let cancelTimerInterval = null;
    let remainingCancelSec = 0;

    function startCancelCountdown(betId, seconds) {
        activeCancelBetId = betId;
        remainingCancelSec = parseInt(seconds, 10);
        const btn = document.getElementById('btn-hud-cancel-bet');
        const countdownEl = document.getElementById('cancel-timer-countdown');

        if (isNaN(remainingCancelSec) || remainingCancelSec <= 0) {
            stopCancelCountdown();
            return;
        }

        if (btn) btn.classList.remove('hidden');
        if (countdownEl) countdownEl.textContent = `${remainingCancelSec}s`;

        if (cancelTimerInterval) clearInterval(cancelTimerInterval);
        cancelTimerInterval = setInterval(() => {
            remainingCancelSec--;
            if (countdownEl) {
                countdownEl.textContent = `${remainingCancelSec}s`;
            }
            if (remainingCancelSec <= 0) {
                stopCancelCountdown();
            }
        }, 1000);
    }

    function stopCancelCountdown() {
        if (cancelTimerInterval) {
            clearInterval(cancelTimerInterval);
            cancelTimerInterval = null;
        }
        activeCancelBetId = null;
        remainingCancelSec = 0;
        const btn = document.getElementById('btn-hud-cancel-bet');
        if (btn) btn.classList.add('hidden');
    }

    async function handleCancelActiveBet() {
        if (!activeCancelBetId || !gameEngineInstance) return;
        const betIdToCancel = activeCancelBetId;
        await gameEngineInstance.cancelBet(betIdToCancel);
    }

    window.onBetPlacedSuccess = function(data) {
        if (data && data.bet && data.cancel_duration > 0) {
            startCancelCountdown(data.bet.id, data.cancel_duration);
        }
    };

    window.onBetCancelledSuccess = function() {
        stopCancelCountdown();
        handleUndoBet();
    };

    document.addEventListener('DOMContentLoaded', () => {
        gameEngineInstance = new GameEngine({
            roomId: {{ $room->id }},
            stateUrl: "{{ route('game.state', $room->id) }}",
            betUrl: "{{ route('game.bet', $room->id) }}",
            cancelUrlBase: "{{ url('/game/bet') }}",
            csrfToken: "{{ csrf_token() }}",
            defaultChip: {{ $denominations[0] ?? 500 }},
            cancellationDuration: {{ (int) $room->cancellation_duration }}
        });

        // Hook timer bar into game engine and check cancel timer
        const oldRenderState = gameEngineInstance.renderState.bind(gameEngineInstance);
        gameEngineInstance.renderState = function(data) {
            oldRenderState(data);
            const timerBar = document.getElementById('hud-timer-bar');
            if (timerBar && data.betting_duration > 0) {
                const pct = Math.max(0, Math.min(100, (data.remaining_seconds / data.betting_duration) * 100));
                timerBar.style.width = `${pct}%`;
            }

            // Sync cancel countdown with active cancellable bet
            const cancellableBet = (data.user_bets || []).find(b => b.can_cancel && b.remaining_cancel_seconds > 0);
            if (cancellableBet) {
                if (!activeCancelBetId || activeCancelBetId !== cancellableBet.id) {
                    startCancelCountdown(cancellableBet.id, cancellableBet.remaining_cancel_seconds);
                }
            } else if (!cancellableBet && activeCancelBetId) {
                stopCancelCountdown();
            }

            // Sync Live Stream vs White Screen
            syncLiveStreamView(data.is_streaming, data.live_stream_url);
        };

        // BroadcastChannel Receiver for Real-Time Camera Stream
        const playerRoomId = {{ $room->id }};
        let lastBroadcastFrameTime = 0;
        if ('BroadcastChannel' in window) {
            const playerStreamChannel = new BroadcastChannel('fun2win_room_' + playerRoomId);
            playerStreamChannel.onmessage = (e) => {
                const msg = e.data;
                if (!msg) return;
                if (msg.type === 'stream_frame' && msg.frame) {
                    lastBroadcastFrameTime = Date.now();
                    const streamImg = document.getElementById('player-live-camera-img');
                    if (streamImg) streamImg.src = msg.frame;
                    syncLiveStreamView(true);
                } else if (msg.type === 'stream_started') {
                    syncLiveStreamView(true);
                } else if (msg.type === 'stream_ended') {
                    syncLiveStreamView(false);
                }
            };
        }

        // Cross-device fallback polling for stream frame when active
        setInterval(() => {
            if (window._isStreamActive && (Date.now() - lastBroadcastFrameTime > 800)) {
                fetch("{{ route('game.stream.frame.get', $room->id) }}")
                    .then(r => r.json())
                    .then(d => {
                        if (d && d.frame) {
                            const streamImg = document.getElementById('player-live-camera-img');
                            if (streamImg) streamImg.src = d.frame;
                        }
                    }).catch(() => {});
            }
        }, 500);

        let playerHls = null;

        function syncLiveStreamView(isStreaming, externalUrl) {
            window._isStreamActive = !!isStreaming;
            const streamBox = document.getElementById('player-live-stream-box');
            const whiteScreen = document.getElementById('player-stream-white-screen');
            const externalWrap = document.getElementById('player-external-stream-wrap');
            const cctvVideo = document.getElementById('live-cctv-stream');
            const ytIframe = document.getElementById('live-youtube-stream');
            const fallbackImg = document.getElementById('player-live-camera-img');

            const streamUrl = (externalUrl || @json($room->live_stream_url ?? ''))?.trim();

            if (isStreaming) {
                if (streamBox) streamBox.classList.remove('hidden');
                if (whiteScreen) whiteScreen.classList.add('hidden');

                if (streamUrl) {
                    if (externalWrap) externalWrap.classList.remove('hidden');
                    if (fallbackImg) fallbackImg.classList.add('hidden');

                    const ytMatch = /(?:youtube\.com\/(?:watch\?v=|embed\/|live\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/i.exec(streamUrl);
                    if (ytMatch) {
                        const ytSrc = 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1&mute=1&playsinline=1&enablejsapi=1&rel=0';
                        if (ytIframe) {
                            if (ytIframe.src !== ytSrc) ytIframe.src = ytSrc;
                            ytIframe.classList.remove('hidden');
                        }
                        if (cctvVideo) cctvVideo.classList.add('hidden');
                    } else if (streamUrl.toLowerCase().includes('.m3u8')) {
                        if (ytIframe) ytIframe.classList.add('hidden');
                        if (cctvVideo) {
                            cctvVideo.classList.remove('hidden');
                            if (Hls.isSupported()) {
                                if (!playerHls) {
                                    playerHls = new Hls({ enableWorker: true, lowLatencyMode: true });
                                    playerHls.loadSource(streamUrl);
                                    playerHls.attachMedia(cctvVideo);
                                    playerHls.on(Hls.Events.MANIFEST_PARSED, () => {
                                        cctvVideo.play().catch(() => {});
                                    });
                                }
                            } else if (cctvVideo.canPlayType('application/vnd.apple.mpegurl')) {
                                if (cctvVideo.src !== streamUrl) cctvVideo.src = streamUrl;
                                cctvVideo.play().catch(() => {});
                            }
                        }
                    } else {
                        // Direct video file/feed (MP4 / WebM)
                        if (ytIframe) ytIframe.classList.add('hidden');
                        if (cctvVideo) {
                            cctvVideo.classList.remove('hidden');
                            if (cctvVideo.src !== streamUrl) cctvVideo.src = streamUrl;
                            cctvVideo.play().catch(() => {});
                        }
                    }
                } else {
                    // Local admin webcam broadcast mode
                    if (externalWrap) externalWrap.classList.add('hidden');
                    if (fallbackImg) fallbackImg.classList.remove('hidden');
                }
            } else {
                if (streamBox) streamBox.classList.add('hidden');
                if (whiteScreen) whiteScreen.classList.remove('hidden');
                if (externalWrap) externalWrap.classList.add('hidden');
                if (playerHls) {
                    playerHls.destroy();
                    playerHls = null;
                }
                if (cctvVideo) {
                    cctvVideo.pause();
                    cctvVideo.removeAttribute('src');
                    cctvVideo.load();
                }
                if (ytIframe) {
                    ytIframe.src = 'about:blank';
                }
            }
        }

        // Fullscreen toggle (Locked to Landscape / Aadavi on mobile)
        window.enterFullscreenLandscape = async function() {
            const docEl = document.documentElement;
            const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);

            if (!isFs) {
                try {
                    const req = docEl.requestFullscreen || docEl.webkitRequestFullscreen || docEl.mozRequestFullScreen || docEl.msRequestFullscreen;
                    if (req) {
                        await req.call(docEl);
                    }
                } catch (e) {
                    console.warn('requestFullscreen error:', e);
                }

                // Lock orientation to Landscape (Aadavi)
                if (screen.orientation && screen.orientation.lock) {
                    try {
                        await screen.orientation.lock('landscape');
                    } catch (e) {
                        console.warn('orientation.lock landscape error:', e);
                    }
                }
            } else {
                if (screen.orientation && screen.orientation.unlock) {
                    try { screen.orientation.unlock(); } catch (e) {}
                }
                try {
                    const exit = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
                    if (exit) {
                        await exit.call(document);
                    }
                } catch (e) {
                    console.warn('exitFullscreen error:', e);
                }
            }
        };

        document.getElementById('btn-toggle-fullscreen')?.addEventListener('click', window.enterFullscreenLandscape);

        // Automatic orientation to Landscape (Aadavi) when entering room
        function applyLandscapeLock() {
            if (screen.orientation && screen.orientation.lock) {
                screen.orientation.lock('landscape').catch(() => {});
            }
        }
        applyLandscapeLock();
        window.addEventListener('load', applyLandscapeLock);
        document.addEventListener('touchstart', applyLandscapeLock, { once: true });
        document.addEventListener('click', applyLandscapeLock, { once: true });

        // Show/hide mobile portrait helper prompt
        function checkMobileOrientation() {
            const prompt = document.getElementById('mobile-aadavi-prompt');
            if (!prompt) return;
            const isPortrait = window.innerHeight > window.innerWidth;
            const isMobile = window.innerWidth <= 900 || /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent);
            if (isMobile && isPortrait) {
                prompt.classList.remove('hidden');
            } else {
                prompt.classList.add('hidden');
            }
        }
        window.addEventListener('resize', checkMobileOrientation);
        window.addEventListener('orientationchange', checkMobileOrientation);
        setTimeout(checkMobileOrientation, 300);

        // Sound toggle
        let soundOn = true;
        document.getElementById('btn-toggle-sound')?.addEventListener('click', function() {
            soundOn = !soundOn;
            this.textContent = soundOn ? '🔊' : '🔇';
        });

        // Refresh state
        document.getElementById('btn-refresh-state')?.addEventListener('click', () => {
            if (gameEngineInstance) gameEngineInstance.fetchState();
        });

        // Close square alert modal on backdrop click
        document.getElementById('squareAlertModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeSquareAlertModal();
        });
    });
</script>
@endpush
