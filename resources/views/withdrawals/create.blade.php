@extends('layouts.app')

@section('title', 'Request Withdrawal - Fun 2 Win')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="glass-panel p-6 sm:p-7 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 border-amber-500/20 bg-[#0d1526]/90 backdrop-blur rounded-2xl">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-amber-400 block mb-1">Financial Settlement</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-royal text-white">Request Withdrawal</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Cash out your accumulated winnings and points to your Bank Account or UPI.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('withdrawals.index') }}" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-bold uppercase tracking-wider transition">
                Withdrawal History
            </a>
            <a href="{{ route('wallet.transactions') }}" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-500/30 rounded-xl text-xs font-bold uppercase tracking-wider transition">
                Points
            </a>
        </div>
    </div>

    <!-- Balance Status Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Balance -->
        <div class="p-5 rounded-2xl bg-[#0c1324]/85 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1 tracking-wider">Total Balance</span>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-amber-300">{{ number_format($user->wallet_balance, 0) }}</span>
                <span class="text-xs font-bold text-amber-500">PTS</span>
            </div>
        </div>

        <!-- Pending In Requests -->
        <div class="p-5 rounded-2xl bg-[#0c1324]/85 border border-slate-800">
            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1 tracking-wider">Pending Withdrawals</span>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-slate-300">{{ number_format($user->pending_withdrawal_amount, 0) }}</span>
                <span class="text-xs font-bold text-slate-500">PTS</span>
            </div>
        </div>

        <!-- Available to Withdraw -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 via-[#0c1324] to-amber-950/40 border border-amber-500/40 shadow-lg shadow-amber-500/10">
            <span class="text-[10px] uppercase font-bold text-amber-400 block mb-1 tracking-wider">Available For Withdrawal</span>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-emerald-400" id="availablePointsDisplay">{{ number_format($user->available_balance, 0) }}</span>
                <span class="text-xs font-bold text-emerald-500">PTS</span>
            </div>
        </div>
    </div>

    <!-- Withdrawal Form -->
    <div class="glass-panel p-6 sm:p-8 border-slate-800 rounded-2xl bg-[#0c1324]/85 shadow-xl">
        <form method="POST" action="{{ route('withdrawals.store') }}" class="space-y-6" id="withdrawalForm">
            @csrf

            <!-- Amount Input -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="withdrawalAmount" class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Withdrawal Amount <span class="text-amber-400">*</span>
                    </label>
                    <span class="text-[11px] text-slate-400">
                        Min: 100 PTS &bull; Max Available: <strong class="text-amber-300">{{ number_format($user->available_balance, 0) }} PTS</strong>
                    </span>
                </div>

                <div class="relative">
                    <input type="number" 
                           id="withdrawalAmount" 
                           name="amount" 
                           value="{{ old('amount', min(1000, $user->available_balance)) }}" 
                           min="100" 
                           max="{{ $user->available_balance }}" 
                           step="10" 
                           required
                           class="w-full px-4 py-3.5 rounded-xl bg-slate-950 border border-slate-700 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 text-white font-mono font-black text-xl transition"
                           placeholder="e.g. 1000">
                    <span class="absolute right-4 top-4 text-xs font-black text-amber-400">PTS</span>
                </div>
                @error('amount')
                    <p class="text-xs text-red-400 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Quick Chips -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Quick Amounts
                </label>
                <div class="flex flex-wrap gap-2.5">
                    @foreach([500, 1000, 2000, 5000, 10000] as $chip)
                        @if($chip <= $user->available_balance)
                            <button type="button" 
                                    onclick="setWithdrawalAmount({{ $chip }})"
                                    class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-amber-500/50 text-slate-300 hover:text-amber-300 text-xs font-mono font-bold transition">
                                {{ number_format($chip) }} PTS
                            </button>
                        @endif
                    @endforeach
                    @if($user->available_balance > 0)
                        <button type="button" 
                                onclick="setWithdrawalAmount({{ $user->available_balance }})"
                                class="px-4 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-mono font-black transition">
                            MAX ({{ number_format($user->available_balance) }})
                        </button>
                    @endif
                </div>
            </div>

            <!-- Settlement Details -->
            <div>
                <label for="settlementDetails" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Settlement Details (Bank Account / UPI) <span class="text-amber-400">*</span>
                </label>
                <textarea id="settlementDetails" 
                          name="settlement_details" 
                          rows="3" 
                          required
                          class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 text-white text-xs transition"
                          placeholder="e.g. Bank: State Bank of India | A/C: 1234567890 | IFSC: SBIN0001234 | Name: John Doe&#10;OR UPI ID: username@upi">{{ old('settlement_details', $user->kyc_info) }}</textarea>
                <p class="text-[11px] text-slate-500 mt-1">Make sure account details are accurate to avoid delays in payout settlement.</p>
                @error('settlement_details')
                    <p class="text-xs text-red-400 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button with Double-Submit Prevention -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                <span class="text-[11px] text-slate-500">
                    Withdrawal requests are processed by the accounts team within 15-60 minutes.
                </span>
                <button type="submit" 
                        id="submitWithdrawalBtn"
                        @if($user->available_balance < 100) disabled @endif
                        class="px-8 py-3.5 bg-gradient-to-r from-amber-500 via-yellow-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-widest rounded-xl transition shadow-lg shadow-amber-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function setWithdrawalAmount(val) {
        const input = document.getElementById('withdrawalAmount');
        input.value = val;
        input.focus();
    }

    document.getElementById('withdrawalForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitWithdrawalBtn');
        btn.disabled = true;
        btn.innerHTML = 'Submitting Request...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    });
</script>
@endpush
@endsection
