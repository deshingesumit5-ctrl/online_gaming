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

    /* Fullscreen HUD Container: Perfectly fits laptop screens without page scrolling */
    .game-viewport {
        height: calc(100vh - 58px);
        max-height: calc(100vh - 58px);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .game-viewport .felt-surface {
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="w-full max-w-7xl mx-auto flex flex-col justify-between rounded-2xl overflow-hidden casino-felt-table shadow-2xl border border-slate-700/80 relative select-none game-viewport">

    <!-- Top Bar (Matching Image 5: ← TABLE 1 : MIN BET 500 & Action Icons) -->
    <div class="relative z-30 px-3 sm:px-5 py-2 flex items-center justify-between text-white bg-black/40 backdrop-blur-sm border-b border-white/10 shrink-0">
        <!-- Left: Back Navigation & Table Title -->
        <div class="flex items-center gap-3 font-royal">
            <a href="{{ route('dashboard') }}" class="text-white hover:text-amber-400 transition text-base font-bold flex items-center gap-1.5" title="Back to Lobby">
                <span>&larr;</span>
            </a>
            <h1 class="text-xs sm:text-sm md:text-base font-black tracking-wider uppercase">
                {{ strtoupper($room->name) }} : MIN BET {{ $denominations[0] ?? 500 }}
            </h1>
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

    <!-- Main Live Table Felt Surface (Matching Image 5) -->
    <div class="felt-surface relative flex-grow min-h-0 flex items-center justify-center p-2 sm:p-3 overflow-hidden">
        
        @if($room->live_stream_url)
            <!-- Live Stream Video when configured (CCTV, HLS .m3u8, YouTube, or MP4) -->
            @php
                $streamUrl = trim($room->live_stream_url);
                $isYouTube = \Illuminate\Support\Str::contains($streamUrl, ['youtube.com', 'youtu.be']);
                $isHls = \Illuminate\Support\Str::contains($streamUrl, '.m3u8');

                if ($isYouTube) {
                    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|live\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/i', $streamUrl, $matches)) {
                        $ytId = $matches[1];
                        $streamUrl = "https://www.youtube.com/embed/{$ytId}?autoplay=1&mute=1&playsinline=1&enablejsapi=1&rel=0";
                    }
                }
            @endphp
            <div class="absolute inset-0 z-0 bg-black flex items-center justify-center overflow-hidden">
                @if($isYouTube)
                    <iframe class="w-full h-full border-0 pointer-events-auto"
                            src="{{ $streamUrl }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                @elseif($isHls)
                    <!-- CCTV / IP Camera HLS Stream (.m3u8) -->
                    <video id="live-cctv-stream" class="w-full h-full object-cover" autoplay muted loop playsinline></video>
                    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const video = document.getElementById('live-cctv-stream');
                            const hlsUrl = @json($streamUrl);
                            if (video) {
                                if (Hls.isSupported()) {
                                    const hls = new Hls({ enableWorker: true, lowLatencyMode: true });
                                    hls.loadSource(hlsUrl);
                                    hls.attachMedia(video);
                                    hls.on(Hls.Events.MANIFEST_PARSED, function() {
                                        video.play().catch(function() {});
                                    });
                                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                    video.src = hlsUrl;
                                    video.addEventListener('loadedmetadata', function() {
                                        video.play().catch(function() {});
                                    });
                                }
                            }
                        });
                    </script>
                @else
                    <video class="w-full h-full object-cover" autoplay muted loop playsinline>
                        <source src="{{ $streamUrl }}" type="video/mp4">
                        <source src="{{ $streamUrl }}" type="video/webm">
                    </video>
                @endif
            </div>
        @else
            <!-- Realistic Live Table Felt Simulation (Matching Image 5 Dealer Stage) -->
            <div class="w-full h-full relative z-10 flex flex-col justify-between py-1 sm:py-2">
                <!-- Top Center: Dealer First Open Card (Joker) -->
                <div class="flex flex-col items-center justify-center">
                    <div id="first-card-slot" class="relative">
                        <!-- Card: 4 Clubs or Dealt Card -->
                        <div class="w-12 sm:w-14 h-16 sm:h-20 rounded-lg bg-white border-2 border-slate-300 shadow-xl flex flex-col justify-between p-1 text-slate-900 transition-transform duration-300 hover:scale-105">
                            <div class="flex items-center justify-between text-[10px] font-bold leading-none">
                                <span id="first-card-val-top">{{ $currentRound->first_card ? strtoupper(explode('_', $currentRound->first_card)[0]) : '4' }}</span>
                                <span id="first-card-suit-top" class="text-xs">♣</span>
                            </div>
                            <div class="my-auto text-center">
                                <div class="w-6 h-7 mx-auto rounded bg-yellow-200/80 border border-yellow-300/60 flex items-center justify-center text-sm">
                                    ♣
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] font-bold leading-none transform rotate-180">
                                <span id="first-card-val-bottom">{{ $currentRound->first_card ? strtoupper(explode('_', $currentRound->first_card)[0]) : '4' }}</span>
                                <span id="first-card-suit-bottom" class="text-xs">♣</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Center: Fanned Deck of Cards Spread on Table (Image 5) -->
                <div class="flex items-center justify-center my-1 sm:my-2">
                    <!-- Deck block stack -->
                    <div class="w-12 h-16 rounded-lg bg-white border border-slate-300 shadow-xl p-1 relative -mr-3 z-10 hidden sm:flex items-center justify-center">
                        <div class="w-full h-full rounded bg-slate-900 border border-slate-700 flex flex-col items-center justify-center">
                            <span class="text-red-500 text-xs">★</span>
                            <span class="text-[7px] text-slate-400 uppercase font-bold mt-0.5">Deck</span>
                        </div>
                    </div>
                    <!-- Spread cards ribbon -->
                    <div class="flex items-center -space-x-2.5 overflow-hidden max-w-xs sm:max-w-md px-2 py-0.5">
                        @for($i = 0; $i < 28; $i++)
                            <div class="fanned-card shrink-0 {{ $i === 0 ? 'bg-pink-700' : 'bg-slate-100' }}"></div>
                        @endfor
                    </div>
                </div>

                <!-- Right Side: Dealt Cards to Andar & Bahar (Image 5) -->
                <div class="absolute right-3 sm:right-8 top-3 sm:top-5 flex flex-col gap-2 sm:gap-3">
                    <!-- Andar dealt stack -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex -space-x-3">
                            <div class="w-10 h-14 rounded bg-white border border-slate-300 shadow-md p-1 flex flex-col justify-between text-red-600">
                                <span class="text-[9px] font-bold leading-none">3♦</span>
                                <div class="text-center text-[10px]">♦</div>
                                <span class="text-[9px] font-bold leading-none transform rotate-180">3♦</span>
                            </div>
                            <div class="w-10 h-14 rounded bg-white border border-slate-300 shadow-md p-1 flex flex-col justify-between text-red-600">
                                <span class="text-[9px] font-bold leading-none">2♦</span>
                                <div class="text-center text-[10px]">♦</div>
                                <span class="text-[9px] font-bold leading-none transform rotate-180">2♦</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bahar dealt stack -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex -space-x-3">
                            <div class="w-10 h-14 rounded bg-white border border-slate-300 shadow-md p-1 flex flex-col justify-between text-red-600">
                                <span class="text-[9px] font-bold leading-none">8♦</span>
                                <div class="text-center text-[10px]">♦</div>
                                <span class="text-[9px] font-bold leading-none transform rotate-180">8♦</span>
                            </div>
                            <div class="w-10 h-14 rounded bg-white border border-slate-300 shadow-md p-1 flex flex-col justify-between text-red-600">
                                <span class="text-[9px] font-bold leading-none">K♥</span>
                                <div class="text-center text-[10px]">♥</div>
                                <span class="text-[9px] font-bold leading-none transform rotate-180">K♥</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- Bottom Casino Cockpit HUD Bar (Compact & Perfectly Scaled) -->
    <div class="relative z-30 p-2 bg-[#101520]/95 backdrop-blur-md border-t border-slate-700/80 text-white shrink-0">
        <div class="flex flex-row items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
            
            <!-- LEFT SECTION: Poker Chips & Action Buttons & Status pills -->
            <div class="flex flex-col gap-1.5 w-full sm:w-auto">
                <!-- Row 1: Poker Chips (100, 500, 1k, 2k, 5k, 10k) -->
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach($denominations as $idx => $denom)
                        @php
                            $label = $denom >= 1000 ? ($denom / 1000) . 'k' : $denom;
                        @endphp
                        <div class="poker-chip chip-{{ $denom }} {{ $idx === 0 ? 'selected' : '' }}" 
                             data-value="{{ $denom }}" onclick="selectPokerChip({{ $denom }}, this)">
                            <span>{{ $label }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Row 2: Action Buttons (UNDO, PLACE BET, CANCEL BET) & Status Pills -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" id="btn-hud-undo" onclick="handleUndoBet()"
                            class="px-3.5 py-1.5 rounded-lg bg-[#272b35] hover:bg-[#343a46] border border-slate-600 text-slate-200 font-black text-xs uppercase tracking-wider transition active:scale-95">
                        UNDO
                    </button>

                    <button type="button" id="btn-hud-place-bet" onclick="handleConfirmBet()"
                            class="px-4 py-1.5 rounded-lg bg-[#3a4150] hover:bg-emerald-600 border border-slate-600 text-white font-black text-xs uppercase tracking-wider transition active:scale-95 shadow-md">
                        PLACE BET
                    </button>

                    <button type="button" id="btn-hud-cancel-bet" onclick="handleCancelActiveBet()"
                            class="hidden px-3 py-1.5 rounded-lg bg-red-700 hover:bg-red-600 border border-red-500 text-white font-black text-xs uppercase tracking-wider transition active:scale-95 shadow-lg shadow-red-700/50 flex items-center gap-1.5 animate-pulse">
                        <span>↩ CANCEL BET</span>
                        <span id="cancel-timer-countdown" class="px-1.5 py-0.5 rounded bg-black/50 text-[10px] font-bold text-amber-300">{{ $room->cancellation_duration }}s</span>
                    </button>

                    <!-- Status Readout 1: BALANCE: ₹0 -->
                    <div class="px-2.5 py-1 rounded-lg bg-black/60 border border-slate-700/80 text-[10px] sm:text-[11px] font-bold text-slate-200">
                        BALANCE: <span class="text-white font-extrabold">₹<span class="user-wallet-balance">{{ number_format($user->wallet_balance, 0) }}</span></span>
                    </div>

                    <!-- Status Readout 2: FIRST BET: ₹0 -->
                    <div class="px-2.5 py-1 rounded-lg bg-black/60 border border-slate-700/80 text-[10px] sm:text-[11px] font-bold text-slate-300">
                        FIRST BET: <span class="text-amber-400 font-extrabold" id="status-first-bet">₹0</span>
                    </div>

                    <!-- Status Readout 3: SECOND BET: ₹0 -->
                    <div class="px-2.5 py-1 rounded-lg bg-black/60 border border-slate-700/80 text-[10px] sm:text-[11px] font-bold text-slate-300">
                        SECOND BET: <span class="text-amber-400 font-extrabold" id="status-second-bet">₹0</span>
                    </div>
                </div>
            </div>

            <!-- CENTER SECTION: ANDAR & BAHAR Boxes with Joker Notch -->
            <div class="relative flex items-center justify-center w-full sm:w-72 md:w-80 shrink-0 my-1 sm:my-0">
                <div class="w-full rounded-xl bg-[#1e232f] border border-slate-700 overflow-hidden shadow-xl relative">
                    <!-- ANDAR Area -->
                    <div id="btn-bet-andar" onclick="selectBetSide('andar')"
                         class="p-2.5 border-b border-slate-700/80 flex items-center justify-between cursor-pointer hover:bg-slate-800/80 transition group">
                        <span class="text-xs sm:text-sm font-black font-royal tracking-widest text-slate-100 group-hover:text-indigo-300">
                            ANDAR
                        </span>
                        <div class="flex items-center gap-2">
                            <span id="andar-bet-badge" class="text-xs font-bold text-indigo-300"></span>
                        </div>
                    </div>

                    <!-- BAHAR Area -->
                    <div id="btn-bet-bahar" onclick="selectBetSide('bahar')"
                         class="p-2.5 flex items-center justify-between cursor-pointer hover:bg-slate-800/80 transition group">
                        <span class="text-xs sm:text-sm font-black font-royal tracking-widest text-slate-100 group-hover:text-red-300">
                            BAHAR
                        </span>
                        <div class="flex items-center gap-2">
                            <span id="bahar-bet-badge" class="text-xs font-bold text-red-300"></span>
                        </div>
                    </div>

                    <!-- Joker Card Notch on the right side of the card box -->
                    <div class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-1.5 z-20 flex items-center">
                        <div class="w-9 h-12 rounded-lg bg-white border-2 border-red-500 shadow-[0_0_12px_rgba(239,68,68,0.5)] flex flex-col justify-between p-0.5 text-slate-900 pointer-events-none">
                            <div class="flex items-center justify-between text-[7px] font-bold leading-none">
                                <span>{{ $currentRound->first_card ? strtoupper(explode('_', $currentRound->first_card)[0]) : '4' }}</span>
                                <span>♣</span>
                            </div>
                            <div class="text-center text-xs leading-none">
                                ♣
                            </div>
                            <div class="flex items-center justify-between text-[7px] font-bold leading-none transform rotate-180">
                                <span>{{ $currentRound->first_card ? strtoupper(explode('_', $currentRound->first_card)[0]) : '4' }}</span>
                                <span>♣</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SECTION: Red Timer Bar, Bead Plate History, Limits -->
            <div class="flex flex-col justify-between w-full sm:w-60 md:w-64 shrink-0 space-y-1">
                <!-- Red Countdown Timer Line -->
                <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                    <div id="hud-timer-bar" class="h-full bg-red-600 transition-all duration-1000 ease-linear" style="width: 100%;"></div>
                </div>

                <!-- History Road / Bead Plate -->
                <div class="flex items-center gap-1 overflow-x-auto py-0.5 scrollbar-none">
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-a-green font-black text-[8px] flex items-center justify-center shrink-0">A</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-a-blue font-black text-[8px] flex items-center justify-center shrink-0">A</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-b-red font-black text-[8px] flex items-center justify-center shrink-0">B</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-a-green font-black text-[8px] flex items-center justify-center shrink-0">A</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-b-red font-black text-[8px] flex items-center justify-center shrink-0">B</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-b-red font-black text-[8px] flex items-center justify-center shrink-0">B</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-b-red font-black text-[8px] flex items-center justify-center shrink-0">B</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-b-red font-black text-[8px] flex items-center justify-center shrink-0">B</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-a-green font-black text-[8px] flex items-center justify-center shrink-0">A</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-a-blue font-black text-[8px] flex items-center justify-center shrink-0">A</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-b-red font-black text-[8px] flex items-center justify-center shrink-0">B</div>
                    <div class="w-4 h-4 sm:w-4.5 sm:h-4.5 rounded-full bead-b-red font-black text-[8px] flex items-center justify-center shrink-0">B</div>
                    <span class="text-slate-500 text-[10px] tracking-widest shrink-0">&bull; &bull; &bull;</span>
                </div>

                <!-- Limits Display -->
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 font-medium">
                        Bet: {{ number_format($denominations[0] ?? 500, 0) }}/500,000
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
            andarBox.classList.add('bg-indigo-950/80', 'ring-2', 'ring-indigo-500');
            baharBox.classList.remove('bg-red-950/80', 'ring-2', 'ring-red-500');
            document.getElementById('status-first-bet').textContent = `₹${activeSelectedChip.toLocaleString()}`;
            document.getElementById('status-second-bet').textContent = `₹0`;
        } else {
            baharBox.classList.add('bg-red-950/80', 'ring-2', 'ring-red-500');
            andarBox.classList.remove('bg-indigo-950/80', 'ring-2', 'ring-indigo-500');
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
        andarBox.classList.remove('bg-indigo-950/80', 'ring-2', 'ring-indigo-500');
        baharBox.classList.remove('bg-red-950/80', 'ring-2', 'ring-red-500');
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
        };

        // Fullscreen toggle
        document.getElementById('btn-toggle-fullscreen')?.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        });

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
