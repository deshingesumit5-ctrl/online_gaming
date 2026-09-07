@extends('layouts.admin')

@section('page-title', 'Live Control Room: ' . $room->name)

@section('content')
<div class="space-y-6">
    <!-- Top Status Bar -->
    <div class="glass-panel p-5 flex flex-wrap items-center justify-between gap-4 border-amber-500/20">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center text-slate-950 font-black text-xl font-royal shadow-lg">
                🎮
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold font-royal text-white">{{ $room->name }}</h1>
                    <span class="badge-live px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">LIVE</span>
                </div>
                <span class="text-xs text-slate-400">Current Active Round: <strong class="text-amber-400 font-bold">#{{ $currentRound->round_number }}</strong> &bull; Status: <strong class="text-slate-200 uppercase font-mono">{{ $currentRound->status }}</strong></span>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
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

    <!-- Main Live Operator Grid: 2 Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left 7 Cols: Dealer Stage, Card Assignment, and Live Flow Controls -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Step 1: Assign First Card & Start Round -->
            <div class="glass-panel p-6 border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-xs">1</span>
                        Assign Open First Card (Joker)
                    </h3>
                    @if($currentRound->first_card)
                        <span class="text-xs font-bold text-emerald-400">Card Set: {{ strtoupper(str_replace('_', ' ', $currentRound->first_card)) }}</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.game.action', $room->id) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="start_round">

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-48 overflow-y-auto p-2 bg-slate-900/80 rounded-xl border border-slate-800">
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

            <!-- Step 2: Betting Window Trigger (30s Timer) -->
            <div class="glass-panel p-6 border-slate-800">
                <h3 class="text-sm font-bold font-royal text-white mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-xs">2</span>
                    Betting Window Control
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <form method="POST" action="{{ route('admin.game.action', $room->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="open_betting">
                        <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-sm uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition flex flex-col items-center justify-center gap-1">
                            <span>⏱️ OPEN BETTING ({{ $room->betting_duration }}s)</span>
                            <small class="text-[10px] font-normal opacity-80">Triggers live countdown for all players</small>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.game.action', $room->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="close_betting">
                        <button type="submit" class="w-full py-4 bg-amber-700 hover:bg-amber-600 text-white rounded-xl font-black text-sm uppercase tracking-wider shadow-lg transition flex flex-col items-center justify-center gap-1">
                            <span>🔒 LOCK / CLOSE BETTING</span>
                            <small class="text-[10px] font-normal opacity-80">Immediately locks all placed bets</small>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Step 3: Declare Winner & Instant Payout Engine -->
            <div class="glass-panel p-6 border-amber-500/30 bg-amber-950/10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-xs">3</span>
                        Declare Result & Execute 1:1 Auto-Payout
                    </h3>
                    @if($currentRound->status === 'result_declared')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $currentRound->winning_side === 'andar' ? 'badge-andar' : 'badge-bahar' }}">
                            {{ $currentRound->winning_side }} WON
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- ANDAR WON -->
                    <form method="POST" action="{{ route('admin.game.action', $room->id) }}" id="form-andar" onsubmit="return false;">
                        @csrf
                        <input type="hidden" name="action" value="declare_result">
                        <input type="hidden" name="winning_side" value="andar">
                        <button type="button" onclick="openConfirmModal('form-andar','Confirm declaration: ANDAR WON?','This will immediately credit 1:1 winnings to all ANDAR bettors.','andar')" class="btn-andar w-full py-5 text-center flex flex-col items-center justify-center gap-1" {{ $currentRound->status === 'result_declared' ? 'disabled' : '' }}>
                            <span class="text-xl font-black font-royal tracking-widest text-white">ANDAR WON</span>
                            <span class="text-[11px] font-normal text-indigo-200">1:1 Payout to Andar Bets</span>
                        </button>
                    </form>

                    <!-- BAHAR WON -->
                    <form method="POST" action="{{ route('admin.game.action', $room->id) }}" id="form-bahar" onsubmit="return false;">
                        @csrf
                        <input type="hidden" name="action" value="declare_result">
                        <input type="hidden" name="winning_side" value="bahar">
                        <button type="button" onclick="openConfirmModal('form-bahar','Confirm declaration: BAHAR WON?','This will immediately credit 1:1 winnings to all BAHAR bettors.','bahar')" class="btn-bahar w-full py-5 text-center flex flex-col items-center justify-center gap-1" {{ $currentRound->status === 'result_declared' ? 'disabled' : '' }}>
                            <span class="text-xl font-black font-royal tracking-widest text-white">BAHAR WON</span>
                            <span class="text-[11px] font-normal text-red-200">1:1 Payout to Bahar Bets</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stream URL Settings -->
            <div class="glass-panel p-5 border-slate-800">
                <h4 class="text-xs font-bold uppercase text-slate-400 mb-2">Live Stream Source URL</h4>
                <form method="POST" action="{{ route('admin.game.action', $room->id) }}" class="flex gap-2">
                    @csrf
                    <input type="hidden" name="action" value="update_stream">
                    <input type="text" name="live_stream_url" value="{{ $room->live_stream_url }}" placeholder="Enter YouTube Embed URL, HLS/m3u8, or MP4 URL"
                           class="form-input-custom text-xs">
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-semibold whitespace-nowrap transition border border-slate-700">
                        Save URL
                    </button>
                </form>
            </div>

            <!-- Table Timing Settings (Admin can edit betting timer and cancellation window) -->
            <div class="glass-panel p-5 border-slate-800">
                <h4 class="text-xs font-bold uppercase text-slate-400 mb-2 flex items-center justify-between">
                    <span>⏱️ Table Timing Controls</span>
                    <span class="text-[10px] text-amber-400 font-normal">Active: {{ $room->cancellation_duration }}s Cancel Window</span>
                </h4>
                <form method="POST" action="{{ route('admin.game.action', $room->id) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @csrf
                    <input type="hidden" name="action" value="update_room_timings">
                    <div>
                        <label class="text-[10px] text-slate-400 block mb-1 font-bold uppercase">Betting Duration (Sec)</label>
                        <input type="number" name="betting_duration" value="{{ $room->betting_duration }}" min="5" max="300" required class="form-input-custom text-xs">
                    </div>
                    <div>
                        <label class="text-[10px] text-slate-400 block mb-1 font-bold uppercase">Cancel Window (Sec)</label>
                        <div class="flex gap-2">
                            <input type="number" name="cancellation_duration" value="{{ $room->cancellation_duration }}" min="0" max="300" required class="form-input-custom text-xs">
                            <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-slate-950 font-black rounded-lg text-xs uppercase tracking-wider whitespace-nowrap transition">
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right 5 Cols: Live Bet Book & Liability Exposure -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Liability Summary Bar -->
            <div class="glass-panel p-5 border-slate-800">
                <h3 class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-4">Round #{{ $currentRound->round_number }} Bet Book & Volume</h3>
                
                <div class="grid grid-cols-2 gap-4 text-center mb-4">
                    <div class="p-3 bg-indigo-950/60 rounded-xl border border-indigo-700/50">
                        <span class="text-[10px] uppercase font-bold text-indigo-300 block">Andar Total</span>
                        <span class="text-lg font-black text-white">{{ number_format($totalAndarAmount, 0) }}</span>
                        <span class="text-[10px] text-slate-400 block">{{ count($andarBets) }} bet(s)</span>
                    </div>

                    <div class="p-3 bg-red-950/60 rounded-xl border border-red-700/50">
                        <span class="text-[10px] uppercase font-bold text-red-300 block">Bahar Total</span>
                        <span class="text-lg font-black text-white">{{ number_format($totalBaharAmount, 0) }}</span>
                        <span class="text-[10px] text-slate-400 block">{{ count($baharBets) }} bet(s)</span>
                    </div>
                </div>

                <div class="text-xs text-slate-400 flex justify-between border-t border-slate-800 pt-3">
                    <span>Total Round Pool:</span>
                    <strong class="text-amber-300 font-bold">{{ number_format($totalAndarAmount + $totalBaharAmount, 0) }} pts</strong>
                </div>
            </div>

            <!-- Live Bets Placed in This Round -->
            <div class="glass-panel p-5 border-slate-800">
                <h3 class="text-sm font-bold font-royal text-white mb-3 flex items-center justify-between">
                    <span>Active Bets ({{ count($activeBets) }})</span>
                    <span class="text-xs font-normal text-slate-400">Auto-Refreshes</span>
                </h3>

                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @forelse($activeBets as $bet)
                        <div class="p-2.5 bg-slate-900 rounded-lg border border-slate-800 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded font-bold uppercase {{ $bet->selection === 'andar' ? 'badge-andar' : 'badge-bahar' }}">
                                    {{ $bet->selection }}
                                </span>
                                <span class="text-slate-200 font-semibold">{{ $bet->user->name ?? 'Player' }} ({{ '@' . ($bet->user->username ?? '') }})</span>
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

            <!-- Recent Rounds History -->
            <div class="glass-panel p-5 border-slate-800">
                <h4 class="text-xs font-bold uppercase text-slate-400 mb-3">Recent Closed Rounds</h4>
                <div class="space-y-2">
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
        </div>
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

<script>
    let _pendingFormId = null;
    let _pendingWinningSide = null;

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
                // Disable declare buttons
                const btnAndar = document.querySelector('#form-andar button');
                const btnBahar = document.querySelector('#form-bahar button');
                if (btnAndar) btnAndar.disabled = true;
                if (btnBahar) btnBahar.disabled = true;

                // Show square banner in center of laptop
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
</script>
@endsection
