/**
 * Fun 2 Win Game Engine
 * Handles live game state synchronization, betting timer countdown, and placing bets.
 */
class GameEngine {
    constructor(config) {
        this.roomId = config.roomId;
        this.stateUrl = config.stateUrl;
        this.betUrl = config.betUrl;
        this.cancelUrlBase = config.cancelUrlBase;
        this.csrfToken = config.csrfToken;
        this.selectedChip = config.defaultChip || 100;
        this.cancellationDuration = config.cancellationDuration || 30;

        this.currentRoundId = null;
        this.currentStatus = null;
        this.remainingSeconds = 0;
        this.timerInterval = null;
        this.pollInterval = null;

        // De-duplication tracking (State variable + storage guard)
        const storedLastProcessed = sessionStorage.getItem('f2w_last_processed_round_' + this.roomId);
        this.lastProcessedRoundId = storedLastProcessed ? parseInt(storedLastProcessed, 10) : null;
        this.resultModalShownForRound = this.lastProcessedRoundId;
        this.lastWalletBalance = null;
        this.isInitialStateFetch = true;

        this.init();
    }

    init() {
        this.bindEvents();
        this.fetchState();
        this.startPolling();
    }

    bindEvents() {
        // Chip selection
        document.querySelectorAll('.chip-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                this.selectedChip = parseInt(btn.dataset.value, 10);
                this.updateBetButtonLabels();
            });
        });

    }

    updateBetButtonLabels() {
        const andarAmountSpan = document.getElementById('andar-current-chip');
        const baharAmountSpan = document.getElementById('bahar-current-chip');
        if (andarAmountSpan) andarAmountSpan.textContent = this.selectedChip.toLocaleString();
        if (baharAmountSpan) baharAmountSpan.textContent = this.selectedChip.toLocaleString();
    }

    startPolling() {
        this.pollInterval = setInterval(() => {
            this.fetchState();
        }, 1500);
    }

    async fetchState() {
        try {
            const res = await fetch(this.stateUrl, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;

            const data = await res.json();
            this.renderState(data);
        } catch (err) {
            console.error('Error fetching game state:', err);
        }
    }

    updateWalletBalance(balance) {
        if (balance === undefined || balance === null) return;
        const balanceEls = document.querySelectorAll('.user-wallet-balance');
        balanceEls.forEach(el => {
            el.textContent = Math.floor(Number(balance)).toLocaleString('en-US');
        });
    }

    renderState(data) {
        const isInitial = this.isInitialStateFetch;
        this.isInitialStateFetch = false;

        // 1. Update Wallet Balance & Detect Point Approvals (Deduplicated per balance increase)
        if (data.wallet_balance !== undefined) {
            const currentBal = Number(data.wallet_balance);
            if (this.lastWalletBalance !== null && currentBal > this.lastWalletBalance) {
                const diff = currentBal - this.lastWalletBalance;
                // If wallet balance increased outside of round result declared, admin approved points!
                if (data.round_status !== 'result_declared') {
                    const approvedToastKey = 'pts_approved_seen_' + this.roomId + '_' + currentBal;
                    if (!sessionStorage.getItem(approvedToastKey)) {
                        sessionStorage.setItem(approvedToastKey, 'true');
                        if (typeof window.showToast === 'function') {
                            window.showToast(`🎉 Points Request Approved! +${diff.toLocaleString()} pts added to your wallet.`, 'success');
                        }
                    }
                }
            }
            this.lastWalletBalance = currentBal;
            this.updateWalletBalance(data.wallet_balance);
        }
        if (data.cancellation_duration !== undefined) {
            this.cancellationDuration = data.cancellation_duration;
        }

        // 2. Round Info
        const roundNumEl = document.getElementById('round-number-display') || document.getElementById('session-id-display');
        if (roundNumEl) roundNumEl.textContent = `Session #${data.round_number}`;

        const roundStatusBadge = document.getElementById('round-status-badge');
        if (roundStatusBadge) {
            const statusMap = {
                'open': { text: 'ROUND IN PROGRESS', class: 'bg-slate-700 text-slate-200' },
                'betting_open': { text: data.remaining_seconds != null ? ('BETTING OPEN – ' + String(data.remaining_seconds).padStart(2, '0') + ' SEC') : 'BETTING OPEN', class: 'bg-emerald-600 text-white animate-pulse' },
                'betting_closed': { text: 'BETTING CLOSED', class: 'bg-amber-600 text-white' },
                'result_pending': { text: 'RESULT PENDING', class: 'bg-amber-700 text-white' },
                'result_declared': { text: 'RESULT DECLARED', class: 'bg-indigo-600 text-white' },
                'round_closed': { text: 'SESSION COMPLETE', class: 'bg-slate-800 text-slate-400' },
            };
            const s = statusMap[data.round_status] || { text: data.round_status, class: 'bg-slate-700 text-slate-200' };
            roundStatusBadge.textContent = s.text;
            roundStatusBadge.className = `px-2 py-0.5 text-[10px] font-bold rounded-full ${s.class}`;
        }

        const statusBanner = document.getElementById('player-game-status-banner');
        if (statusBanner) {
            if (data.round_status === 'betting_open') {
                statusBanner.textContent = 'BETTING OPEN – ' + String(data.remaining_seconds ?? 0).padStart(2, '0') + ' SEC';
            } else if (data.round_status === 'betting_closed') {
                statusBanner.textContent = 'BETTING CLOSED · Bets locked · Live game in progress';
            } else if (data.round_status === 'result_pending') {
                statusBanner.textContent = 'RESULT PENDING';
            } else if (data.round_status === 'result_declared') {
                statusBanner.textContent = 'RESULT DECLARED';
            } else if (data.round_status === 'open') {
                statusBanner.textContent = 'ROUND IN PROGRESS';
            } else {
                statusBanner.textContent = '';
            }
        }

        const countEl = document.getElementById('player-count-display');
        if (countEl && data.active_users !== undefined) {
            countEl.textContent = data.active_users;
        }

        if (this.currentStatus === 'betting_closed' && data.round_status === 'betting_open') {
            if (typeof window.showToast === 'function') {
                window.showToast('BETTING OPEN. 10 seconds remaining. You can place/add bets for this betting window.', 'success');
            }
        }
        if (this.currentStatus === 'betting_open' && data.round_status === 'betting_closed') {
            if (typeof window.showToast === 'function') {
                window.showToast('BETTING CLOSED. Your bets for this betting window are locked. The live game is still in progress. Please continue watching the live table.', 'info');
            }
        }
        this.currentStatus = data.round_status;

        const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        setTxt('sum-andar', Number(data.session_andar || 0).toLocaleString());
        setTxt('sum-bahar', Number(data.session_bahar || 0).toLocaleString());
        setTxt('sum-total', Number(data.session_total || 0).toLocaleString());
        setTxt('sum-limit', Number(data.session_limit || 1000000).toLocaleString());
        setTxt('sum-remaining', Number(data.session_remaining || 0).toLocaleString());

        // 3. First Card Render
        this.renderFirstCard(data.first_card);
        const hudRank = document.getElementById('hud-first-card-rank');
        if (hudRank && data.first_card) {
            const raw = String(data.first_card).split('_')[0].toUpperCase();
            const shortVal = raw === 'JACK' ? 'J' : (raw === 'QUEEN' ? 'Q' : (raw === 'KING' ? 'K' : (raw === 'ACE' ? 'A' : raw)));
            hudRank.textContent = shortVal;
        }
        // 4. Timer Handling
        const isBettingOpen = (data.round_status === 'betting_open');
        this.updateBettingControls(isBettingOpen);
        this.updateTimer(data.remaining_seconds, data.betting_duration || 30, isBettingOpen);

        // 5. Total volume odds bar
        this.updateVolumeBar(data.total_andar || 0, data.total_bahar || 0);

        // 6. User Bets List & Cancel buttons
        this.renderUserBets(data.user_bets || []);

        // 7. Dynamic Result Celebration (Strict de-duplication: exactly ONCE per round, never replay on resume/re-open)
        if (data.round_status === 'result_declared' && data.winning_side && data.winning_side !== 'none') {
            const roundId = data.round_id;
            const seenKey = 'f2w_round_result_seen_' + roundId;
            const alreadySeenLocal = localStorage.getItem(seenKey) === 'true';
            const alreadySeenSession = sessionStorage.getItem('f2w_round_result_seen_' + roundId) === 'true';
            const isAlreadyProcessed = (this.lastProcessedRoundId === roundId) || (this.resultModalShownForRound === roundId);

            if (isInitial) {
                // Guard: Re-opening or resuming the room when round is already settled must NEVER replay modal
                this.lastProcessedRoundId = roundId;
                this.resultModalShownForRound = roundId;
                sessionStorage.setItem('f2w_last_processed_round_' + this.roomId, roundId.toString());
                sessionStorage.setItem('f2w_round_result_seen_' + roundId, 'true');
                localStorage.setItem(seenKey, 'true');
            } else if (!alreadySeenLocal && !alreadySeenSession && !isAlreadyProcessed) {
                // Only trigger if this is an active transition in state while user is in the room
                if (this.currentStatus && this.currentStatus !== 'result_declared') {
                    // Mark processed immediately to block duplicate triggers from concurrent polling calls
                    this.lastProcessedRoundId = roundId;
                    this.resultModalShownForRound = roundId;
                    sessionStorage.setItem('f2w_last_processed_round_' + this.roomId, roundId.toString());
                    sessionStorage.setItem('f2w_round_result_seen_' + roundId, 'true');
                    localStorage.setItem(seenKey, 'true');

                    this.showResultOverlay(data);
                }
            }
        }

        // 8. Recent round badges
        if (data.recent_history) {
            this.renderRecentHistory(data.recent_history);
        }

        this.currentRoundId = data.round_id;
        this.currentStatus = data.round_status;
    }

    renderFirstCard(cardCode) {
        const container = document.getElementById('first-card-container');
        if (!container) return;

        if (!cardCode) {
            container.innerHTML = `
                <div class="card-empty">
                    <div class="text-center">
                        <span class="text-2xl block mb-1">🃏</span>
                        <span class="text-xs uppercase font-medium">Waiting Deal</span>
                    </div>
                </div>`;
            return;
        }

        // Parse card code e.g. "king_hearts", "2_spades", "ace_diamonds"
        const parts = cardCode.split('_');
        const val = parts[0] ? parts[0].toUpperCase() : '?';
        const suit = parts[1] || 'spades';

        const suitSymbols = {
            'spades': '♠',
            'hearts': '♥',
            'diamonds': '♦',
            'clubs': '♣'
        };

        const isRed = (suit === 'hearts' || suit === 'diamonds');
        const colorClass = isRed ? 'text-red-600' : 'text-slate-900';
        const symbol = suitSymbols[suit] || '♠';

        const shortVal = val === 'JACK' ? 'J' : (val === 'QUEEN' ? 'Q' : (val === 'KING' ? 'K' : (val === 'ACE' ? 'A' : val)));

        container.innerHTML = `
            <div class="card-slot ${colorClass}">
                <div class="flex justify-between items-start font-bold text-lg leading-none">
                    <span>${shortVal}</span>
                    <span class="text-base">${symbol}</span>
                </div>
                <div class="text-4xl text-center my-auto">${symbol}</div>
                <div class="flex justify-between items-end font-bold text-lg leading-none rotate-180">
                    <span>${shortVal}</span>
                    <span class="text-base">${symbol}</span>
                </div>
            </div>`;
    }

    updateTimer(seconds, totalDuration, isBettingOpen) {
        const timerText = document.getElementById('betting-timer-seconds');
        const timerBar = document.getElementById('betting-timer-progress');
        const timerRing = document.getElementById('betting-timer-ring');

        if (!isBettingOpen || seconds <= 0) {
            if (timerText) timerText.textContent = '00';
            if (timerBar) timerBar.style.width = '0%';
            if (timerRing) timerRing.style.strokeDashoffset = '283';
            return;
        }

        const formatted = seconds < 10 ? `0${seconds}` : `${seconds}`;
        if (timerText) timerText.textContent = formatted;

        const percentage = (seconds / totalDuration) * 100;
        if (timerBar) timerBar.style.width = `${percentage}%`;

        if (timerRing) {
            const circumference = 283; // 2 * PI * 45
            const offset = circumference - (percentage / 100) * circumference;
            timerRing.style.strokeDashoffset = offset.toString();
        }
    }

    updateBettingControls(isOpen) {
        const btnAndar = document.getElementById('btn-bet-andar');
        const btnBahar = document.getElementById('btn-bet-bahar');
        const overlay = document.getElementById('betting-disabled-overlay');

        if (btnAndar) btnAndar.disabled = !isOpen;
        if (btnBahar) btnBahar.disabled = !isOpen;

        if (overlay) {
            overlay.style.display = isOpen ? 'none' : 'flex';
        }
    }

    updateVolumeBar(andarVol, baharVol) {
        const total = andarVol + baharVol;
        const andarPct = total > 0 ? Math.round((andarVol / total) * 100) : 50;
        const baharPct = 100 - andarPct;

        const andarBar = document.getElementById('bar-andar-vol');
        const baharBar = document.getElementById('bar-bahar-vol');
        const andarText = document.getElementById('text-andar-vol');
        const baharText = document.getElementById('text-bahar-vol');

        if (andarBar) andarBar.style.width = `${andarPct}%`;
        if (baharBar) baharBar.style.width = `${baharPct}%`;
        if (andarText) andarText.textContent = `${andarVol.toLocaleString()} pts (${andarPct}%)`;
        if (baharText) baharText.textContent = `${baharVol.toLocaleString()} pts (${baharPct}%)`;
    }

    renderUserBets(bets) {
        const list = document.getElementById('my-bets-list');
        if (!list) return;

        if (bets.length === 0) {
            list.innerHTML = `<div class="text-center text-slate-500 py-4 text-xs">No bets placed in this round yet.</div>`;
            return;
        }

        list.innerHTML = bets.map(bet => {
            const isAndar = bet.selection === 'andar';
            const badgeClass = isAndar ? 'badge-andar' : 'badge-bahar';
            const statusColors = {
                'active': 'text-amber-400',
                'won': 'text-emerald-400 font-bold',
                'lost': 'text-slate-500 line-through',
                'cancelled': 'text-red-400 italic',
            };

            let cancelHtml = '';
            if (bet.can_cancel) {
                const remSec = (bet.remaining_cancel_seconds !== undefined && bet.remaining_cancel_seconds !== null) ? bet.remaining_cancel_seconds : this.cancellationDuration;
                cancelHtml = `
                    <button class="btn-cancel-bet px-2.5 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded font-semibold transition" 
                            data-bet-id="${bet.id}">
                        Cancel (${remSec}s)
                    </button>`;
            }

            return `
                <div class="flex items-center justify-between p-2.5 bg-slate-900/60 rounded-lg border border-slate-800 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded font-bold uppercase ${badgeClass}">${bet.selection}</span>
                        <span class="text-white font-semibold">${bet.amount.toLocaleString()} pts</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="${statusColors[bet.status] || 'text-slate-300'} uppercase">${bet.status}</span>
                        ${cancelHtml}
                    </div>
                </div>
            `;
        }).join('');

        // Bind cancel buttons
        list.querySelectorAll('.btn-cancel-bet').forEach(btn => {
            btn.onclick = () => this.cancelBet(btn.dataset.betId);
        });
    }

    renderRecentHistory(rounds) {
        const container = document.getElementById('recent-rounds-pills');
        if (!container) return;

        container.innerHTML = rounds.map(r => {
            const isAndar = r.winning_side === 'andar';
            const bgClass = isAndar ? 'bg-indigo-600 text-white' : 'bg-red-600 text-white';
            const label = isAndar ? 'A' : (r.winning_side === 'bahar' ? 'B' : '-');
            return `<div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shadow ${bgClass}" title="Round #${r.round_number}: ${r.winning_side.toUpperCase()}">${label}</div>`;
        }).join('');
    }

    async placeBet(selection, options = {}) {
        try {
            const bodyPayload = {
                selection: selection,
            };
            if (selection === 'both') {
                bodyPayload.andar_amount = options.andar_amount;
                bodyPayload.bahar_amount = options.bahar_amount;
            } else {
                bodyPayload.amount = options.amount || this.selectedChip;
            }

            const res = await fetch(this.betUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(bodyPayload)
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                window.showToast(data.message || 'Failed to place bet.', 'error');
                return false;
            }

            window.showToast(data.message, 'success');
            if (typeof window.onBetPlacedSuccess === 'function') {
                window.onBetPlacedSuccess(data);
            }
            this.fetchState();
            return true;
        } catch (err) {
            console.error('Bet error:', err);
            window.showToast('Network error while placing bet.', 'error');
            return false;
        }
    }

    async cancelBet(betId) {
        try {
            const url = `${this.cancelUrlBase}/${betId}/cancel`;
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                }
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                window.showToast(data.message || 'Cannot cancel bet.', 'error');
                return;
            }

            if (data.wallet_balance !== undefined) {
                this.updateWalletBalance(data.wallet_balance);
            }
            window.showToast(data.message || 'Bet cancelled successfully', 'success');
            if (typeof window.onBetCancelledSuccess === 'function') {
                window.onBetCancelledSuccess(data);
            }
            this.fetchState();
        } catch (err) {
            console.error('Cancel error:', err);
            window.showToast('Network error cancelling bet.', 'error');
        }
    }

    showResultOverlay(data) {
        // Prevent duplicate modal instances in DOM
        let modal = document.getElementById('round-result-modal');
        if (modal) {
            return; // Modal is already rendered and active
        }

        modal = document.createElement('div');
        modal.id = 'round-result-modal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md';

        const viewportContainer = document.getElementById('game-main-viewport') || document.body;
        viewportContainer.appendChild(modal);

        const winningSide = data.winning_side;
        const userBets = data.user_bets || [];
        const winningBets = userBets.filter(b => b.selection === winningSide && b.status !== 'cancelled');
        const losingBets = userBets.filter(b => b.selection !== winningSide && b.status !== 'cancelled');
        const hasWon = winningBets.length > 0;
        const totalStakeWin = winningBets.reduce((acc, b) => acc + Number(b.amount || 0), 0);
        const totalProfit = winningBets.reduce((acc, b) => acc + Number(b.profit_amount || 0), 0);
        const totalReturn = winningBets.reduce((acc, b) => acc + Number(b.payout_amount || (b.amount * 2) || 0), 0);
        const totalLost = losingBets.reduce((acc, b) => acc + Number(b.amount || 0), 0);
        const payoutLabel = data.payout_label || ((Number(data.payout_mode) === 25) ? '25% Profit' : '100% Profit');

        const isAndar = winningSide === 'andar';
        const winTitle = isAndar ? 'ROYAL ANDAR WON!' : 'ROYAL BAHAR WON!';
        const headerColor = isAndar ? 'from-indigo-600 to-indigo-900 border-indigo-400' : 'from-red-600 to-red-900 border-red-400';

        let userResultHtml = '';
        if (userBets.length > 0) {
            if (hasWon) {
                userResultHtml = `
                    <div class="p-4 bg-emerald-950/80 border border-emerald-500 rounded-xl my-4 text-center">
                        <span class="text-3xl block mb-1">🎉</span>
                        <h4 class="text-emerald-300 font-black text-xl uppercase tracking-wider">YOU WON</h4>
                        <div class="space-y-1.5 text-xs text-emerald-100 mt-3 text-left bg-black/40 p-3 rounded-lg border border-emerald-500/30">
                            <div class="flex justify-between"><span>Winning Side:</span> <strong class="text-white uppercase">${winningSide.toUpperCase()}</strong></div>
                            <div class="flex justify-between"><span>User's Total Bet:</span> <strong class="text-white">${totalStakeWin.toLocaleString()} Points</strong></div>
                            <div class="flex justify-between"><span>Payout Mode:</span> <strong class="text-emerald-300 font-bold">${payoutLabel}</strong></div>
                            <div class="flex justify-between"><span>Profit Points:</span> <strong class="text-emerald-400 font-bold">+${totalProfit.toLocaleString()} Points</strong></div>
                            <div class="flex justify-between border-t border-emerald-500/40 pt-1 text-sm font-black text-yellow-300">
                                <span>Total Return Points:</span>
                                <span>${totalReturn.toLocaleString()} Points</span>
                            </div>
                            <div class="flex justify-between border-t border-emerald-500/20 pt-1 text-[11px] text-slate-300">
                                <span>Updated Wallet Balance:</span>
                                <strong class="text-white">${Math.floor(Number(data.wallet_balance)).toLocaleString()} Points</strong>
                            </div>
                            <div class="flex justify-between text-[10px] text-slate-400">
                                <span>Session ID: #${data.round_number}</span>
                                <span>Round ID: ${data.round_id}</span>
                            </div>
                        </div>
                    </div>`;
            } else {
                userResultHtml = `
                    <div class="p-4 bg-slate-900/90 border border-red-700/60 rounded-xl my-4 text-center">
                        <span class="text-2xl block mb-1">💔</span>
                        <h4 class="text-slate-200 font-black text-lg uppercase tracking-wider">BETTER LUCK NEXT TIME</h4>
                        <div class="space-y-1.5 text-xs text-slate-300 mt-3 text-left bg-black/50 p-3 rounded-lg border border-white/10">
                            <div class="flex justify-between"><span>Winning Side:</span> <strong class="text-white uppercase">${winningSide.toUpperCase()}</strong></div>
                            <div class="flex justify-between"><span>Your Bet:</span> <strong class="text-white">${totalLost.toLocaleString()} Points</strong></div>
                            <div class="flex justify-between"><span class="text-red-400 font-bold">Result:</span> <strong class="text-red-400 font-bold">LOST</strong></div>
                            <div class="flex justify-between"><span class="text-red-300">Points Lost:</span> <strong class="text-red-300 font-mono">${totalLost.toLocaleString()} Points</strong></div>
                            <div class="flex justify-between border-t border-slate-700 pt-1 text-[11px] text-slate-400">
                                <span>Updated Wallet Balance:</span>
                                <strong class="text-white">${Math.floor(Number(data.wallet_balance)).toLocaleString()} Points</strong>
                            </div>
                            <div class="flex justify-between text-[10px] text-slate-500">
                                <span>Session ID: #${data.round_number}</span>
                                <span>Round ID: ${data.round_id}</span>
                            </div>
                        </div>
                    </div>`;
            }
        } else {
            userResultHtml = `
                <div class="p-3 bg-slate-900/60 rounded-lg my-4 text-center text-xs text-slate-400">
                    You did not place any bets in this round.
                </div>`;
        }

        // Persist seen state immediately so it will never show multiple times
        const seenKey = 'f2w_round_result_seen_' + data.round_id;
        localStorage.setItem(seenKey, 'true');
        sessionStorage.setItem(seenKey, 'true');

        modal.innerHTML = `
            <div class="glass-panel max-w-md w-full p-6 text-center border-2 ${headerColor} shadow-2xl animate-bounce-short">
                <span class="text-xs uppercase tracking-widest text-slate-400">Session #${data.round_number} · ID ${data.session_id || data.round_id}</span>
                <h2 class="text-2xl md:text-3xl font-royal font-black text-white mt-1 mb-2">${winTitle}</h2>
                <div class="inline-block px-4 py-1.5 rounded-full font-bold text-sm uppercase ${isAndar ? 'badge-andar' : 'badge-bahar'} mb-2">
                    Winner: ${winningSide.toUpperCase()}
                </div>
                ${userResultHtml}
                <div class="grid grid-cols-2 gap-2 mt-4">
                    <button type="button" onclick="GameEngine.dismissResultModal(${data.round_id}); window.location.reload();" class="py-2.5 px-3 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs uppercase font-black rounded-xl shadow transition cursor-pointer">
                        NEXT SESSION
                    </button>
                    <button type="button" onclick="GameEngine.dismissResultModal(${data.round_id})" class="py-2.5 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs uppercase font-bold rounded-xl border border-slate-700 transition cursor-pointer">
                        Continue Watching
                    </button>
                </div>
            </div>
        `;

        // Also dismiss on backdrop click
        modal.onclick = (e) => {
            if (e.target === modal) {
                GameEngine.dismissResultModal(data.round_id);
            }
        };
    }

    static dismissResultModal(roundId) {
        if (roundId) {
            localStorage.setItem('f2w_round_result_seen_' + roundId, 'true');
            sessionStorage.setItem('f2w_round_result_seen_' + roundId, 'true');
        }
        const modal = document.getElementById('round-result-modal');
        if (modal) {
            modal.remove();
        }
    }
}

window.GameEngine = GameEngine;
