@extends('layouts.admin')

@section('page-title', 'Live Control Room: ' . $room->name)

@push('styles')
<style>
    #admin-pen-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 16px;
        height: 16px;
        margin-left: -8px;
        margin-top: -8px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        background: #fbbf24;
        border: 2px solid #fff;
        box-shadow: 0 0 10px rgba(245, 158, 11, 0.9);
        pointer-events: none;
        z-index: 30;
        display: none;
    }
    #admin-stream-white-screen {
        z-index: 6;
    }
    #admin-cctv-live-jpg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 3;
    }
    #admin-cctv-video {
        position: relative;
        z-index: 2;
    }
    #admin-live-card-overlay {
        position: absolute;
        left: 48%;
        top: 58%;
        width: 4.8%;
        height: auto;
        aspect-ratio: 56 / 80;
        flex: none;
        margin: 0;
        transform: translate(-50%, -50%);
        border-radius: 8%;
        background: #fff;
        border: 2px solid #111;
        box-shadow: 0 4px 12px rgba(0,0,0,0.45);
        z-index: 32;
        display: none;
        pointer-events: auto;
        cursor: grab;
        padding: 3px 4px;
        flex-direction: column;
        justify-content: space-between;
        font-weight: 900;
        line-height: 0.9;
        font-size: 14px;
        font-family: Arial, Helvetica, sans-serif;
        user-select: none;
        color: #dc2626;
    }
    #admin-live-card-overlay.is-visible { display: flex; }
    #admin-live-card-overlay.is-red { color: #dc2626; }
    #admin-live-card-overlay.is-black { color: #dc2626; }
    #admin-live-card-overlay .card-index {
        display: flex;
        flex-direction: column;
        align-items: center;
        line-height: 0.85;
        font-size: 11px;
        font-weight: 900;
        color: #dc2626;
    }
    #admin-live-card-overlay .card-index-br { transform: rotate(180deg); }
    #admin-live-card-overlay .card-index .index-suit { font-size: 8px; line-height: 1; }
    #admin-live-card-overlay .card-pip-panel {
        flex: 1;
        margin: 1px 6px;
        background: #f4e8a4;
        border: 1px solid #222;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        align-content: space-evenly;
        overflow: hidden;
        padding: 1px 2px;
    }
    #admin-live-card-overlay .suit { text-align: center; font-size: 11px; color: #dc2626; line-height: 1; width: 46%; }
    #admin-live-card-overlay .card-pip-panel[data-pips="1"] .suit,
    #admin-live-card-overlay .card-pip-panel[data-pips="2"] .suit,
    #admin-live-card-overlay .card-pip-panel[data-pips="3"] .suit { width: 100%; font-size: 14px; }
    #admin-live-card-overlay .card-index,
    #admin-live-card-overlay .card-pip-panel { display: none; }
    #admin-live-card-overlay {
        padding: 0;
        overflow: hidden;
        background: transparent;
        border: 0;
    }
    #admin-live-card-overlay .card-photo {
        width: 100%;
        height: 100%;
        object-fit: fill;
        display: block;
        pointer-events: none;
        border-radius: 8%;
    }</style>
@endpush

@section('content')
<div class="space-y-5">
    <!-- Top Status Bar (Section A: Session Information & Table Info) -->
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
                <span class="text-xs text-slate-400">
                    Session <strong class="text-amber-400 font-bold">#{{ $currentRound->round_number }}</strong> &bull; ID <strong class="text-slate-200">{{ $currentRound->id }}</strong> &bull; Status: <strong class="text-slate-200 uppercase font-mono">{{ str_replace('_', ' ', $currentRound->status) }}</strong>
                    @if($currentRound->started_at)
                        &bull; Started <strong class="text-slate-200">{{ $currentRound->started_at->format('h:i:s A') }}</strong>
                        &bull; Duration: <strong id="admin-session-duration" class="text-amber-300 font-mono" data-started="{{ $currentRound->started_at->timestamp }}">00:00</strong>
                    @endif
                    &bull; Payout: <strong class="{{ $currentRound->payout_locked ? 'text-emerald-400 font-black' : 'text-amber-300 font-bold' }}">{{ $currentRound->payoutLabel() }}{{ $currentRound->payout_locked ? ' (LOCKED)' : '' }}</strong>
                </span>
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
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold border border-slate-700 transition js-busy-btn" data-busy-text="Starting Next...">
                    Start Next Session
                </button>
            </form>
        </div>
    </div>

    <!-- Step Guidance (PDF Pages 22 & 23) -->
    @php
        $stepStatus = 'session_start';
        if ($currentRound->status === 'result_declared' || $currentRound->status === 'round_closed') {
            $currentStepIndex = 8; // declare result / completed
        } elseif ($currentRound->payout_locked) {
            $currentStepIndex = 5; // continue live game / match occurs
        } elseif ($currentRound->status === 'betting_open' || $currentRound->status === 'betting_closed') {
            $currentStepIndex = 3; // betting window
        } elseif ($currentRound->first_card) {
            $currentStepIndex = 2; // initial cards
        } else {
            $currentStepIndex = 1; // session start / ref card
        }

        $workflowSteps = [
            ['num' => 1, 'name' => 'SESSION START'],
            ['num' => 2, 'name' => 'REFERENCE CARD'],
            ['num' => 3, 'name' => 'INITIAL CARDS'],
            ['num' => 4, 'name' => 'BETTING WINDOW (10s)'],
            ['num' => 5, 'name' => 'FIRST CARD CONDITION'],
            ['num' => 6, 'name' => 'CONTINUE LIVE GAME'],
            ['num' => 7, 'name' => 'MATCH OCCURS'],
            ['num' => 8, 'name' => 'DECLARE RESULT'],
            ['num' => 9, 'name' => 'PAYOUT & COMPLETE'],
        ];
    @endphp
    <div class="glass-panel p-3.5 border-slate-800/80">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-black uppercase tracking-wider text-amber-400 font-royal flex items-center gap-1.5">
                <span>📋</span> Step Guidance Workflow
            </span>
            <span class="text-[10px] text-slate-400 italic">
                * Note: Betting Window is repeatable multiple times during active session
            </span>
        </div>
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-thin text-[10px] font-bold">
            @foreach($workflowSteps as $idx => $step)
                @php
                    $isPassed = ($idx < $currentStepIndex);
                    $isCurrent = ($idx === $currentStepIndex);
                    if ($isCurrent) {
                        $badgeStyle = 'bg-amber-500 text-slate-950 border-amber-400 shadow-[0_0_10px_rgba(245,158,11,0.5)]';
                    } elseif ($isPassed) {
                        $badgeStyle = 'bg-emerald-950 text-emerald-300 border-emerald-700';
                    } else {
                        $badgeStyle = 'bg-slate-900 text-slate-500 border-slate-800';
                    }
                @endphp
                <div class="flex items-center gap-1.5 shrink-0">
                    <div class="px-2.5 py-1 rounded-lg border flex items-center gap-1.5 {{ $badgeStyle }}">
                        <span class="w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-black {{ $isCurrent ? 'bg-slate-950 text-amber-400' : ($isPassed ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400') }}">
                            {{ $isPassed ? '✓' : $step['num'] }}
                        </span>
                        <span class="whitespace-nowrap uppercase tracking-wider">{{ $step['name'] }}</span>
                    </div>
                    @if(!$loop->last)
                        <span class="text-slate-600 font-black text-xs">&rarr;</span>
                    @endif
                </div>
            @endforeach
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
                <span id="admin-first-card-set-label" class="text-xs font-bold text-emerald-400">
                    @if($currentRound->first_card)
                        Card Set: {{ strtoupper(str_replace('_', ' ', $currentRound->first_card)) }}
                    @endif
                </span>
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

                <p class="text-[10px] text-slate-500">Or keep this page focused and tap 2–9 / 0 — the card appears on the live camera table, not in a corner box.</p>
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
                    <button type="button" onclick="toggleAdminStreamFullscreen()" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition cursor-pointer" title="Toggle Fullscreen">
                        ⛶ Fullscreen
                    </button>
                </div>
            </div>

            <!-- Live Camera Screen (Matching User Panel: Video when running, White Screen when ended) -->
            <div id="admin-stream-preview" class="relative w-full rounded-xl overflow-hidden border border-slate-700/80 bg-white shadow-inner flex items-center justify-center" style="height: 175px;">
                <!-- Live Camera Video Element (Local Device Webcam) -->
                <video id="admin-live-camera" class="w-full h-full object-cover hidden" autoplay muted playsinline></video>

                <!-- External / CCTV Live Stream Player Container -->
                <div id="admin-cctv-stream-container" class="w-full h-full absolute inset-0 bg-black {{ ($room->is_streaming && $room->live_stream_url) ? '' : 'hidden' }}">
                    <video id="admin-cctv-video" class="w-full h-full object-cover {{ ($room->is_streaming && $room->live_stream_url) ? '' : 'hidden' }}" autoplay muted playsinline></video>
                    <img id="admin-cctv-live-jpg" class="w-full h-full object-cover hidden" alt="Live CCTV">
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
                <div id="admin-pen-marker" class="hidden"></div>
                <div id="admin-live-card-overlay">
                    <img class="card-photo" src="{{ asset('images/overlay-9-hearts.jpg') }}" alt="9 of Hearts">
                    <div class="card-index">
                        <div class="rank" id="admin-live-card-rank"></div>
                        <div class="index-suit" id="admin-live-card-index-suit"></div>
                    </div>
                    <div class="card-pip-panel" id="admin-live-card-pips">
                        <div class="suit" id="admin-live-card-suit"></div>
                    </div>
                    <div class="card-index card-index-br">
                        <div class="rank" id="admin-live-card-rank-b"></div>
                        <div class="index-suit" id="admin-live-card-index-suit-b"></div>
                    </div>
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
            <p class="text-[10px] text-slate-500 mt-2">Press 2–9 or 0 on this page to put that card on the live table video. Drag it over the real card so it covers it for all players.</p>
            <div class="flex items-center gap-2 mt-2">
                <button type="button" id="btn-overlay-card-smaller" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[10px] font-black uppercase tracking-wider border border-slate-700 cursor-pointer">− Size</button>
                <button type="button" id="btn-overlay-card-larger" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[10px] font-black uppercase tracking-wider border border-slate-700 cursor-pointer">+ Size</button>
                <button type="button" id="btn-overlay-card-delete" class="px-3 py-1.5 rounded-lg bg-red-700 hover:bg-red-600 text-white text-[10px] font-black uppercase tracking-wider border border-red-500 cursor-pointer">Delete</button>
            </div>
        </div>

        <!-- STEP 3: Betting Window Control (PDF Pages 8 & 9) -->
        <div class="glass-panel p-5 border-slate-800 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-black text-xs shrink-0">3</span>
                    <span>Betting Window Control</span>
                </h3>
                <span class="text-xs text-slate-400 font-mono">Status: <strong class="{{ $currentRound->status === 'betting_open' ? 'text-emerald-400' : 'text-amber-400' }}">{{ strtoupper($currentRound->status) }}</strong>
                    @if(!empty($currentWindow))
                        &bull; Window #{{ $currentWindow->window_number }}
                    @endif
                    @if($currentRound->status === 'betting_open')
                        &bull; <span id="admin-betting-countdown" class="text-emerald-300 font-bold" data-remaining="{{ $currentRound->remainingBettingSeconds() }}">{{ $currentRound->remainingBettingSeconds() }}s remaining</span>
                    @endif
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 my-auto">
                <form method="POST" action="{{ route('admin.game.action', $room->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="open_betting">
                    <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-xs sm:text-sm uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition flex flex-col items-center justify-center gap-0.5 js-busy-btn" data-busy-text="OPENING...">
                        <span>⏱️ OPEN BETTING – 10 SEC</span>
                        <small class="text-[9px] sm:text-[10px] font-normal opacity-80">Repeatable while session is active</small>
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.game.action', $room->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="close_betting">
                    <button type="submit" class="w-full py-3.5 bg-amber-700 hover:bg-amber-600 text-white rounded-xl font-black text-xs sm:text-sm uppercase tracking-wider shadow-lg transition flex flex-col items-center justify-center gap-0.5 js-busy-btn" data-busy-text="CLOSING...">
                        <span>🔒 CLOSE BETTING</span>
                        <small class="text-[9px] sm:text-[10px] font-normal opacity-80">Immediately locks placed bets</small>
                    </button>
                </form>
            </div>
        </div>

        <!-- STEP 3B: First Card Payout Condition -->
        <div class="glass-panel p-5 border-slate-800 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-black text-xs shrink-0">3b</span>
                    <span>First Card Payout Condition</span>
                </h3>
                <span class="text-xs font-bold {{ $currentRound->payout_locked ? 'text-emerald-400' : 'text-amber-300' }}">
                    {{ $currentRound->payoutLabel() }}{{ $currentRound->payout_locked ? ' · LOCKED' : '' }}
                </span>
            </div>
            <p class="text-[10px] text-slate-500 mb-3">Locks 25% or 100% profit for this entire session. Required before declaring a winner. Resets on the next session.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <form method="POST" action="{{ route('admin.game.action', $room->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="confirm_first_card">
                    <input type="hidden" name="first_card_matched" value="1">
                    <button type="submit" class="w-full py-3.5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow transition js-busy-btn" data-busy-text="LOCKING 25%..." {{ $currentRound->payout_locked ? 'disabled' : '' }}>
                        First Card Matched – 25%
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.game.action', $room->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="confirm_first_card">
                    <input type="hidden" name="first_card_matched" value="0">
                    <button type="submit" class="w-full py-3.5 bg-indigo-700 hover:bg-indigo-600 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow transition js-busy-btn" data-busy-text="LOCKING 100%..." {{ $currentRound->payout_locked ? 'disabled' : '' }}>
                        First Card Not Matched – 100%
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
                    <button type="button" onclick="openConfirmResultModal('form-andar','andar')" class="btn-andar w-full py-3.5 text-center flex flex-col items-center justify-center gap-0.5" {{ $currentRound->status === 'result_declared' ? 'disabled' : '' }}>
                        <span class="text-base sm:text-lg font-black font-royal tracking-widest text-white">ANDAR WON</span>
                        <span class="text-[10px] font-normal text-indigo-200">{{ $currentRound->payoutLabel() }} · Confirm & Process</span>
                    </button>
                </form>

                <!-- BAHAR WON -->
                <form method="POST" action="{{ route('admin.game.action', $room->id) }}" id="form-bahar" onsubmit="return false;">
                    @csrf
                    <input type="hidden" name="action" value="declare_result">
                    <input type="hidden" name="winning_side" value="bahar">
                    <button type="button" onclick="openConfirmResultModal('form-bahar','bahar')" class="btn-bahar w-full py-3.5 text-center flex flex-col items-center justify-center gap-0.5" {{ $currentRound->status === 'result_declared' ? 'disabled' : '' }}>
                        <span class="text-base sm:text-lg font-black font-royal tracking-widest text-white">BAHAR WON</span>
                        <span class="text-[10px] font-normal text-red-200">{{ $currentRound->payoutLabel() }} · Confirm & Process</span>
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
                <span>Total Session Pool:</span>
                <strong class="text-amber-300 font-bold">{{ number_format($totalAndarAmount + $totalBaharAmount, 0) }} pts</strong>
            </div>
            <div class="text-[10px] text-slate-500 mt-1">Active users this session: <strong class="text-slate-300">{{ $room->active_users_count }}</strong></div>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-1">
        <div class="glass-panel p-5 border-slate-800">
            <h4 class="text-xs font-bold uppercase text-slate-400 mb-2.5">Betting Window History</h4>
            <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                @forelse(($bettingWindows ?? []) as $w)
                    <div class="p-2 bg-slate-900 rounded-lg border border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-200 font-bold">#{{ $w->window_number }}</span>
                        <span class="text-slate-400">{{ $w->started_at ? $w->started_at->format('H:i:s') : '-' }} → {{ $w->ended_at ? $w->ended_at->format('H:i:s') : 'open' }}</span>
                        <span class="uppercase font-bold {{ $w->status === 'open' ? 'text-emerald-400' : 'text-slate-400' }}">{{ $w->status }}</span>
                    </div>
                @empty
                    <div class="text-slate-500 text-xs text-center py-2">No betting windows yet.</div>
                @endforelse
            </div>
        </div>
        <div class="glass-panel p-5 border-slate-800">
            <h4 class="text-xs font-bold uppercase text-slate-400 mb-2.5">Session Completion</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2 rounded-lg bg-slate-900 border border-slate-800"><span class="text-slate-500 block">Total bets</span><strong class="text-white">{{ number_format($totalAndarAmount + $totalBaharAmount, 0) }} pts</strong></div>
                <div class="p-2 rounded-lg bg-slate-900 border border-slate-800"><span class="text-slate-500 block">Processed</span><strong class="text-white">{{ number_format($sessionProcessed ?? 0, 0) }} pts</strong></div>
                <div class="p-2 rounded-lg bg-slate-900 border border-slate-800"><span class="text-slate-500 block">Winners</span><strong class="text-emerald-400">{{ $sessionWinners ?? 0 }}</strong></div>
                <div class="p-2 rounded-lg bg-slate-900 border border-slate-800"><span class="text-slate-500 block">Losers</span><strong class="text-red-400">{{ $sessionLosers ?? 0 }}</strong></div>
            </div>
        </div>
        <div class="glass-panel p-5 border-slate-800">
            <h4 class="text-xs font-bold uppercase text-slate-400 mb-2.5">Audit Log</h4>
            <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                @forelse(($auditLogs ?? []) as $log)
                    <div class="text-[10px] text-slate-300 border-b border-slate-800/80 pb-1">
                        <span class="text-slate-500">{{ $log->created_at?->format('H:i:s') }}</span>
                        <strong class="text-amber-300 ml-1">{{ $log->action }}</strong>
                        @if($log->new_state)
                            <span class="text-slate-500"> → {{ $log->new_state }}</span>
                        @endif
                    </div>
                @empty
                    <div class="text-slate-500 text-xs text-center py-2">No audit entries yet.</div>
                @endforelse
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
                Confirm & Process Result
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
    let adminWantsLive = {{ $room->is_streaming ? 'true' : 'false' }};
    let adminHlsWatchdog = null;
    let adminStreamStarting = false;
    const currentRoomId = {{ $room->id }};
    const configuredStreamUrl = @json($room->live_stream_url ?? '');
    const streamChannel = ('BroadcastChannel' in window) ? new BroadcastChannel('fun2win_room_' + currentRoomId) : null;
    const canvasForFrames = document.getElementById('admin-stream-canvas');
    const canvasCtx = canvasForFrames ? canvasForFrames.getContext('2d') : null;
    const penUpdateUrl = @json(route('admin.game.pen.position.update', $room->id));
    const adminPenMarker = document.getElementById('admin-pen-marker');
    const adminPreview = document.getElementById('admin-stream-preview');
    let adminPenStreaming = false;
    let lastPenPost = null;
    let lastAdminPointer = null;

    function showAdminPen(x, y, visible) {
        if (!adminPenMarker) return;
        if (!visible) {
            adminPenMarker.style.display = 'none';
            return;
        }
        adminPenMarker.style.display = 'block';
        adminPenMarker.style.left = (x * 100) + '%';
        adminPenMarker.style.top = (y * 100) + '%';
    }

    function publishPenPosition(x, y, visible) {
        const payload = { type: 'pen-position', x: x, y: y, visible: !!visible, t: Date.now() };
        showAdminPen(x, y, visible);
        if (streamChannel) {
            streamChannel.postMessage(payload);
        }
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.content : '';
        const body = JSON.stringify({ x: x, y: y, visible: !!visible });
        if (lastPenPost) {
            lastPenPost.body = body;
            return;
        }
        lastPenPost = { body: body };
        const send = () => {
            const next = lastPenPost.body;
            fetch(penUpdateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: next
            }).finally(() => {
                if (lastPenPost && lastPenPost.body !== next) {
                    send();
                } else {
                    lastPenPost = null;
                }
            });
        };
        send();
    }

    function penCoordsFromEvent(e) {
        if (!adminPreview) return null;
        const rect = adminPreview.getBoundingClientRect();
        const src = e.touches && e.touches[0] ? e.touches[0] : e;
        const x = (src.clientX - rect.left) / rect.width;
        const y = (src.clientY - rect.top) / rect.height;
        if (!isFinite(x) || !isFinite(y)) return null;
        return {
            x: Math.min(1, Math.max(0, x)),
            y: Math.min(1, Math.max(0, y))
        };
    }

    function onAdminPenMove(e) {
        const src = e.touches && e.touches[0] ? e.touches[0] : e;
        if (src && isFinite(src.clientX) && isFinite(src.clientY)) {
            lastAdminPointer = { x: src.clientX, y: src.clientY };
        }
        if (!adminPenStreaming) return;
        const pos = penCoordsFromEvent(e);
        if (!pos) return;
        e.preventDefault();
        publishPenPosition(pos.x, pos.y, true);
    }

    function onAdminPenLeave() {
        if (!adminPenStreaming) return;
        publishPenPosition(0, 0, false);
    }

    function bindAdminPenTracking() {
        adminPenStreaming = true;
        if (adminPreview) adminPreview.classList.add('is-pen-live');
        if (!adminPreview || adminPreview._penBound) return;
        adminPreview._penBound = true;
        adminPreview.addEventListener('pointermove', onAdminPenMove);
        adminPreview.addEventListener('pointerdown', onAdminPenMove);
        adminPreview.addEventListener('pointerleave', onAdminPenLeave);
        adminPreview.addEventListener('touchmove', onAdminPenMove, { passive: false });
    }

    function unbindAdminPenTracking() {
        adminPenStreaming = false;
        if (adminPreview) adminPreview.classList.remove('is-pen-live');
        showAdminPen(0, 0, false);
        publishPenPosition(0, 0, false);
    }

    function checkStreamInputType(val) {
        const warningEl = document.getElementById('rtsp-warning-badge');
        if (!warningEl) return;
        if (val && val.trim().toLowerCase().startsWith('rtsp://')) {
            warningEl.classList.remove('hidden');
        } else {
            warningEl.classList.add('hidden');
        }
    }

    let adminLiveJpegTimer = null;
    const liveJpegUrl = @json(route('game.live.jpeg', $room->id));
    const livePlaylistUrl = @json(route('game.live.playlist', $room->id));

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

    function isAdminVideoPlaying(video) {
        return !!(video && adminHls && video.readyState >= 2 && !video.paused && video.videoWidth > 0);
    }

    function stopAdminHls() {
        const cctvVideo = document.getElementById('admin-cctv-video');
        if (cctvVideo && cctvVideo._liveEdgeIv) {
            clearInterval(cctvVideo._liveEdgeIv);
            cctvVideo._liveEdgeIv = null;
        }
        if (adminHls) {
            try { adminHls.destroy(); } catch (e) {}
            adminHls = null;
        }
    }

    function sanitizeStreamUrl(url) {
        if (!url) return '';
        const raw = String(url).trim();
        const m = raw.match(/https?:\/\/[^\s"'<>\\]+/i) || raw.match(/rtsp:\/\/[^\s"'<>\\]+/i);
        const candidate = m ? m[0] : raw;
        try {
            const u = new URL(candidate);
            const host = (u.hostname || '').toLowerCase();
            if (!host) return candidate;
            if (host.indexOf('criterion-trademark') !== -1) return candidate;
            return u.href;
        } catch (e) {
            return candidate;
        }
    }

    function parseStreamUrl(url) {
        if (!url) return null;
        url = String(url).trim();
        if (!url) return null;
        const ytMatch = /(?:youtube\.com\/(?:watch\?v=|embed\/|live\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/i.exec(url);
        if (ytMatch) {
            return {
                type: 'youtube',
                embedUrl: 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1&mute=1&playsinline=1&enablejsapi=1&rel=0'
            };
        }
        if (url.toLowerCase().includes('.m3u8') || /^rtsp:\/\//i.test(url)) {
            return { type: 'hls', streamUrl: url };
        }
        if (/\.(jpe?g|png|mjpeg)(\?|$)/i.test(url) || /snapshot|image\.cgi|jpg\/image|picture/i.test(url)) {
            return { type: 'jpeg', streamUrl: url };
        }
        return { type: 'video', streamUrl: url };
    }

    function showAdminCctvStage() {
        const whiteScreen = document.getElementById('admin-stream-white-screen');
        const cctvContainer = document.getElementById('admin-cctv-stream-container');
        const webcamVideo = document.getElementById('admin-live-camera');
        if (whiteScreen) whiteScreen.classList.add('hidden');
        if (cctvContainer) cctvContainer.classList.remove('hidden');
        if (webcamVideo) webcamVideo.classList.add('hidden');
    }

    function startAdminLiveJpeg() {
        const cctvContainer = document.getElementById('admin-cctv-stream-container');
        const img = document.getElementById('admin-cctv-live-jpg');
        if (!img || !adminWantsLive) return;
        if (cctvContainer) cctvContainer.classList.remove('hidden');
        const tick = () => {
            if (!adminWantsLive) return;
            const probe = new Image();
            probe.onload = function () {
                const liveVideo = document.getElementById('admin-cctv-video');
                if (liveVideo && liveVideo.videoWidth > 0 && !liveVideo.paused) {
                    img.classList.add('hidden');
                    return;
                }
                img._jpegFailCount = 0;
                img.src = probe.src;
                img.classList.remove('hidden');
                const whiteScreen = document.getElementById('admin-stream-white-screen');
                if (whiteScreen) whiteScreen.classList.add('hidden');
            };
            probe.onerror = function () {
                img._jpegFailCount = (img._jpegFailCount || 0) + 1;
            };
            probe.src = liveJpegUrl + (liveJpegUrl.indexOf('?') >= 0 ? '&' : '?') + 't=' + Date.now();
        };
        tick();
        if (!adminLiveJpegTimer) {
            adminLiveJpegTimer = setInterval(tick, 400);
        }
    }

    function attachAdminHls(video, playUrl, fallbackUrl) {
        if (!video || !playUrl || !adminWantsLive) return;
        if (isAdminVideoPlaying(video)) {
            video.play().catch(() => {});
            return;
        }
        if (adminHls) {
            video.play().catch(() => {});
            return;
        }
        video.classList.remove('hidden');
        video.muted = true;
        video.setAttribute('muted', '');
        video.autoplay = true;
        video.playsInline = true;
        video.setAttribute('playsinline', '');
        if (!Hls.isSupported()) {
            if (video.canPlayType('application/vnd.apple.mpegurl')) {
                playNativeHlsAtLiveEdge(video, playUrl);
            }
            return;
        }
        adminHls = createLowLatencyHls();
        adminHls.loadSource(playUrl);
        adminHls.attachMedia(video);
        adminHls.on(Hls.Events.MANIFEST_PARSED, () => {
            video.play().catch(() => {});
        });
        video.addEventListener('playing', function () {
            if (video.videoWidth > 0) {
                const jpg = document.getElementById('admin-cctv-live-jpg');
                if (jpg) jpg.classList.add('hidden');
            }
        });
        adminHls.on(Hls.Events.ERROR, function(_, data) {
            if (!adminWantsLive || !data || !adminHls) return;
            if (!data.fatal) {
                video.play().catch(() => {});
                return;
            }
            if (data.type === Hls.ErrorTypes.NETWORK_ERROR) {
                if (fallbackUrl && fallbackUrl !== playUrl && !video._triedProxyHls) {
                    video._triedProxyHls = true;
                    try { adminHls.destroy(); } catch (e) {}
                    adminHls = null;
                    attachAdminHls(video, fallbackUrl, null);
                    return;
                }
                startAdminLiveJpeg();
                adminHls.startLoad();
                video.play().catch(() => {});
                return;
            }
            if (data.type === Hls.ErrorTypes.MEDIA_ERROR) {
                adminHls.recoverMediaError();
                video.play().catch(() => {});
            }
        });
        video.onclick = function() {
            video.play().catch(() => {});
        };
    }

    function startAdminHlsWatchdog(video) {
        if (adminHlsWatchdog) clearInterval(adminHlsWatchdog);
        adminHlsWatchdog = setInterval(() => {
            if (!adminWantsLive || !video) return;
            if (video.paused || video.ended) {
                video.play().catch(() => {});
            }
        }, 2000);
    }

    function keepHlsAtLiveEdge(hls, video) {
        return;
    }

    function playNativeHlsAtLiveEdge(video, streamUrl) {
        video.src = streamUrl;
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

    async function startLiveCameraStream() {
        const existingVideo = document.getElementById('admin-cctv-video');
        if (adminHls && existingVideo) {
            adminWantsLive = true;
            showAdminCctvStage();
            existingVideo.play().catch(() => {});
            startAdminLiveJpeg();
            return;
        }
        if (adminStreamStarting) return;
        adminStreamStarting = true;
        adminWantsLive = true;
        try {
            const whiteScreen = document.getElementById('admin-stream-white-screen');
            const badge = document.getElementById('admin-stream-status-badge');
            const cctvContainer = document.getElementById('admin-cctv-stream-container');
            const cctvVideo = document.getElementById('admin-cctv-video');
            const cctvIframe = document.getElementById('admin-cctv-iframe');
            const webcamVideo = document.getElementById('admin-live-camera');
            const rawUrl = String(configuredStreamUrl || '').trim();
            const parsed = parseStreamUrl(sanitizeStreamUrl(rawUrl) || rawUrl);

            if (rawUrl || parsed) {
                showAdminCctvStage();
                startAdminLiveJpeg();

                if (parsed && parsed.type === 'youtube') {
                    if (cctvVideo) cctvVideo.classList.add('hidden');
                    if (cctvIframe) {
                        cctvIframe.src = parsed.embedUrl;
                        cctvIframe.classList.remove('hidden');
                    }
                } else if (parsed && parsed.type === 'hls') {
                    if (cctvIframe) cctvIframe.classList.add('hidden');
                    if (cctvVideo) {
                        cctvVideo.classList.remove('hidden');
                        attachAdminHls(cctvVideo, livePlaylistUrl, null);
                        startAdminHlsWatchdog(cctvVideo);
                    }
                } else if (parsed && parsed.type === 'jpeg') {
                    if (cctvIframe) cctvIframe.classList.add('hidden');
                    if (cctvVideo) cctvVideo.classList.add('hidden');
                } else if (parsed && parsed.type === 'video') {
                    if (cctvIframe) cctvIframe.classList.add('hidden');
                    if (cctvVideo) {
                        cctvVideo.classList.remove('hidden');
                        if (cctvVideo.src !== parsed.streamUrl) cctvVideo.src = parsed.streamUrl;
                        cctvVideo.play().catch(() => {});
                    }
                }

                if (badge) {
                    badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-red-600 text-white animate-pulse';
                    badge.textContent = '🔴 LIVE (CCTV LINK)';
                }
            } else {
                if (cctvContainer) cctvContainer.classList.add('hidden');
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Camera is not available in this browser.');
                }
                if (!adminMediaStream) {
                    adminMediaStream = await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'environment' },
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

            setTimeout(startAdminLiveJpeg, 600);
            setTimeout(startAdminLiveJpeg, 1600);

            const activeVideoEl = parsed && parsed.type !== 'youtube' ? cctvVideo : (!parsed ? webcamVideo : null);
            if (frameBroadcastInterval) clearInterval(frameBroadcastInterval);

            if (activeVideoEl) {
                frameBroadcastInterval = setInterval(() => {
                    if (!activeVideoEl || !canvasCtx) return;
                    if (activeVideoEl.paused || activeVideoEl.ended) {
                        activeVideoEl.play().catch(() => {});
                        return;
                    }
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
            bindAdminPenTracking();
        } catch (err) {
            console.error('Camera/Stream start error:', err);
            const hasCctv = String(configuredStreamUrl || '').trim() !== '';
            if (hasCctv) {
                showAdminCctvStage();
                startAdminLiveJpeg();
            } else if (!{{ $room->is_streaming ? 'true' : 'false' }}) {
                alert('Could not start stream: ' + (err.message || 'Please check stream link or camera permission.'));
            }
        } finally {
            adminStreamStarting = false;
        }
    }

    async function endLiveCameraStream() {
        const whiteScreen = document.getElementById('admin-stream-white-screen');
        const badge = document.getElementById('admin-stream-status-badge');
        const cctvContainer = document.getElementById('admin-cctv-stream-container');
        const cctvVideo = document.getElementById('admin-cctv-video');
        const cctvIframe = document.getElementById('admin-cctv-iframe');
        const webcamVideo = document.getElementById('admin-live-camera');

        adminWantsLive = false;
        adminStreamStarting = false;
        if (adminHlsWatchdog) {
            clearInterval(adminHlsWatchdog);
            adminHlsWatchdog = null;
        }

        if (frameBroadcastInterval) {
            clearInterval(frameBroadcastInterval);
            frameBroadcastInterval = null;
        }

        if (adminLiveJpegTimer) {
            clearInterval(adminLiveJpegTimer);
            adminLiveJpegTimer = null;
        }

        stopAdminHls();

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
        const cctvJpg = document.getElementById('admin-cctv-live-jpg');
        if (cctvJpg) {
            cctvJpg.removeAttribute('src');
            cctvJpg.classList.add('hidden');
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
        unbindAdminPenTracking();
    }

    function openConfirmResultModal(formId, side) {
        const payoutLocked = {{ $currentRound->payout_locked ? 'true' : 'false' }};
        if (!payoutLocked) {
            if (typeof window.showToast === 'function') {
                window.showToast('Confirm first card condition (25% or 100%) before declaring the result.', 'error');
            } else {
                alert('Confirm first card condition (25% or 100%) before declaring the result.');
            }
            return;
        }
        _pendingFormId = formId;
        _pendingWinningSide = side;

        const sideUpper = side.toUpperCase();
        const payoutLabel = @json($currentRound->payoutLabel());
        const totalAndar = @json(number_format($totalAndarAmount, 0));
        const totalBahar = @json(number_format($totalBaharAmount, 0));
        const sessionId = @json($currentRound->round_number);

        document.getElementById('confirmTitle').textContent = 'Confirm Result?';
        
        const bodyEl = document.getElementById('confirmBody');
        if (bodyEl) {
            bodyEl.innerHTML = `
                <div class="space-y-2 text-left bg-slate-900/80 p-3.5 rounded-xl border border-slate-700/80 my-2 text-xs font-semibold">
                    <div class="flex justify-between"><span class="text-slate-400">SESSION:</span><strong class="text-white font-mono">#${sessionId}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-400">Winning Side:</span><strong class="${side === 'andar' ? 'text-indigo-400' : 'text-red-400'} font-black text-sm uppercase">${sideUpper}</strong></div>
                    <div class="flex justify-between"><span class="text-slate-400">Payout Mode:</span><strong class="text-amber-300 font-bold">${payoutLabel}</strong></div>
                    <div class="flex justify-between border-t border-slate-800 pt-1.5"><span class="text-slate-400">Total Andar Bets:</span><strong class="text-white">${totalAndar} Points</strong></div>
                    <div class="flex justify-between"><span class="text-slate-400">Total Bahar Bets:</span><strong class="text-white">${totalBahar} Points</strong></div>
                </div>
                <p class="text-[11px] text-slate-400 text-center mt-2">This action will close the session and process the applicable winning points.</p>
            `;
        }

        const icon = document.getElementById('confirmIcon');
        const btn  = document.getElementById('confirmOkBtn');
        btn.disabled = false;
        btn.innerHTML = 'CONFIRM & PROCESS RESULT';

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
            submitBtn.innerHTML = 'CONFIRM & PROCESS RESULT';
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

    function resumeAdminLiveStream() {
        if (!adminWantsLive) return;
        const hasCctv = String(configuredStreamUrl || '').trim() !== '';
        if (!hasCctv) return;
        const video = document.getElementById('admin-cctv-video');
        if (adminHls && video) {
            showAdminCctvStage();
            video.play().catch(() => {});
            startAdminLiveJpeg();
            return;
        }
        startLiveCameraStream();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', resumeAdminLiveStream);
    } else {
        resumeAdminLiveStream();
    }
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) resumeAdminLiveStream();
    });
    document.addEventListener('visibilitychange', function () {
        if (document.hidden || !adminWantsLive) return;
        const video = document.getElementById('admin-cctv-video');
        if (video) video.play().catch(() => {});
    });

    let adminOverlayCard = @json($currentRound->first_card);
    let adminOverlayX = 0.48;
    let adminOverlayY = 0.58;
    let adminOverlayScale = 1;
    let adminOverlayVisible = false;
    const OVERLAY_SCALE_KEY = 'fun2win_overlay_default_scale_' + currentRoomId;
    const OVERLAY_BASE_VIDEO_FRAC = 0.048;
    function readDefaultOverlayScale() {
        try {
            const v = parseFloat(localStorage.getItem(OVERLAY_SCALE_KEY));
            if (isFinite(v) && v >= 0.2 && v <= 3) return v;
        } catch (e) {}
        return 1;
    }
    function saveDefaultOverlayScale(scale) {
        adminOverlayScale = scale;
        try { localStorage.setItem(OVERLAY_SCALE_KEY, String(scale)); } catch (e) {}
    }
    adminOverlayScale = readDefaultOverlayScale();

    if (adminPreview) {
        adminPreview.addEventListener('pointermove', function (e) {
            lastAdminPointer = { x: e.clientX, y: e.clientY };
        });
        adminPreview.addEventListener('pointerdown', function (e) {
            lastAdminPointer = { x: e.clientX, y: e.clientY };
        });
    }

    function overlayMediaSize(media) {
        if (!media) return { mw: 0, mh: 0 };
        if (media.videoWidth) return { mw: media.videoWidth, mh: media.videoHeight };
        if (media.naturalWidth) return { mw: media.naturalWidth, mh: media.naturalHeight };
        return { mw: 0, mh: 0 };
    }

    function adminOverlayMedia() {
        const cctv = document.getElementById('admin-cctv-video');
        if (cctv && cctv.videoWidth > 0 && !cctv.classList.contains('hidden')) return cctv;
        const cam = document.getElementById('admin-live-camera');
        if (cam && cam.videoWidth > 0 && !cam.classList.contains('hidden')) return cam;
        const jpg = document.getElementById('admin-cctv-live-jpg');
        if (jpg && jpg.naturalWidth > 0 && !jpg.classList.contains('hidden')) return jpg;
        return cctv || cam || jpg;
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

    function applyAdminOverlayBox(overlay) {
        if (!overlay || !adminPreview) return;
        const metrics = overlayCoverMetrics(adminPreview, adminOverlayMedia());
        const widthPx = metrics.mw * metrics.coverScale * OVERLAY_BASE_VIDEO_FRAC * adminOverlayScale;
        overlay.style.width = widthPx + 'px';
        overlay.style.height = (widthPx * 168 / 118) + 'px';
        overlay.style.left = (metrics.offsetX + adminOverlayX * metrics.displayW) + 'px';
        overlay.style.top = (metrics.offsetY + adminOverlayY * metrics.displayH) + 'px';
        overlay.style.transform = 'translate(-50%, -50%)';
    }

    function coverCoordsFromClient(clientX, clientY) {
        if (!adminPreview) return null;
        const rect = adminPreview.getBoundingClientRect();
        const metrics = overlayCoverMetrics(adminPreview, adminOverlayMedia());
        if (!metrics.displayW || !metrics.displayH) return null;
        return {
            x: Math.min(1, Math.max(0, (clientX - rect.left - metrics.offsetX) / metrics.displayW)),
            y: Math.min(1, Math.max(0, (clientY - rect.top - metrics.offsetY) / metrics.displayH))
        };
    }

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

    function paintAdminOverlayCard(code, x, y, scale) {
        const overlay = document.getElementById('admin-live-card-overlay');
        const rankEl = document.getElementById('admin-live-card-rank');
        const rankB = document.getElementById('admin-live-card-rank-b');
        const indexSuit = document.getElementById('admin-live-card-index-suit');
        const indexSuitB = document.getElementById('admin-live-card-index-suit-b');
        const pips = document.getElementById('admin-live-card-pips');
        if (!overlay || !code) return;
        adminOverlayCard = code;
        if (x != null) adminOverlayX = Number(x);
        if (y != null) adminOverlayY = Number(y);
        if (scale != null && isFinite(Number(scale))) adminOverlayScale = Number(scale);
        const parts = String(code).split('_');
        const raw = (parts[0] || '').toUpperCase();
        const suit = (parts[1] || 'spades').toLowerCase();
        const shortVal = raw === 'JACK' ? 'J' : (raw === 'QUEEN' ? 'Q' : (raw === 'KING' ? 'K' : (raw === 'ACE' ? 'A' : raw)));
        const symbols = { spades: '♠', hearts: '♥', diamonds: '♦', clubs: '♣' };
        const symbol = symbols[suit] || '♠';
        if (rankEl) rankEl.textContent = shortVal;
        if (rankB) rankB.textContent = shortVal;
        if (indexSuit) indexSuit.textContent = symbol;
        if (indexSuitB) indexSuitB.textContent = symbol;
        fillOverlayPips(pips, symbol, overlayPipCount(raw), 'admin-live-card-suit');
        overlay.classList.add('is-visible');
        overlay.classList.add('is-red');
        overlay.classList.remove('is-black');
        adminOverlayVisible = true;
        applyAdminOverlayBox(overlay);
    }

    function hideAdminOverlayCard() {
        const overlay = document.getElementById('admin-live-card-overlay');
        if (overlay) overlay.classList.remove('is-visible');
        adminOverlayVisible = false;
        if (streamChannel) {
            streamChannel.postMessage({
                type: 'overlay_card',
                first_card: null,
                card_hidden: true,
                t: Date.now()
            });
        }
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.content : '';
        fetch("{{ route('admin.game.action', $room->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                action: 'hide_card_overlay'
            })
        }).catch(() => {});
    }

    function publishOverlayCard() {
        if (!adminOverlayVisible || !adminOverlayCard) return;
        if (streamChannel) {
            streamChannel.postMessage({
                type: 'overlay_card',
                first_card: adminOverlayCard,
                card_x: adminOverlayX,
                card_y: adminOverlayY,
                card_scale: adminOverlayScale,
                t: Date.now()
            });
        }
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.content : '';
        fetch("{{ route('admin.game.action', $room->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                action: 'update_first_card',
                first_card: adminOverlayCard,
                x: adminOverlayX,
                y: adminOverlayY,
                scale: adminOverlayScale
            })
        }).then(r => r.json()).then(data => {
            if (data && data.success) {
                const label = document.getElementById('admin-first-card-set-label');
                if (label) label.textContent = 'Card Set: ' + adminOverlayCard.replace('_', ' ').toUpperCase();
            }
        }).catch(() => {});
    }

    (function bindAdminCardDrag() {
        const overlay = document.getElementById('admin-live-card-overlay');
        if (!overlay || !adminPreview) return;
        let dragging = false;
        overlay.addEventListener('pointerdown', function (e) {
            dragging = true;
            overlay.setPointerCapture(e.pointerId);
            overlay.style.cursor = 'grabbing';
            e.preventDefault();
            e.stopPropagation();
        });
        overlay.addEventListener('pointermove', function (e) {
            if (!dragging) return;
            e.preventDefault();
            e.stopPropagation();
            const rect = adminPreview.getBoundingClientRect();
            const metrics = overlayCoverMetrics(adminPreview, adminOverlayMedia());
            adminOverlayX = Math.min(1, Math.max(0, (e.clientX - rect.left - metrics.offsetX) / metrics.displayW));
            adminOverlayY = Math.min(1, Math.max(0, (e.clientY - rect.top - metrics.offsetY) / metrics.displayH));
            applyAdminOverlayBox(overlay);
        });
        overlay.addEventListener('pointerup', function (e) {
            if (!dragging) return;
            dragging = false;
            overlay.style.cursor = 'grab';
            e.stopPropagation();
            publishOverlayCard();
        });
    })();

    document.addEventListener('keydown', function (e) {
        const tag = (e.target && e.target.tagName) ? e.target.tagName.toUpperCase() : '';
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || e.target.isContentEditable) {
            return;
        }

        const rankMap = {
            '2': '2', '3': '3', '4': '4', '5': '5', '6': '6', '7': '7', '8': '8', '9': '9', '0': '10'
        };
        const rank = rankMap[e.key];
        if (!rank) return;

        const selected = document.querySelector('input[name="first_card"]:checked');
        const currentCode = selected ? selected.value : @json($currentRound->first_card);
        const parts = (currentCode || '8_spades').split('_');
        const suit = parts[1] || 'spades';
        const newCode = rank + '_' + suit;

        const radio = document.querySelector('input[name="first_card"][value="' + newCode + '"]');
        if (radio) radio.checked = true;

        let placeX = adminOverlayX;
        let placeY = adminOverlayY;
        if (lastAdminPointer) {
            const atCursor = coverCoordsFromClient(lastAdminPointer.x, lastAdminPointer.y);
            if (atCursor) {
                placeX = atCursor.x;
                placeY = atCursor.y;
            }
        }
        paintAdminOverlayCard(newCode, placeX, placeY, readDefaultOverlayScale());
        publishOverlayCard();
    });

    const btnSmaller = document.getElementById('btn-overlay-card-smaller');
    const btnLarger = document.getElementById('btn-overlay-card-larger');
    if (btnSmaller) {
        btnSmaller.addEventListener('click', function () {
            saveDefaultOverlayScale(Math.max(0.2, Math.round((adminOverlayScale - 0.1) * 10) / 10));
            const overlay = document.getElementById('admin-live-card-overlay');
            if (overlay) applyAdminOverlayBox(overlay);
            publishOverlayCard();
        });
    }
    if (btnLarger) {
        btnLarger.addEventListener('click', function () {
            saveDefaultOverlayScale(Math.min(2.5, Math.round((adminOverlayScale + 0.1) * 10) / 10));
            const overlay = document.getElementById('admin-live-card-overlay');
            if (overlay) applyAdminOverlayBox(overlay);
            publishOverlayCard();
        });
    }
    const btnDelete = document.getElementById('btn-overlay-card-delete');
    if (btnDelete) {
        btnDelete.addEventListener('click', function () {
            hideAdminOverlayCard();
        });
    }


    if (adminPreview && typeof ResizeObserver !== 'undefined') {
        new ResizeObserver(function () {
            const overlay = document.getElementById('admin-live-card-overlay');
            if (overlay && overlay.classList.contains('is-visible')) applyAdminOverlayBox(overlay);
        }).observe(adminPreview);
    }
    const adminCctvVideo = document.getElementById('admin-cctv-video');
    if (adminCctvVideo) {
        adminCctvVideo.addEventListener('loadedmetadata', function () {
            const overlay = document.getElementById('admin-live-card-overlay');
            if (overlay && overlay.classList.contains('is-visible')) applyAdminOverlayBox(overlay);
        });
    }

    function toggleAdminStreamFullscreen() {
        const el = document.getElementById('admin-stream-preview');
        if (!el) return;
        if (!document.fullscreenElement) {
            el.requestFullscreen().catch(() => {});
        } else {
            document.exitFullscreen().catch(() => {});
        }
    }

    (function initDurationTracker() {
        const el = document.getElementById('admin-session-duration');
        if (!el) return;
        const started = parseInt(el.dataset.started, 10);
        if (!started || isNaN(started)) return;
        const update = () => {
            const nowSec = Math.floor(Date.now() / 1000);
            const diff = Math.max(0, nowSec - started);
            const mins = Math.floor(diff / 60);
            const secs = diff % 60;
            el.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        };
        update();
        setInterval(update, 1000);
    })();

    (function initBettingCountdown() {
        const el = document.getElementById('admin-betting-countdown');
        if (!el) return;
        let remaining = parseInt(el.dataset.remaining, 10);
        if (isNaN(remaining) || remaining <= 0) return;
        const iv = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                el.textContent = '0s (Closing...)';
                clearInterval(iv);
            } else {
                el.textContent = `${remaining}s remaining`;
            }
        }, 1000);
    })();

    document.querySelectorAll('.js-busy-btn').forEach(function (btn) {
        const form = btn.closest('form');
        if (!form) return;
        form.addEventListener('submit', function () {
            if (btn.disabled) return;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = btn.dataset.busyText || 'Processing...';
        });
    });
</script>
@endsection
