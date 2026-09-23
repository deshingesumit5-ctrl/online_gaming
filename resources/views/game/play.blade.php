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

    #player-pen-marker {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
        opacity: 0 !important;
    }

    .live-card-overlay {
        position: absolute;
        left: 48%;
        top: 58%;
        width: 4.8%;
        height: auto;
        aspect-ratio: 118 / 168;
        flex: none;
        margin: 0;
        transform: translate(-50%, -50%);
        border-radius: 8%;
        background: #fff;
        border: 3px solid #111;
        box-shadow: 0 8px 24px rgba(0,0,0,0.55);
        z-index: 24;
        display: none;
        pointer-events: none;
        padding: 6px 8px;
        flex-direction: column;
        justify-content: space-between;
        font-weight: 900;
        line-height: 0.9;
        font-family: Arial, Helvetica, sans-serif;
        color: #dc2626;
    }
    .live-card-overlay.is-visible { display: flex; }
    .live-card-overlay .card-index {
        display: flex;
        flex-direction: column;
        align-items: center;
        line-height: 0.85;
        font-weight: 900;
        color: #dc2626;
    }
    .live-card-overlay .rank {
        font-size: 22px;
        font-weight: 900;
        letter-spacing: -1px;
        color: inherit;
        text-shadow: 0 1px 0 #fff;
    }
    .live-card-overlay .card-index .index-suit { font-size: 14px; line-height: 1; }
    .live-card-overlay .card-index-br { transform: rotate(180deg); }
    .live-card-overlay .card-pip-panel {
        flex: 1;
        margin: 2px 12px;
        background: #f4e8a4;
        border: 1.5px solid #222;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        align-content: space-evenly;
        overflow: hidden;
        padding: 3px 4px;
    }
    .live-card-overlay .suit { font-size: 22px; text-align: center; line-height: 1; color: #dc2626; width: 46%; }
    .live-card-overlay .card-pip-panel[data-pips="1"] .suit,
    .live-card-overlay .card-pip-panel[data-pips="2"] .suit,
    .live-card-overlay .card-pip-panel[data-pips="3"] .suit { width: 100%; font-size: 32px; }
    .live-card-overlay.is-red { color: #dc2626; }
    .live-card-overlay.is-black { color: #dc2626; }
    .live-card-overlay .card-index,
    .live-card-overlay .card-pip-panel { display: none; }
    .live-card-overlay {
        padding: 0;
        overflow: hidden;
        background: transparent;
        border: 0;
    }
    .live-card-overlay .card-photo {
        width: 100%;
        height: 100%;
        object-fit: fill;
        display: block;
        pointer-events: none;
        border-radius: 8%;
    }


    /* Black Side Panels & Image 2 Layout */
    .panel-black-sidebar {
        width: clamp(120px, 17vw, 210px);
        background: #000000;
    }
    .panel-left {
        left: 0;
        top: 0;
        bottom: 0;
    }
    .panel-right {
        right: 0;
        top: 0;
        bottom: 0;
    }
    .vertical-bead-card {
        background: radial-gradient(circle at 50% 25%, #2a080e 0%, #150306 100%);
    }
    .center-hud-anchor {
        left: clamp(120px, 17vw, 210px);
        right: clamp(120px, 17vw, 210px);
    }

    @media (max-width: 639px) {
        .panel-black-sidebar {
            width: clamp(94px, 19vw, 130px);
            padding: 4px !important;
        }
        .center-hud-anchor {
            left: clamp(94px, 19vw, 130px);
            right: clamp(94px, 19vw, 130px);
        }
        .poker-chip {
            width: 30px !important;
            height: 30px !important;
            font-size: 8.5px !important;
        }
        .btn-hud-action {
            padding: 3px 5px !important;
            font-size: 9px !important;
        }
        .vertical-bead-card {
            padding: 4px 6px !important;
            max-width: 90px !important;
        }
        .vertical-bead-card .bead-b-circle,
        .vertical-bead-card .bead-a-circle {
            width: 13px !important;
            height: 13px !important;
            font-size: 7.5px !important;
        }
        .vertical-bead-card .bead-dot {
            width: 2.5px !important;
            height: 2.5px !important;
        }
        .hud-andar-bahar-box {
            max-width: 240px !important;
            margin-bottom: 2px !important;
        }
        .hud-andar-bahar-box #btn-bet-andar,
        .hud-andar-bahar-box #btn-bet-bahar {
            padding: 4px 8px !important;
            font-size: 11px !important;
        }
    }

    @media (orientation: landscape) and (max-height: 550px) {
        .panel-black-sidebar {
            width: clamp(110px, 17vw, 155px);
            padding: 4px 6px !important;
        }
        .center-hud-anchor {
            left: clamp(110px, 17vw, 155px);
            right: clamp(110px, 17vw, 155px);
        }
        .poker-chip {
            width: 32px !important;
            height: 32px !important;
            font-size: 9px !important;
        }
        .btn-hud-action {
            padding: 4px 6px !important;
            font-size: 10px !important;
        }
        .vertical-bead-card {
            padding: 3px 5px !important;
            max-width: 95px !important;
        }
        .vertical-bead-card .bead-b-circle,
        .vertical-bead-card .bead-a-circle {
            width: 14px !important;
            height: 14px !important;
            font-size: 8px !important;
        }
        .vertical-bead-card .bead-dot {
            width: 3px !important;
            height: 3px !important;
        }
        .hud-andar-bahar-box {
            max-width: 260px !important;
            margin-bottom: 2px !important;
        }
        .hud-andar-bahar-box #btn-bet-andar,
        .hud-andar-bahar-box #btn-bet-bahar {
            padding: 4px 10px !important;
            font-size: 12px !important;
        }
    }

    /* Mobile Portrait Lock / Rotate Screen Overlay (Matching Image 3) */
    #device-rotate-overlay {
        display: none;
    }
    @media screen and (orientation: portrait) and (max-width: 1024px) {
        #device-rotate-overlay {
            display: flex !important;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            z-index: 999999;
            background-color: #000000;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px;
            user-select: none;
        }
        #game-main-viewport {
            overflow: hidden !important;
            height: 100vh !important;
        }
    }
    .hud-andar-bahar-box #btn-bet-both {
        transition: all 0.2s ease;
    }
</style>
@endpush

@section('content')
<!-- ============================================================== -->
<!-- MOBILE ROTATE DEVICE OVERLAY (Matches Image 3 Exactly)         -->
<!-- Appears only in mobile portrait mode; blocks vertical screen   -->
<!-- ============================================================== -->
<div id="device-rotate-overlay" class="flex flex-col items-center justify-center bg-black text-white select-none">
    <div class="relative w-28 h-28 sm:w-32 sm:h-32 flex items-center justify-center mb-6">
        <svg class="w-full h-full text-slate-500" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Phone in portrait orientation -->
            <rect x="22" y="16" width="34" height="56" rx="6" stroke="#475569" stroke-width="3" fill="none" />
            <line x1="34" y1="22" x2="44" y2="22" stroke="#475569" stroke-width="2.5" stroke-linecap="round" />
            <circle cx="39" cy="65" r="2" fill="#475569" />

            <!-- Phone in landscape orientation -->
            <rect x="32" y="44" width="56" height="34" rx="6" stroke="#64748b" stroke-width="3" fill="#090d16" />
            <line x1="38" y1="61" x2="38" y2="71" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" />
            <circle cx="81" cy="61" r="2" fill="#64748b" />

            <!-- Curved Rotation Arrow -->
            <path d="M 60 22 C 72 24, 82 34, 84 46" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" fill="none" />
            <polygon points="84,49 80,42 88,42" fill="#38bdf8" />
        </svg>
    </div>

    <h2 class="text-xl sm:text-2xl font-black text-white tracking-wide mb-2 font-sans">
        Please Rotate Your Device
    </h2>
    <p class="text-slate-400 text-xs sm:text-sm font-medium max-w-xs text-center leading-relaxed">
        This application works best in landscape mode.
    </p>

    <button type="button" onclick="requestFullScreenIfLandscape()" class="mt-6 px-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 text-xs font-bold hover:text-white transition">
        Tap to enable Fullscreen
    </button>
</div>

<div id="game-main-viewport" class="w-full h-full max-w-none mx-auto flex flex-col justify-between overflow-hidden relative select-none game-viewport" style="background: #000;">

    <!-- Top Bar (PDF Page 17: Fun2Win Logo, Wallet Points, Notifications, Profile, Close) -->
    <div class="relative z-30 px-2 sm:px-5 py-2 flex items-center justify-between text-white bg-black/60 backdrop-blur-md border-b border-white/10 shrink-0 gap-2">
        <!-- Left: Back Navigation & Table Title & Logo -->
        <div class="flex items-center gap-2 sm:gap-3 font-royal min-w-0">
            <a href="{{ route('dashboard', ['tab' => 'lobby']) }}" class="text-white hover:text-amber-400 transition text-sm sm:text-base font-bold flex items-center gap-1 shrink-0" title="Back to Lobby">
                <span>&larr;</span>
            </a>
            <img src="{{ asset('images/logo.png') }}" alt="Fun 2 Win" class="w-6 h-6 sm:w-7 sm:h-7 object-contain rounded shrink-0">
            <h1 class="text-xs sm:text-sm font-black tracking-wider uppercase truncate">
                {{ strtoupper($room->name) }}
            </h1>
            <span id="session-id-display" class="text-[10px] sm:text-xs text-amber-300 font-mono hidden md:inline">Session #{{ $currentRound->round_number }}</span>
            <span id="round-status-badge" class="px-2 py-0.5 text-[9px] sm:text-[10px] font-bold rounded-full bg-slate-700 text-slate-200 shrink-0">{{ strtoupper(str_replace('_', ' ', $currentRound->status)) }}</span>
        </div>

        <!-- Right: Wallet Points, Notifications, Profile, Refresh, Sound, Fullscreen, Close (PDF Page 17) -->
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
            <!-- Wallet Points Display (Page 17) -->
            <div class="px-2 sm:px-3 py-1 rounded-lg bg-black/80 border border-amber-400/50 text-[10px] sm:text-xs font-black text-amber-300 flex items-center gap-1 shadow-sm">
                <span class="text-[9px] text-slate-400 font-bold hidden xs:inline">PTS:</span>
                <span class="user-wallet-balance">{{ number_format($user->wallet_balance, 0) }}</span>
            </div>

            <!-- Notifications (Page 17) -->
            <a href="{{ route('dashboard', ['tab' => 'notifications']) }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-black/60 hover:bg-black/80 border border-white/20 text-white flex items-center justify-center text-xs transition active:scale-95" title="Notifications">
                🔔
            </a>

            <!-- Profile (Page 17) -->
            <a href="{{ route('profile') }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-black/60 hover:bg-black/80 border border-white/20 text-white flex items-center justify-center text-xs transition active:scale-95 font-bold" title="Profile">
                👤
            </a>

            <button type="button" id="btn-refresh-state" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-black/60 hover:bg-black/80 border border-white/20 text-white flex items-center justify-center text-xs transition active:scale-95 hidden sm:flex" title="Refresh Live State">
                ↻
            </button>
            <button type="button" id="btn-toggle-sound" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-black/60 hover:bg-black/80 border border-white/20 text-white flex items-center justify-center text-xs transition active:scale-95 hidden sm:flex" title="Toggle Sound">
                🔊
            </button>
            <button type="button" id="btn-toggle-fullscreen" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-black/60 hover:bg-black/80 border border-white/20 text-white flex items-center justify-center text-xs transition active:scale-95" title="Fullscreen">
                ⛶
            </button>

            <!-- Close Button (✕) -->
            <a href="{{ route('dashboard', ['tab' => 'lobby']) }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-red-950/80 hover:bg-red-800 border border-red-500/40 text-white flex items-center justify-center text-xs sm:text-sm font-black transition hover:scale-105 active:scale-95 shadow-lg ml-1" title="Close / Return to Lobby">
                ✕
            </a>
        </div>
    </div>

    <!-- Main Live Table Surface with Black Side Panels (Matching Image 2 Exactly) -->
    <div id="player-felt-surface" class="felt-surface relative flex-grow min-h-0 w-full overflow-hidden bg-black"
         style="background: {{ $room->is_streaming ? '#000' : '#1e1a17 url(\'' . asset('images/live-table-bg.jpg') . '\') center center / cover no-repeat' }};">
        
        <!-- Live Stream Video / Camera Broadcast Container (Overlaid when stream is active) -->
        <div id="player-live-stream-box" class="absolute inset-0 z-0 bg-black flex items-center justify-center overflow-hidden {{ $room->is_streaming ? '' : 'hidden' }}">
            <!-- Live Camera Frame Image (broadcasted from Admin Live Camera) -->
            <img id="player-live-camera-img" class="w-full h-full object-cover hidden" alt="Live Dealer Stream" src="">

            <!-- External / CCTV Live Stream Player Container -->
            <div id="player-external-stream-wrap" class="hidden absolute inset-0 bg-black">
                <video id="live-cctv-stream" class="w-full h-full object-cover hidden" autoplay muted playsinline disablepictureinpicture></video>
                <iframe id="live-youtube-stream" class="w-full h-full border-0 hidden pointer-events-auto"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
            <!-- Yellow pen marker hidden from users -->
            <div id="player-pen-marker" aria-hidden="true" style="display: none !important;"></div>
            <div id="player-live-card-overlay" class="live-card-overlay" aria-hidden="true">
                <img class="card-photo" src="{{ asset('images/overlay-9-hearts.jpg') }}" alt="9 of Hearts">
                <div class="card-index">
                    <div class="rank" id="player-live-card-rank"></div>
                    <div class="index-suit" id="player-live-card-index-suit"></div>
                </div>
                <div class="card-pip-panel" id="player-live-card-pips">
                    <div class="suit" id="player-live-card-suit"></div>
                </div>
                <div class="card-index card-index-br">
                    <div class="rank" id="player-live-card-rank-b"></div>
                    <div class="index-suit" id="player-live-card-index-suit-b"></div>
                </div>
            </div>
            <div id="player-live-wait-cover" class="absolute inset-0 z-40 bg-black {{ $room->is_streaming ? '' : 'hidden' }}"></div>

            <!-- Live Streaming Indicator Badge -->
            <div class="absolute top-2.5 left-[clamp(120px,18vw,220px)] ml-2 sm:ml-3 z-10 flex items-center gap-1.5 sm:gap-2 bg-black/70 backdrop-blur-sm border border-red-500/40 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[9px] sm:text-[10px]">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                <span class="font-black uppercase tracking-wider text-red-400">LIVE DEALER</span>
                <span class="text-slate-500">&bull;</span>
                <span class="font-bold text-slate-200">👥 <span id="player-count-display">{{ $room->active_users_count ?? 1 }}</span> Players</span>
            </div>
            <div id="player-game-status-banner" class="absolute top-11 left-1/2 -translate-x-1/2 z-20 px-3 py-1.5 rounded-lg bg-black/70 border border-white/15 text-[10px] sm:text-xs font-black uppercase tracking-wider text-amber-300 text-center max-w-[80%]"></div>
        </div>

        <!-- Joker First Card Slot Overlaid on Felt (Hidden / Clean) -->
        <div class="hidden">
            <span id="first-card-val-top">{{ $currentRound->first_card ? strtoupper(explode('_', $currentRound->first_card)[0]) : '4' }}</span>
            <span id="first-card-suit-top">♣</span>
            <span id="first-card-val-bottom">{{ $currentRound->first_card ? strtoupper(explode('_', $currentRound->first_card)[0]) : '4' }}</span>
            <span id="first-card-suit-bottom">♣</span>
        </div>

        <!-- ============================================================== -->
        <!-- 1. LEFT BLACK SCREEN PANEL (Matching Image 2 Exactly)          -->
        <!-- ============================================================== -->
        <div id="panel-left-black" class="panel-black-sidebar panel-left absolute left-0 top-0 bottom-0 z-20 flex flex-col justify-between p-2 sm:p-3 bg-black select-none border-r border-white/10 shadow-2xl">
            <!-- Upper Section: Poker Chips Vertically Stacked -->
            <div class="flex flex-col items-center justify-center flex-1 my-auto py-1 sm:py-2 gap-2 sm:gap-3">
                @php
                    $chipColorClasses = [
                        500   => 'chip-green',
                        1000  => 'chip-silver',
                        2000  => 'chip-purple',
                        5000  => 'chip-pink',
                        10000 => 'chip-gold',
                    ];
                @endphp
                <div class="flex flex-col items-center gap-1.5 sm:gap-2.5">
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
                    <input type="number" id="manual-bet-amount" min="500" max="1000000" step="1" placeholder="Amt" class="w-16 sm:w-20 px-1.5 py-0.5 sm:py-1 rounded-lg bg-black/80 border border-white/20 text-[10px] sm:text-[11px] font-bold text-white text-center mt-0.5 sm:mt-1" title="Enter 500 or more points">
                </div>
            </div>

            <!-- Lower Section: Action Buttons & Balance (Matching Image 2) -->
            <div class="flex flex-col gap-1.5 sm:gap-2 shrink-0 pt-1">
                <!-- Action Buttons: UNDO & PLACE BET -->
                <div class="grid grid-cols-2 gap-1 sm:gap-1.5">
                    <button type="button" id="btn-hud-undo" onclick="handleUndoBet()"
                            class="btn-hud-action py-1.5 sm:py-2 px-1 rounded-lg bg-[#991b1b] hover:bg-[#b91c1c] active:scale-95 text-white font-black text-[10px] sm:text-xs uppercase tracking-wider transition shadow-md cursor-pointer text-center">
                        UNDO
                    </button>

                    <button type="button" id="btn-hud-place-bet" onclick="handleConfirmBet()"
                            class="btn-hud-action py-1.5 sm:py-2 px-1 rounded-lg bg-[#16a34a] hover:bg-[#22c55e] active:scale-95 text-white font-black text-[10px] sm:text-xs uppercase tracking-wider transition shadow-md shadow-emerald-700/40 cursor-pointer text-center">
                        PLACE BET
                    </button>
                </div>

                <button type="button" id="btn-hud-cancel-bet" onclick="handleCancelActiveBet()"
                        class="btn-hud-action hidden py-1.5 px-2 rounded-lg bg-red-700 hover:bg-red-600 border border-red-500 text-white font-black text-[10px] sm:text-xs uppercase tracking-wider transition active:scale-95 shadow-lg shadow-red-700/50 flex items-center justify-center gap-1 animate-pulse">
                    <span>↩ CANCEL</span>
                    <span id="cancel-timer-countdown" class="px-1 py-0.5 rounded-full bg-black/60 text-[9px] font-bold text-amber-300">{{ $room->cancellation_duration }}s</span>
                </button>

                <!-- Balance Display Card (Matching Image 2 Rounded Box) -->
                <div class="p-1.5 sm:p-2 rounded-xl bg-black/90 border border-white/20 text-[10px] sm:text-xs font-bold leading-tight shadow-md">
                    <div class="text-slate-300 truncate">
                        BALANCE: <strong class="text-white font-black"><span class="user-wallet-balance">{{ number_format($user->wallet_balance, 0) }}</span></strong>
                    </div>
                    <div class="flex items-center justify-between text-[9px] sm:text-[10px] text-slate-400 mt-0.5 pt-0.5 border-t border-white/10">
                        <span>1ST: <strong class="text-white" id="status-first-bet">0</strong></span>
                        <span>2ND: <strong class="text-white" id="status-second-bet">0</strong></span>
                    </div>
                </div>

                <div id="session-bet-summary" class="hidden text-[8px] sm:text-[9px] font-medium text-slate-400 leading-tight space-y-0.5">
                    <div>Session Andar: <strong class="text-white" id="sum-andar">0</strong> pts</div>
                    <div>Session Bahar: <strong class="text-white" id="sum-bahar">0</strong> pts</div>
                    <div>Total: <strong class="text-amber-300" id="sum-total">0</strong> / <span id="sum-limit">10,00,000</span> pts</div>
                    <div>Remaining: <strong class="text-emerald-300" id="sum-remaining">10,00,000</strong> pts</div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 2. CENTER TABLE AREA: ANDAR / BAHAR BOX (Matching Image 2)     -->
        <!-- ============================================================== -->
        <div class="center-hud-anchor absolute bottom-2 sm:bottom-4 z-20 pointer-events-none flex justify-center items-center px-2">
            <div class="relative w-full max-w-[280px] sm:max-w-[340px] md:max-w-[380px] hud-andar-bahar-box pointer-events-auto">
                <div class="w-full rounded-2xl overflow-hidden border-2 border-slate-700 bg-black shadow-2xl relative">
                    <!-- ANDAR Area (Black Bar) -->
                    <div id="btn-bet-andar" onclick="selectBetSide('andar')"
                         class="px-4 sm:px-5 py-2.5 sm:py-3.5 bg-[#181a22] border-b border-slate-700/80 flex items-center justify-between cursor-pointer hover:bg-slate-800 transition group select-none">
                        <span class="text-xs sm:text-sm md:text-base font-black font-royal tracking-widest text-white group-hover:text-indigo-300">
                            ANDAR
                        </span>
                        <div class="flex items-center gap-2 pr-10">
                            <span id="andar-bet-badge" class="text-xs sm:text-sm font-black text-amber-300"></span>
                        </div>
                    </div>

                    <!-- BAHAR Area (Red Bar) -->
                    <div id="btn-bet-bahar" onclick="selectBetSide('bahar')"
                         class="px-4 sm:px-5 py-2.5 sm:py-3.5 bg-[#dc2626] flex items-center justify-between cursor-pointer hover:bg-red-700 transition group select-none">
                        <span class="text-xs sm:text-sm md:text-base font-black font-royal tracking-widest text-white group-hover:text-red-100">
                            BAHAR
                        </span>
                        <div class="flex items-center gap-2 pr-10">
                            <span id="bahar-bet-badge" class="text-xs sm:text-sm font-black text-amber-300"></span>
                        </div>
                    </div>

                    <!-- Right Capsule Indicator (Matching Image 2) -->
                    <div class="absolute right-0 top-0 bottom-0 w-11 sm:w-13 bg-gradient-to-r from-transparent via-black/40 to-black/80 flex items-center justify-center pointer-events-none">
                        <div class="w-7 sm:w-8 md:w-9 h-12 sm:h-14 md:h-15 rounded-xl bg-gradient-to-b from-slate-900 via-slate-800 to-red-950 border border-white/20 flex flex-col items-center justify-center text-[10px] sm:text-[11px] font-bold text-white shadow-inner">
                            <span id="hud-first-card-rank">{{ $currentRound->first_card ? strtoupper(explode('_', $currentRound->first_card)[0]) : '4' }}</span>
                            <span class="text-red-400 text-xs sm:text-sm leading-none mt-0.5">★</span>
                        </div>
                    </div>
                </div>

                <!-- ⚡ BOTH Button (Below ANDAR and BAHAR) -->
                <div class="mt-1 sm:mt-1.5 w-full">
                    <div id="btn-bet-both" onclick="openBothBetModal()"
                         class="w-full rounded-xl py-1.5 sm:py-2 px-3 sm:px-4 bg-gradient-to-r from-[#181a22] via-[#242b3d] to-[#7f1d1d] hover:brightness-110 border-2 border-amber-400/80 shadow-lg flex items-center justify-between cursor-pointer transition active:scale-[0.98] select-none group">
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span class="text-xs sm:text-sm font-black font-royal tracking-wider text-amber-300 group-hover:text-amber-200">
                                ⚡ BOTH
                            </span>
                            <span class="text-[9px] sm:text-[10px] text-slate-300 font-bold hidden xs:inline">(Andar + Bahar)</span>
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span id="both-bet-badge" class="text-[10px] sm:text-xs font-black text-amber-300"></span>
                            <span class="px-2 py-0.5 rounded bg-gradient-to-r from-amber-400 to-yellow-400 text-slate-950 text-[9px] sm:text-[10px] font-black uppercase tracking-wider shadow">SET BET</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 3. RIGHT BLACK SCREEN PANEL (Matching Image 2 Exactly)         -->
        <!-- ============================================================== -->
        <div id="panel-right-black" class="panel-black-sidebar panel-right absolute right-0 top-0 bottom-0 z-20 flex flex-col justify-between items-center p-2 sm:p-3 bg-black select-none border-l border-white/10 shadow-2xl">
            <!-- Countdown Timer Bar at Top of Right Panel -->
            <div class="w-full max-w-[120px] sm:max-w-[140px] bg-slate-900 h-1.5 sm:h-2 rounded-full overflow-hidden border border-white/10 shrink-0 mb-1 sm:mb-2">
                <div id="hud-timer-bar" class="h-full bg-red-600 transition-all duration-1000 ease-linear shadow-[0_0_8px_#dc2626]" style="width: 100%;"></div>
            </div>

            <!-- Vertical Bead Road Scorecard Card (Matching Image 2) -->
            <div class="vertical-bead-card flex-1 flex flex-col items-center justify-center my-auto p-1.5 sm:p-2.5 rounded-2xl sm:rounded-3xl border-2 border-red-900/60 shadow-xl bg-gradient-to-b from-[#28080e] via-[#1a0509] to-[#120306] w-full max-w-[105px] sm:max-w-[120px]">
                <div class="flex items-start justify-center gap-1.5 sm:gap-2.5 py-1">
                    <!-- Column 1: Bead circles B and A -->
                    <div class="flex flex-col items-center gap-1 sm:gap-1.5">
                        <div class="bead-b-circle shrink-0">B</div>
                        <div class="bead-b-circle shrink-0">B</div>
                        <div class="bead-a-circle shrink-0">A</div>
                        <div class="bead-a-circle shrink-0">A</div>
                        <div class="bead-b-circle shrink-0">B</div>
                        <div class="bead-a-circle shrink-0">A</div>
                        <div class="bead-b-circle shrink-0">B</div>
                    </div>
                    <!-- Column 2: Dots -->
                    <div class="flex flex-col items-center gap-2 sm:gap-2.5 py-1">
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                    </div>
                    <!-- Column 3: Dots -->
                    <div class="flex flex-col items-center gap-2 sm:gap-2.5 py-1">
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                        <div class="bead-dot shrink-0"></div>
                    </div>
                </div>
            </div>

            <!-- Limits Display at Bottom of Right Panel -->
            <div class="text-center shrink-0 pt-1">
                <span class="text-[9px] sm:text-[10px] text-slate-400 font-medium">
                    Bet: 0/500,000
                </span>
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

    {{-- Place Bet on Both Modal Dialog --}}
    <div id="bothBetModal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-3" style="background:rgba(0,0,0,0.8);backdrop-filter:blur(6px);">
        <div class="relative w-full max-w-[420px] rounded-3xl bg-gradient-to-b from-[#181c24] to-[#0b0e14] border-2 border-amber-400 shadow-2xl p-4 sm:p-5 text-white flex flex-col gap-3 max-h-[95vh] overflow-y-auto">
            <!-- Header with title & close button -->
            <div class="flex items-center justify-between border-b border-white/10 pb-2">
                <div class="flex items-center gap-2">
                    <span class="text-amber-400 text-lg">⚡</span>
                    <h3 class="text-xs sm:text-sm font-black font-royal uppercase tracking-wider text-amber-300">Place Bet on Both</h3>
                </div>
                <button type="button" onclick="closeBothBetModal()" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-xs font-bold transition">✕</button>
            </div>

            <!-- ANDAR Section -->
            <div class="bg-black/60 border border-slate-700/80 rounded-2xl p-2.5 sm:p-3">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs sm:text-sm font-black font-royal tracking-widest text-indigo-300 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> ANDAR BET
                    </span>
                    <span id="both-modal-andar-display" class="text-xs sm:text-sm font-black text-amber-300">500 pts</span>
                </div>
                <!-- Quick Chips for Andar -->
                <div class="flex items-center justify-between gap-1 mb-2">
                    <button type="button" onclick="setBothSideAmount('andar', 500)" class="flex-1 py-1 rounded-lg bg-slate-800 hover:bg-indigo-950 border border-white/15 text-[10px] sm:text-xs font-black">500</button>
                    <button type="button" onclick="setBothSideAmount('andar', 1000)" class="flex-1 py-1 rounded-lg bg-slate-800 hover:bg-indigo-950 border border-white/15 text-[10px] sm:text-xs font-black">1k</button>
                    <button type="button" onclick="setBothSideAmount('andar', 2000)" class="flex-1 py-1 rounded-lg bg-slate-800 hover:bg-indigo-950 border border-white/15 text-[10px] sm:text-xs font-black">2k</button>
                    <button type="button" onclick="setBothSideAmount('andar', 5000)" class="flex-1 py-1 rounded-lg bg-slate-800 hover:bg-indigo-950 border border-white/15 text-[10px] sm:text-xs font-black">5k</button>
                    <button type="button" onclick="setBothSideAmount('andar', 10000)" class="flex-1 py-1 rounded-lg bg-slate-800 hover:bg-indigo-950 border border-white/15 text-[10px] sm:text-xs font-black">10k</button>
                </div>
                <!-- Manual Input for Andar -->
                <div class="flex items-center gap-2">
                    <label class="text-[10px] text-slate-400 font-bold shrink-0">Manual Amt:</label>
                    <input type="number" id="both-andar-manual" min="500" max="1000000" step="100" placeholder="e.g. 500" value="500"
                           oninput="onBothManualInput('andar', this.value)"
                           class="w-full px-2 py-1 rounded-lg bg-black/90 border border-slate-600 focus:border-amber-400 text-xs font-black text-white text-center">
                </div>
            </div>

            <!-- BAHAR Section -->
            <div class="bg-black/60 border border-red-900/60 rounded-2xl p-2.5 sm:p-3">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs sm:text-sm font-black font-royal tracking-widest text-red-400 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> BAHAR BET
                    </span>
                    <span id="both-modal-bahar-display" class="text-xs sm:text-sm font-black text-amber-300">1,000 pts</span>
                </div>
                <!-- Quick Chips for Bahar -->
                <div class="flex items-center justify-between gap-1 mb-2">
                    <button type="button" onclick="setBothSideAmount('bahar', 500)" class="flex-1 py-1 rounded-lg bg-slate-800 hover:bg-red-950 border border-white/15 text-[10px] sm:text-xs font-black">500</button>
                    <button type="button" onclick="setBothSideAmount('bahar', 1000)" class="flex-1 py-1 rounded-lg bg-slate-800 hover:bg-red-950 border border-white/15 text-[10px] sm:text-xs font-black">1k</button>
                    <button type="button" onclick="setBothSideAmount('bahar', 2000)" class="flex-1 py-1 rounded-lg bg-slate-800 hover:bg-red-950 border border-white/15 text-[10px] sm:text-xs font-black">2k</button>
                    <button type="button" onclick="setBothSideAmount('bahar', 5000)" class="flex-1 py-1 rounded-lg bg-slate-800 hover:bg-red-950 border border-white/15 text-[10px] sm:text-xs font-black">5k</button>
                    <button type="button" onclick="setBothSideAmount('bahar', 10000)" class="flex-1 py-1 rounded-lg bg-slate-800 hover:bg-red-950 border border-white/15 text-[10px] sm:text-xs font-black">10k</button>
                </div>
                <!-- Manual Input for Bahar -->
                <div class="flex items-center gap-2">
                    <label class="text-[10px] text-slate-400 font-bold shrink-0">Manual Amt:</label>
                    <input type="number" id="both-bahar-manual" min="500" max="1000000" step="100" placeholder="e.g. 1000" value="1000"
                           oninput="onBothManualInput('bahar', this.value)"
                           class="w-full px-2 py-1 rounded-lg bg-black/90 border border-slate-600 focus:border-amber-400 text-xs font-black text-white text-center">
                </div>
            </div>

            <!-- Total Summary Bar -->
            <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-xs sm:text-sm font-bold">
                <span class="text-slate-300">Total Points:</span>
                <span id="both-modal-total-display" class="text-sm sm:text-base font-black text-amber-300">1,500 pts</span>
            </div>

            <!-- Actions -->
            <div class="grid grid-cols-2 gap-2 pt-1">
                <button type="button" onclick="closeBothBetModal()"
                        class="py-2.5 rounded-xl font-black text-xs uppercase tracking-wider text-white bg-slate-800 hover:bg-slate-700 transition border border-white/15">
                    CANCEL
                </button>
                <button type="button" id="btn-both-modal-confirm" onclick="submitBothBet()"
                        class="py-2.5 rounded-xl font-black text-xs uppercase tracking-wider text-slate-950 bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 hover:brightness-110 transition shadow-lg shadow-amber-500/40">
                    PLACE BET ON BOTH
                </button>
            </div>
        </div>
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
    let activeBothAndar = 500;
    let activeBothBahar = 1000;

    function openBothBetModal() {
        selectBetSide('both');
        updateBothModalDisplays();
        document.getElementById('bothBetModal')?.classList.remove('hidden');
    }

    function closeBothBetModal() {
        document.getElementById('bothBetModal')?.classList.add('hidden');
    }

    function setBothSideAmount(side, val) {
        const parsed = parseInt(val, 10);
        if (isNaN(parsed) || parsed < 500) return;
        if (side === 'andar') {
            activeBothAndar = parsed;
            const input = document.getElementById('both-andar-manual');
            if (input) input.value = parsed;
        } else if (side === 'bahar') {
            activeBothBahar = parsed;
            const input = document.getElementById('both-bahar-manual');
            if (input) input.value = parsed;
        }
        updateBothModalDisplays();
        syncBothHudBadges();
    }

    function onBothManualInput(side, val) {
        const parsed = parseInt(val, 10);
        if (side === 'andar') {
            activeBothAndar = isNaN(parsed) ? 0 : parsed;
        } else if (side === 'bahar') {
            activeBothBahar = isNaN(parsed) ? 0 : parsed;
        }
        updateBothModalDisplays();
        syncBothHudBadges();
    }

    function updateBothModalDisplays() {
        const andarDisp = document.getElementById('both-modal-andar-display');
        const baharDisp = document.getElementById('both-modal-bahar-display');
        const totalDisp = document.getElementById('both-modal-total-display');
        if (andarDisp) andarDisp.textContent = `${activeBothAndar.toLocaleString()} pts`;
        if (baharDisp) baharDisp.textContent = `${activeBothBahar.toLocaleString()} pts`;
        if (totalDisp) totalDisp.textContent = `${(activeBothAndar + activeBothBahar).toLocaleString()} pts`;
    }

    function syncBothHudBadges() {
        if (activeSelectedSide === 'both') {
            const andarBadge = document.getElementById('andar-bet-badge');
            const baharBadge = document.getElementById('bahar-bet-badge');
            const bothBadge = document.getElementById('both-bet-badge');
            if (andarBadge) andarBadge.textContent = `${activeBothAndar.toLocaleString()} pts`;
            if (baharBadge) baharBadge.textContent = `${activeBothBahar.toLocaleString()} pts`;
            if (bothBadge) bothBadge.textContent = `${(activeBothAndar + activeBothBahar).toLocaleString()} pts`;
            document.getElementById('status-first-bet').textContent = `${activeBothAndar.toLocaleString()} pts`;
            document.getElementById('status-second-bet').textContent = `${activeBothBahar.toLocaleString()} pts`;
        }
    }

    async function submitBothBet() {
        if (activeBothAndar < 500 || activeBothBahar < 500) {
            showSquareBanner('Minimum Bet', 'Minimum betting amount is 500 points for each side.');
            return;
        }

        const totalAmount = activeBothAndar + activeBothBahar;
        const totalEl = document.getElementById('sum-total');
        const currentSessionTotal = totalEl ? parseInt(totalEl.textContent.replace(/,/g, '') || '0', 10) : 0;
        if ((currentSessionTotal + totalAmount) > 1000000) {
            showSquareBanner('Session Limit Exceeded', 'Session betting limit exceeded. Maximum cumulative limit is 10,00,000 Points across Andar + Bahar.');
            return;
        }

        closeBothBetModal();

        if (gameEngineInstance) {
            const placeBtn = document.getElementById('btn-hud-place-bet');
            const modalBtn = document.getElementById('btn-both-modal-confirm');
            if (placeBtn) {
                placeBtn.disabled = true;
                placeBtn.dataset.originalText = placeBtn.textContent;
                placeBtn.textContent = 'PLACING...';
            }
            if (modalBtn) modalBtn.disabled = true;

            await gameEngineInstance.placeBet('both', {
                andar_amount: activeBothAndar,
                bahar_amount: activeBothBahar
            });

            if (placeBtn) {
                placeBtn.disabled = false;
                placeBtn.textContent = placeBtn.dataset.originalText || 'PLACE BET';
            }
            if (modalBtn) modalBtn.disabled = false;
        }
    }

    function selectPokerChip(val, el) {
        activeSelectedChip = parseInt(val, 10);
        document.querySelectorAll('.poker-chip').forEach(c => c.classList.remove('selected'));
        if (el) el.classList.add('selected');
        const manual = document.getElementById('manual-bet-amount');
        if (manual) manual.value = '';

        if (activeSelectedSide && activeSelectedSide !== 'both') {
            updateSideBadge(activeSelectedSide, activeSelectedChip);
        }
    }

    function selectBetSide(side) {
        activeSelectedSide = side;
        const andarBox = document.getElementById('btn-bet-andar');
        const baharBox = document.getElementById('btn-bet-bahar');
        const bothBox = document.getElementById('btn-bet-both');

        if (side === 'both') {
            bothBox?.classList.add('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
            andarBox.classList.add('ring-2', 'ring-amber-400');
            baharBox.classList.add('ring-2', 'ring-amber-400');
            syncBothHudBadges();
            return;
        }

        bothBox?.classList.remove('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
        const bothBadge = document.getElementById('both-bet-badge');
        if (bothBadge) bothBadge.textContent = '';

        if (side === 'andar') {
            andarBox.classList.add('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
            baharBox.classList.remove('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
            document.getElementById('status-first-bet').textContent = `${activeSelectedChip.toLocaleString()} pts`;
            document.getElementById('status-second-bet').textContent = `0 pts`;
        } else {
            baharBox.classList.add('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
            andarBox.classList.remove('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
            document.getElementById('status-second-bet').textContent = `${activeSelectedChip.toLocaleString()} pts`;
            document.getElementById('status-first-bet').textContent = `0 pts`;
        }

        updateSideBadge(side, activeSelectedChip);
    }

    function updateSideBadge(side, amount) {
        const andarBadge = document.getElementById('andar-bet-badge');
        const baharBadge = document.getElementById('bahar-bet-badge');
        if (side === 'andar') {
            andarBadge.textContent = `${amount.toLocaleString()} pts`;
            baharBadge.textContent = '';
        } else if (side === 'bahar') {
            baharBadge.textContent = `${amount.toLocaleString()} pts`;
            andarBadge.textContent = '';
        }
    }

    function handleUndoBet() {
        activeSelectedSide = null;
        const andarBox = document.getElementById('btn-bet-andar');
        const baharBox = document.getElementById('btn-bet-bahar');
        const bothBox = document.getElementById('btn-bet-both');
        andarBox.classList.remove('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
        baharBox.classList.remove('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
        bothBox?.classList.remove('ring-2', 'ring-amber-400', 'shadow-[0_0_15px_rgba(245,158,11,0.5)]');
        document.getElementById('andar-bet-badge').textContent = '';
        document.getElementById('bahar-bet-badge').textContent = '';
        const bothBadge = document.getElementById('both-bet-badge');
        if (bothBadge) bothBadge.textContent = '';
        document.getElementById('status-first-bet').textContent = '0 pts';
        document.getElementById('status-second-bet').textContent = '0 pts';
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
            showSquareBanner('Selection Required', 'Please select ANDAR, BAHAR, or BOTH before placing your bet.');
            return;
        }

        if (activeSelectedSide === 'both') {
            await submitBothBet();
            return;
        }

        const manual = document.getElementById('manual-bet-amount');
        if (manual && manual.value !== '') {
            const typed = parseInt(manual.value, 10);
            if (isNaN(typed) || typed < 500) {
                showSquareBanner('Minimum Bet', 'Minimum betting amount is 500 points.');
                return;
            }
            activeSelectedChip = typed;
        }

        const totalEl = document.getElementById('sum-total');
        const currentSessionTotal = totalEl ? parseInt(totalEl.textContent.replace(/,/g, '') || '0', 10) : 0;
        if ((currentSessionTotal + activeSelectedChip) > 1000000) {
            showSquareBanner('Session Limit Exceeded', 'Session betting limit exceeded. Maximum cumulative limit is 10,00,000 Points across Andar + Bahar.');
            return;
        }

        if (gameEngineInstance) {
            gameEngineInstance.selectedChip = activeSelectedChip;
            const placeBtn = document.getElementById('btn-hud-place-bet');
            if (placeBtn) {
                placeBtn.disabled = true;
                placeBtn.dataset.originalText = placeBtn.textContent;
                placeBtn.textContent = 'PLACING...';
            }
            await gameEngineInstance.placeBet(activeSelectedSide);
            if (placeBtn) {
                placeBtn.disabled = false;
                placeBtn.textContent = placeBtn.dataset.originalText || 'PLACE BET';
            }
        }
    }

    // Orientation checking & Fullscreen trigger (Matching Image 3)
    function checkOrientationAndPrompt() {
        const isPortrait = window.matchMedia('(orientation: portrait)').matches;
        const isMobile = window.innerWidth <= 1024;
        const overlay = document.getElementById('device-rotate-overlay');
        if (overlay) {
            if (isPortrait && isMobile) {
                overlay.style.setProperty('display', 'flex', 'important');
            } else {
                overlay.style.setProperty('display', 'none', 'important');
            }
        }
    }
    window.addEventListener('resize', checkOrientationAndPrompt);
    window.addEventListener('orientationchange', checkOrientationAndPrompt);
    window.addEventListener('load', checkOrientationAndPrompt);
    document.addEventListener('DOMContentLoaded', checkOrientationAndPrompt);

    function requestFullScreenIfLandscape() {
        try {
            if (!document.fullscreenElement) {
                const docEl = document.documentElement;
                if (docEl.requestFullscreen) docEl.requestFullscreen();
                else if (docEl.webkitRequestFullscreen) docEl.webkitRequestFullscreen();
                else if (docEl.msRequestFullscreen) docEl.msRequestFullscreen();
            }
        } catch (e) {}
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

        const manualAmt = document.getElementById('manual-bet-amount');
        if (manualAmt) {
            manualAmt.addEventListener('input', function () {
                const typed = parseInt(this.value, 10);
                if (!isNaN(typed) && typed >= 500) {
                    activeSelectedChip = typed;
                    document.querySelectorAll('.poker-chip').forEach(c => c.classList.remove('selected'));
                    if (activeSelectedSide) updateSideBadge(activeSelectedSide, activeSelectedChip);
                }
            });
        }

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
                } else if (msg.type === 'pen-position') {
                    applyPenPosition(msg);
                } else if (msg.type === 'overlay_card') {
                    if (msg.card_hidden || !msg.first_card) {
                        applyLiveCardOverlay(null);
                    } else {
                        applyLiveCardOverlay(msg.first_card, msg.card_x, msg.card_y, msg.card_scale);
                    }
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
        let playerRtc = null;
        let liveJpegTimer = null;
        let cctvMode = null;
        let streamEndedByAdmin = false;
        let liveFootageReady = false;
        let pendingLiveCard = undefined;
        const liveJpegUrl = @json(route('game.live.jpeg', $room->id));
        const livePlaylistUrl = @json(route('game.live.playlist', $room->id));
        const penPositionUrl = @json(route('game.pen.position.get', $room->id));
        const playerPenMarker = document.getElementById('player-pen-marker');
        let lastPenT = 0;
        let penPollBusy = false;

        function overlayPipCount(raw) {
            if (raw === '10') return 10;
            const n = parseInt(raw, 10);
            if (n >= 2 && n <= 9) return n;
            return 1;
        }

        function fillOverlayPips(panel, symbol, count, firstId) {
            if (!panel) return;
            panel.innerHTML = '';
            panel.setAttribute('data-pips', String(count));
            for (let i = 0; i < count; i++) {
                const d = document.createElement('div');
                d.className = 'suit';
                if (i === 0 && firstId) d.id = firstId;
                d.textContent = symbol;
                panel.appendChild(d);
            }
        }

        function setFeltBackground(isStreaming) {
            const felt = document.getElementById('player-felt-surface');
            if (!felt) return;
            if (isStreaming) {
                felt.style.background = '#000';
            } else {
                felt.style.background = "#1e1a17 url('{{ asset('images/live-table-bg.jpg') }}') center center / cover no-repeat";
            }
        }

        function hideLiveWaitCover() {
            const cover = document.getElementById('player-live-wait-cover');
            if (cover) cover.classList.add('hidden');
        }

        function showLiveWaitCover() {
            const cover = document.getElementById('player-live-wait-cover');
            if (cover) cover.classList.remove('hidden');
        }

        function finishLiveFootageReveal() {
            if (liveFootageReady) return;
            liveFootageReady = true;
            hideLiveWaitCover();
            if (pendingLiveCard !== undefined) {
                const pending = pendingLiveCard;
                pendingLiveCard = undefined;
                applyLiveCardOverlay(pending.cardCode, pending.x, pending.y, pending.scale);
            }
        }

        function revealPlayerLiveFootage() {
            if (liveFootageReady) return;
            const streamBox = document.getElementById('player-live-stream-box');
            const externalWrap = document.getElementById('player-external-stream-wrap');
            const cctvVideo = document.getElementById('live-cctv-stream');
            const fallbackImg = document.getElementById('player-live-camera-img');
            if (streamBox) {
                streamBox.classList.remove('hidden');
                streamBox.classList.add('bg-black');
            }
            if (externalWrap) externalWrap.classList.add('bg-black');

            const videoReady = cctvVideo && !cctvVideo.classList.contains('hidden') && cctvVideo.videoWidth > 0;
            if (videoReady) {
                cctvVideo.style.opacity = '1';
                cctvVideo.classList.remove('hidden');
                if (typeof cctvVideo.requestVideoFrameCallback === 'function') {
                    cctvVideo.requestVideoFrameCallback(function () {
                        finishLiveFootageReveal();
                    });
                } else {
                    requestAnimationFrame(function () {
                        requestAnimationFrame(finishLiveFootageReveal);
                    });
                }
                return;
            }

            const jpegReady = fallbackImg && !fallbackImg.classList.contains('hidden') && fallbackImg.naturalWidth > 0;
            if (jpegReady) {
                finishLiveFootageReveal();
                return;
            }
        }

        function bindLiveFootageReadyWatchers(cctvVideo, fallbackImg) {
            if (cctvVideo && !cctvVideo._liveReadyBound) {
                cctvVideo._liveReadyBound = true;
                const onVideoReady = () => {
                    if (cctvVideo.videoWidth > 0) revealPlayerLiveFootage();
                };
                cctvVideo.addEventListener('playing', onVideoReady);
                cctvVideo.addEventListener('loadeddata', onVideoReady);
            }
            if (fallbackImg && !fallbackImg._liveReadyBound) {
                fallbackImg._liveReadyBound = true;
                fallbackImg.addEventListener('load', () => {
                    if (fallbackImg.naturalWidth > 0) {
                        const vid = document.getElementById('live-cctv-stream');
                        if (vid && vid.videoWidth > 0 && !vid.classList.contains('hidden')) return;
                        fallbackImg.classList.remove('hidden');
                        revealPlayerLiveFootage();
                    }
                });
            }
        }

        let lastLiveOverlayLayout = { x: 0.48, y: 0.58, scale: 1 };
        const OVERLAY_BASE_VIDEO_FRAC = 0.048;

        function overlayMediaSize(media) {
            if (!media) return { mw: 0, mh: 0 };
            if (media.videoWidth) return { mw: media.videoWidth, mh: media.videoHeight };
            if (media.naturalWidth) return { mw: media.naturalWidth, mh: media.naturalHeight };
            return { mw: 0, mh: 0 };
        }

        function playerOverlayMedia() {
            const v = document.getElementById('live-cctv-stream');
            if (v && v.videoWidth > 0 && !v.classList.contains('hidden')) return v;
            const img = document.getElementById('player-live-camera-img');
            if (img && img.naturalWidth > 0 && !img.classList.contains('hidden')) return img;
            return v || img;
        }

        function overlayCoverMetrics(container, media) {
            const cw = Math.max(1, container.clientWidth);
            const ch = Math.max(1, container.clientHeight);
            const sz = overlayMediaSize(media);
            if (!sz.mw || !sz.mh) {
                return {
                    coverScale: 1,
                    displayW: cw,
                    displayH: ch,
                    offsetX: 0,
                    offsetY: 0,
                    mw: cw,
                    mh: ch
                };
            }
            const mw = sz.mw;
            const mh = sz.mh;
            const coverScale = Math.max(cw / mw, ch / mh);
            const displayW = mw * coverScale;
            const displayH = mh * coverScale;
            return {
                coverScale,
                displayW,
                displayH,
                offsetX: (cw - displayW) / 2,
                offsetY: (ch - displayH) / 2,
                mw,
                mh
            };
        }

        function layoutPlayerOverlayCard(overlay, x, y, scale) {
            const box = document.getElementById('player-live-stream-box');
            if (!overlay || !box) return;
            const sc = (scale != null && scale !== '' && isFinite(Number(scale))) ? Number(scale) : 1;
            const px = (x != null && x !== '' && isFinite(Number(x))) ? Number(x) : 0.48;
            const py = (y != null && y !== '' && isFinite(Number(y))) ? Number(y) : 0.58;
            lastLiveOverlayLayout = { x: px, y: py, scale: sc };
            const metrics = overlayCoverMetrics(box, playerOverlayMedia());
            const widthPx = metrics.mw * metrics.coverScale * OVERLAY_BASE_VIDEO_FRAC * sc;
            overlay.style.width = widthPx + 'px';
            overlay.style.height = (widthPx * 168 / 118) + 'px';
            overlay.style.left = (metrics.offsetX + px * metrics.displayW) + 'px';
            overlay.style.top = (metrics.offsetY + py * metrics.displayH) + 'px';
            overlay.style.transform = 'translate(-50%, -50%)';
        }

        function applyLiveCardOverlay(cardCode, x, y, scale) {
            const overlay = document.getElementById('player-live-card-overlay');
            const rankEl = document.getElementById('player-live-card-rank');
            const rankB = document.getElementById('player-live-card-rank-b');
            const indexSuit = document.getElementById('player-live-card-index-suit');
            const indexSuitB = document.getElementById('player-live-card-index-suit-b');
            const pips = document.getElementById('player-live-card-pips');
            const hudRank = document.getElementById('hud-first-card-rank');
            if (!overlay) return;
            if (!cardCode) {
                overlay.classList.remove('is-visible');
                pendingLiveCard = liveFootageReady ? undefined : { cardCode: null, x: null, y: null, scale: null };
                return;
            }
            if (!liveFootageReady || !window._isStreamActive) {
                pendingLiveCard = { cardCode, x, y, scale };
                overlay.classList.remove('is-visible');
                if (hudRank) {
                    const rawHud = String(cardCode).split('_')[0].toUpperCase();
                    hudRank.textContent = rawHud === 'JACK' ? 'J' : (rawHud === 'QUEEN' ? 'Q' : (rawHud === 'KING' ? 'K' : (rawHud === 'ACE' ? 'A' : rawHud)));
                }
                return;
            }
            const parts = String(cardCode).split('_');
            const raw = (parts[0] || '').toUpperCase();
            const suit = (parts[1] || 'spades').toLowerCase();
            const shortVal = raw === 'JACK' ? 'J' : (raw === 'QUEEN' ? 'Q' : (raw === 'KING' ? 'K' : (raw === 'ACE' ? 'A' : raw)));
            const symbols = { spades: '♠', hearts: '♥', diamonds: '♦', clubs: '♣' };
            const symbol = symbols[suit] || '♠';
            if (rankEl) rankEl.textContent = shortVal;
            if (rankB) rankB.textContent = shortVal;
            if (indexSuit) indexSuit.textContent = symbol;
            if (indexSuitB) indexSuitB.textContent = symbol;
            fillOverlayPips(pips, symbol, overlayPipCount(raw), 'player-live-card-suit');
            overlay.classList.add('is-visible');
            overlay.classList.add('is-red');
            overlay.classList.remove('is-black');
            layoutPlayerOverlayCard(overlay, x, y, scale);
            if (hudRank) hudRank.textContent = shortVal;
        }
        window.applyLiveCardOverlay = applyLiveCardOverlay;

        const playerStreamBox = document.getElementById('player-live-stream-box');
        if (playerStreamBox && typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(function () {
                const overlay = document.getElementById('player-live-card-overlay');
                if (overlay && overlay.classList.contains('is-visible')) {
                    layoutPlayerOverlayCard(overlay, lastLiveOverlayLayout.x, lastLiveOverlayLayout.y, lastLiveOverlayLayout.scale);
                }
            }).observe(playerStreamBox);
        }
        const playerCctvVideo = document.getElementById('live-cctv-stream');
        if (playerCctvVideo) {
            playerCctvVideo.addEventListener('loadedmetadata', function () {
                const overlay = document.getElementById('player-live-card-overlay');
                if (overlay && overlay.classList.contains('is-visible')) {
                    layoutPlayerOverlayCard(overlay, lastLiveOverlayLayout.x, lastLiveOverlayLayout.y, lastLiveOverlayLayout.scale);
                }
            });
        }

        function applyPenPosition(pos) {
            if (!playerPenMarker || !pos) return;
            const t = Number(pos.t || 0);
            if (t && t < lastPenT) return;
            if (t) lastPenT = t;
            if (pos.card_hidden) applyLiveCardOverlay(null);
            else if (pos.first_card) applyLiveCardOverlay(pos.first_card, pos.card_x, pos.card_y, pos.card_scale);
            // Yellow cursor mark is for admin only - do not show to players
            if (playerPenMarker) {
                playerPenMarker.style.display = 'none';
            }
        }

        async function pollPenPosition() {
            if (penPollBusy) return;
            penPollBusy = true;
            try {
                const res = await fetch(penPositionUrl + '?after=' + lastPenT, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                applyPenPosition(data);
            } catch (e) {
            } finally {
                penPollBusy = false;
            }
        }

        function createLowLatencyHls() {
            return new Hls({
                enableWorker: true,
                lowLatencyMode: false,
                backBufferLength: 30,
                maxBufferLength: 20,
                maxMaxBufferLength: 40,
                liveSyncDurationCount: 3,
                liveMaxLatencyDurationCount: 10,
                liveDurationInfinity: true,
                startFragPrefetch: true
            });
        }

        function keepHlsAtLiveEdge(hls, video) {
            return;
        }

        function playNativeHlsAtLiveEdge(video, streamUrl) {
            if (video.src !== streamUrl) video.src = streamUrl;
            const seekLive = () => {
                try {
                    if (!isFinite(video.duration) && video.seekable && video.seekable.length > 0) {
                        video.currentTime = Math.max(0, video.seekable.end(video.seekable.length - 1) - 0.3);
                    }
                } catch (e) {}
                video.play().catch(() => {});
            };
            video.addEventListener('loadedmetadata', seekLive, { once: true });
            seekLive();
        }

        function stopLiveJpeg() {
            if (liveJpegTimer) {
                clearInterval(liveJpegTimer);
                liveJpegTimer = null;
            }
        }

        function snapshotCandidates(streamUrl) {
            try {
                const u = new URL(streamUrl);
                const origin = u.origin;
                const dir = u.pathname.replace(/\/[^/]*$/, '');
                return [
                    streamUrl.replace(/\.m3u8(\?.*)?$/i, '/snapshot.jpg'),
                    streamUrl.replace(/\.m3u8(\?.*)?$/i, '.jpg'),
                    origin + dir + '/snapshot.jpg',
                    origin + dir + '/latest.jpg',
                    origin + dir + '/preview.jpg',
                    origin + '/cgi-bin/snapshot.cgi',
                    origin + '/axis-cgi/jpg/image.cgi',
                    origin + '/ISAPI/Streaming/channels/101/picture',
                    origin + '/jpg/image.jpg',
                    origin + '/snapshot.jpg',
                    liveJpegUrl
                ].filter((v, i, a) => v && a.indexOf(v) === i);
            } catch (e) {
                return [liveJpegUrl];
            }
        }

        function startLiveJpegFromUrl(img, url) {
            if (liveJpegTimer) return;
            let busy = false;
            liveJpegTimer = setInterval(() => {
                if (busy) return;
                busy = true;
                const tmp = new Image();
                tmp.onload = () => { img.src = tmp.src; busy = false; };
                tmp.onerror = () => { busy = false; };
                tmp.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + 't=' + Date.now();
            }, 120);
        }

        function startLiveJpeg(img) {
            startLiveJpegFromUrl(img, liveJpegUrl);
        }

        function whepUrlFromHls(hlsUrl) {
            try {
                const u = new URL(hlsUrl);
                let path = u.pathname.replace(/\/index\.m3u8$/i, '').replace(/\.m3u8$/i, '');
                if (!path || path === '/') path = '/';
                return u.origin + path.replace(/\/$/, '') + '/whep';
            } catch (e) {
                return null;
            }
        }

        async function startWhepPlayback(video, whepUrl) {
            const pc = new RTCPeerConnection({ iceServers: [] });
            pc.addTransceiver('video', { direction: 'recvonly' });
            pc.addTransceiver('audio', { direction: 'recvonly' });
            pc.ontrack = (ev) => {
                if (ev.streams && ev.streams[0]) {
                    video.srcObject = ev.streams[0];
                    video.play().catch(() => {});
                }
            };
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            const ctrl = new AbortController();
            const timer = setTimeout(() => ctrl.abort(), 1500);
            try {
                const res = await fetch(whepUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/sdp' },
                    body: pc.localDescription.sdp,
                    signal: ctrl.signal
                });
                if (!res.ok) {
                    pc.close();
                    throw new Error('whep ' + res.status);
                }
                const answer = await res.text();
                await pc.setRemoteDescription({ type: 'answer', sdp: answer });
                return pc;
            } finally {
                clearTimeout(timer);
            }
        }

        function keepVideoRunning(video) {
            if (!video || video._keepAliveBound) return;
            video._keepAliveBound = true;
            const resume = () => {
                if (streamEndedByAdmin) return;
                video.play().catch(() => {});
            };
            ['pause', 'ended', 'stalled', 'waiting', 'suspend'].forEach((evt) => {
                video.addEventListener(evt, resume);
            });
        }

        function startCctvLowLatency(streamUrl, cctvVideo, ytIframe, externalWrap, fallbackImg) {
            if (cctvMode === 'hls' && playerHls) {
                if (cctvVideo) {
                    cctvVideo.play().catch(() => {});
                    if (cctvVideo.videoWidth > 0) revealPlayerLiveFootage();
                }
                return;
            }
            cctvMode = 'hls';
            startHlsPlayback(streamUrl, cctvVideo, ytIframe, externalWrap, fallbackImg);
        }

        function startHlsPlayback(streamUrl, cctvVideo, ytIframe, externalWrap, fallbackImg) {
            if (ytIframe) ytIframe.classList.add('hidden');
            if (externalWrap) externalWrap.classList.remove('hidden');
            if (fallbackImg) {
                fallbackImg.classList.remove('hidden');
                startLiveJpeg(fallbackImg);
            }
            if (!cctvVideo) return;
            cctvVideo.muted = true;
            cctvVideo.setAttribute('muted', '');
            cctvVideo.autoplay = true;
            cctvVideo.playsInline = true;
            cctvVideo.style.opacity = liveFootageReady ? '1' : '0';
            cctvVideo.classList.remove('hidden');
            keepVideoRunning(cctvVideo);
            bindLiveFootageReadyWatchers(cctvVideo, fallbackImg);
            const playUrl = livePlaylistUrl || streamUrl;

            const showVideoWhenReady = () => {
                if (cctvVideo.videoWidth > 0) {
                    if (fallbackImg) fallbackImg.classList.add('hidden');
                    stopLiveJpeg();
                    revealPlayerLiveFootage();
                }
            };
            cctvVideo.addEventListener('playing', showVideoWhenReady);
            cctvVideo.addEventListener('loadeddata', showVideoWhenReady);

            if (Hls.isSupported()) {
                if (!playerHls) {
                    playerHls = createLowLatencyHls();
                    playerHls.loadSource(playUrl);
                    playerHls.attachMedia(cctvVideo);
                    playerHls.on(Hls.Events.MANIFEST_PARSED, () => {
                        cctvVideo.play().catch(() => {});
                    });
                    playerHls.on(Hls.Events.ERROR, function(_, data) {
                        if (streamEndedByAdmin || !data || !playerHls) return;
                        if (fallbackImg) {
                            fallbackImg.classList.remove('hidden');
                            startLiveJpeg(fallbackImg);
                        }
                        if (!data.fatal) {
                            cctvVideo.play().catch(() => {});
                            return;
                        }
                        if (data.type === Hls.ErrorTypes.NETWORK_ERROR) {
                            if (streamUrl && streamUrl !== playUrl && !cctvVideo._triedDirectHls) {
                                cctvVideo._triedDirectHls = true;
                                try { playerHls.destroy(); } catch (e) {}
                                playerHls = null;
                                playerHls = createLowLatencyHls();
                                playerHls.loadSource(streamUrl);
                                playerHls.attachMedia(cctvVideo);
                                return;
                            }
                            playerHls.startLoad();
                            cctvVideo.play().catch(() => {});
                        } else if (data.type === Hls.ErrorTypes.MEDIA_ERROR) {
                            playerHls.recoverMediaError();
                            cctvVideo.play().catch(() => {});
                        }
                    });
                    cctvVideo.onclick = function() {
                        cctvVideo.play().catch(() => {});
                    };
                } else {
                    cctvVideo.play().catch(() => {});
                }
            } else if (cctvVideo.canPlayType('application/vnd.apple.mpegurl')) {
                playNativeHlsAtLiveEdge(cctvVideo, playUrl);
            }
        }

        function syncLiveStreamView(isStreaming, externalUrl) {
            const streamBox = document.getElementById('player-live-stream-box');
            const whiteScreen = document.getElementById('player-stream-white-screen');
            const externalWrap = document.getElementById('player-external-stream-wrap');
            const cctvVideo = document.getElementById('live-cctv-stream');
            const ytIframe = document.getElementById('live-youtube-stream');
            const fallbackImg = document.getElementById('player-live-camera-img');

            const streamUrl = (externalUrl || @json($room->live_stream_url ?? ''))?.trim();

            if (isStreaming) {
                streamEndedByAdmin = false;
                window._isStreamActive = true;
                setFeltBackground(true);
                if (!liveFootageReady) showLiveWaitCover();
                if (streamBox) {
                    streamBox.classList.remove('hidden');
                    streamBox.classList.add('bg-black');
                }
                if (whiteScreen) whiteScreen.classList.add('hidden');
                bindLiveFootageReadyWatchers(cctvVideo, fallbackImg);

                if (streamUrl) {
                    if (cctvMode === 'jpeg') {
                        if (externalWrap) externalWrap.classList.add('hidden');
                        if (fallbackImg) fallbackImg.classList.remove('hidden');
                        if (fallbackImg && fallbackImg.naturalWidth > 0) revealPlayerLiveFootage();
                    } else {
                    if (externalWrap) {
                        externalWrap.classList.remove('hidden');
                        externalWrap.classList.add('bg-black');
                    }

                    const ytMatch = /(?:youtube\.com\/(?:watch\?v=|embed\/|live\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/i.exec(streamUrl);
                    if (ytMatch) {
                        if (fallbackImg) fallbackImg.classList.add('hidden');
                        const ytSrc = 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1&mute=1&playsinline=1&enablejsapi=1&rel=0';
                        if (ytIframe) {
                            if (ytIframe.src !== ytSrc) ytIframe.src = ytSrc;
                            ytIframe.classList.remove('hidden');
                        }
                        if (cctvVideo) cctvVideo.classList.add('hidden');
                        finishLiveFootageReveal();
                    } else if (streamUrl.toLowerCase().includes('.m3u8')) {
                        startCctvLowLatency(streamUrl, cctvVideo, ytIframe, externalWrap, fallbackImg);
                    } else {
                        // Direct video file/feed (MP4 / WebM)
                        if (fallbackImg) fallbackImg.classList.add('hidden');
                        if (ytIframe) ytIframe.classList.add('hidden');
                        if (cctvVideo) {
                            cctvVideo.style.opacity = liveFootageReady ? '1' : '0';
                            cctvVideo.classList.remove('hidden');
                            keepVideoRunning(cctvVideo);
                            bindLiveFootageReadyWatchers(cctvVideo, fallbackImg);
                            if (cctvVideo.src !== streamUrl) cctvVideo.src = streamUrl;
                            cctvVideo.play().catch(() => {});
                        }
                    }
                    }
                } else {
                    // Local admin webcam broadcast mode
                    if (externalWrap) externalWrap.classList.add('hidden');
                    if (fallbackImg) fallbackImg.classList.remove('hidden');
                    bindLiveFootageReadyWatchers(cctvVideo, fallbackImg);
                    if (fallbackImg && fallbackImg.naturalWidth > 0) revealPlayerLiveFootage();
                }
            } else {
                streamEndedByAdmin = true;
                window._isStreamActive = false;
                liveFootageReady = false;
                pendingLiveCard = undefined;
                setFeltBackground(false);
                hideLiveWaitCover();
                if (playerPenMarker) playerPenMarker.style.display = 'none';
                const cardOverlay = document.getElementById('player-live-card-overlay');
                if (cardOverlay) cardOverlay.classList.remove('is-visible');
                if (streamBox) {
                    streamBox.classList.add('hidden');
                    streamBox.classList.remove('bg-black');
                }
                if (whiteScreen) whiteScreen.classList.remove('hidden');
                if (externalWrap) {
                    externalWrap.classList.add('hidden');
                    externalWrap.classList.remove('bg-black');
                }
                if (playerRtc) {
                    try { playerRtc.close(); } catch (e) {}
                    playerRtc = null;
                }
                if (playerHls) {
                    playerHls.destroy();
                    playerHls = null;
                }
                if (cctvVideo && cctvVideo._liveEdgeIv) {
                    clearInterval(cctvVideo._liveEdgeIv);
                    cctvVideo._liveEdgeIv = null;
                }
                stopLiveJpeg();
                cctvMode = null;
                if (cctvVideo) {
                    cctvVideo.pause();
                    cctvVideo.srcObject = null;
                    cctvVideo.removeAttribute('src');
                    cctvVideo.load();
                    cctvVideo.style.opacity = '0';
                }
                if (ytIframe) {
                    ytIframe.src = 'about:blank';
                }
            }
        }

        // Plain fullscreen toggle — no forced rotation/orientation lock
        window.toggleFullscreen = async function() {
            const docEl = document.documentElement;
            const isFs = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
            try {
                if (!isFs) {
                    const req = docEl.requestFullscreen || docEl.webkitRequestFullscreen || docEl.mozRequestFullScreen || docEl.msRequestFullscreen;
                    if (req) await req.call(docEl);
                } else {
                    const exit = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
                    if (exit) await exit.call(document);
                }
            } catch (e) {
                console.warn('Fullscreen toggle error:', e);
            }
        };

        document.getElementById('btn-toggle-fullscreen')?.addEventListener('click', window.toggleFullscreen);

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

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // On mobile: stop the stream when user goes to home screen (prevent background PiP)
                const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
                if (isMobile) {
                    const cctvVideoHide = document.getElementById('live-cctv-stream');
                    // Exit PiP if active
                    if (document.pictureInPictureElement) {
                        document.exitPictureInPicture().catch(() => {});
                    }
                    // Pause and detach stream on mobile when hidden to prevent mini-screen
                    if (cctvVideoHide && !streamEndedByAdmin) {
                        cctvVideoHide.pause();
                    }
                }
                return;
            }
            // Page became visible again — resume stream on all devices
            if (streamEndedByAdmin) return;
            const cctvVideo = document.getElementById('live-cctv-stream');
            if (cctvVideo) cctvVideo.play().catch(() => {});
        });

        // Prevent PiP from being triggered on mobile via enterpictureinpicture
        (function disableMobilePiP() {
            const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
            if (!isMobile) return;
            const vid = document.getElementById('live-cctv-stream');
            if (!vid) return;
            vid.addEventListener('enterpictureinpicture', function (e) {
                e.preventDefault();
                if (document.pictureInPictureElement) {
                    document.exitPictureInPicture().catch(() => {});
                }
            });
        })();

        window.addEventListener('pageshow', (e) => {
            if (streamEndedByAdmin || !window._isStreamActive) return;
            const cctvVideo = document.getElementById('live-cctv-stream');
            if (cctvVideo && playerHls) {
                cctvVideo.play().catch(() => {});
                return;
            }
            if (e.persisted) {
                liveFootageReady = false;
                showLiveWaitCover();
                cctvMode = null;
                if (playerHls) {
                    try { playerHls.destroy(); } catch (err) {}
                    playerHls = null;
                }
                syncLiveStreamView(true, @json($room->live_stream_url ?? ''));
            }
        });

        (function runPenLoop() {
            pollPenPosition().finally(() => setTimeout(runPenLoop, 20));
        })();

        @if($room->is_streaming)
            syncLiveStreamView(true, @json($room->live_stream_url ?? ''));
        @endif
    });
</script>
@endpush
