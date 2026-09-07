@extends('layouts.app')

@section('title', 'Request Points - Fun 2 Win')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Request Form Card -->
    <div class="glass-panel p-6 sm:p-8 border-slate-800 rounded-2xl bg-[#0c1324]/85 shadow-xl">
        <form method="POST" action="{{ route('points.request.store') }}" class="space-y-6" id="pointRequestForm">
            @csrf

            <!-- Current Points Read-only Field -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                        Your Current Balance
                    </label>
                    <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-slate-900/90 border border-slate-800 text-slate-300 font-mono font-bold text-sm">
                        <span>💰</span>
                        <span class="text-amber-300">{{ number_format($user->wallet_balance, 0) }}</span> PTS
                        <span class="ml-auto text-[10px] text-emerald-400 font-sans uppercase font-extrabold bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-500/30">Active Wallet</span>
                    </div>
                </div>

                <div>
                    <label for="pointsInput" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                        Points to Request <span class="text-amber-400">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="pointsInput" 
                               name="points" 
                               value="{{ old('points', 1000) }}" 
                               min="10" 
                               max="10000000" 
                               step="10" 
                               required
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 text-white font-mono font-black text-lg transition"
                               placeholder="e.g. 1000">
                        <span class="absolute right-4 top-3.5 text-xs font-bold text-amber-400">PTS</span>
                    </div>
                    @error('points')
                        <p class="text-xs text-red-400 mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Quick Points Chips -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Quick Preset Amounts
                </label>
                <div class="flex flex-wrap gap-2.5">
                    @foreach([500, 1000, 2000, 5000, 10000, 25000] as $preset)
                        <button type="button" 
                                onclick="setPoints({{ $preset }})"
                                class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-amber-500/50 text-slate-300 hover:text-amber-300 text-xs font-mono font-bold transition">
                            +{{ number_format($preset) }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Reference / Remarks Note -->
            <div>
                <label for="remarks" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Payment Reference / Deposit Details / Note <span class="text-slate-500 font-normal">(Optional)</span>
                </label>
                <textarea id="remarks" 
                          name="remarks" 
                          rows="2" 
                          class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 text-white text-xs transition"
                          placeholder="e.g. Transferred via UPI Ref: 1234567890 or operator agent code">{{ old('remarks') }}</textarea>
                @error('remarks')
                    <p class="text-xs text-red-400 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                <span class="text-[11px] text-slate-500 flex items-center gap-1">
                    <span>🔒</span> All requests are logged and reviewed by the gaming operator.
                </span>
                <button type="submit" 
                        id="submitBtn"
                        class="px-8 py-3.5 bg-gradient-to-r from-amber-500 via-yellow-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-widest rounded-xl transition shadow-lg shadow-amber-500/20 transform hover:-translate-y-0.5 active:translate-y-0">
                    Submit Points Request
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    function setPoints(amount) {
        const input = document.getElementById('pointsInput');
        input.value = amount;
        input.focus();
    }

    document.getElementById('pointRequestForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = 'Submitting Request...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    });
</script>
@endpush
@endsection
