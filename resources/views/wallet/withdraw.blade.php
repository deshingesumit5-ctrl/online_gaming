@extends('layouts.app')

@section('title', 'Points Withdrawal - Fun 2 Win')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left 5 cols: Request Form -->
        <div class="lg:col-span-5">
            <div class="glass-panel p-6 border-amber-500/20">
                <span class="text-[10px] font-bold uppercase tracking-widest text-amber-400 block mb-1">Points Payout</span>
                <h2 class="text-xl font-bold font-royal text-white mb-2">Request Withdrawal</h2>
                <p class="text-xs text-slate-400 mb-6">Submit points for settlement directly to your registered bank account or UPI ID.</p>

                <!-- Balance Pill -->
                <div class="p-4 rounded-xl bg-slate-900/90 border border-amber-500/40 flex items-center justify-between mb-6">
                    <span class="text-xs text-slate-400 font-semibold uppercase">Withdrawable Balance:</span>
                    <span class="text-xl font-black text-amber-300">{{ number_format($user->wallet_balance, 0) }} <small class="text-xs text-amber-500">PTS</small></span>
                </div>

                @if($errors->any())
                    <div class="p-3.5 rounded-xl bg-red-950/80 border border-red-500 text-red-200 text-xs mb-5">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('wallet.withdraw.post') }}" method="POST" class="needs-validation space-y-4" data-validate="true">
                    @csrf

                    <div>
                        <label for="amount" class="block text-xs font-semibold text-slate-300 uppercase mb-1">Withdrawal Amount (Min 100 PTS) *</label>
                        <input type="number" id="amount" name="amount" min="100" max="{{ (float)$user->wallet_balance }}" step="1"
                               value="{{ old('amount') }}" required placeholder="e.g. 500"
                               class="form-input-custom text-sm @error('amount') is-invalid @enderror">
                    </div>

                    <div>
                        <label for="settlement_details" class="block text-xs font-semibold text-slate-300 uppercase mb-1">Bank Account / UPI Details *</label>
                        <textarea id="settlement_details" name="settlement_details" rows="3" required
                                  placeholder="Bank Name, Account Holder Name, Account #, IFSC Code or UPI ID (e.g. username@upi)"
                                  class="form-input-custom text-sm @error('settlement_details') is-invalid @enderror">{{ old('settlement_details', $user->kyc_info) }}</textarea>
                    </div>

                    <button type="submit" class="btn-gold w-full py-3 text-xs uppercase tracking-wider font-bold mt-2" {{ (float)$user->wallet_balance < 100 ? 'disabled' : '' }}>
                        Submit Withdrawal Request
                    </button>
                </form>
            </div>
        </div>

        <!-- Right 7 cols: Withdrawal History Tracking -->
        <div class="lg:col-span-7">
            <div class="glass-panel p-6 border-slate-800">
                <h3 class="text-sm font-bold font-royal text-white mb-4 flex items-center gap-2">
                    <span>🏦</span> Withdrawal Status & History
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-3">Date</th>
                                <th class="py-3 px-3">Amount</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3">Remarks / Approver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($withdrawals as $w)
                                @php
                                    $statusBadges = [
                                        'pending' => 'bg-amber-950/80 text-amber-300 border-amber-600/50',
                                        'approved' => 'bg-emerald-950/80 text-emerald-300 border-emerald-500/50',
                                        'processed' => 'bg-emerald-950/80 text-emerald-300 border-emerald-500/50',
                                        'rejected' => 'bg-red-950/80 text-red-300 border-red-600/50',
                                    ];
                                @endphp
                                <tr class="hover:bg-slate-900/40">
                                    <td class="py-3 px-3 text-slate-400 whitespace-nowrap">{{ $w->created_at->format('d M, h:i A') }}</td>
                                    <td class="py-3 px-3 font-bold text-amber-300">{{ number_format($w->amount, 0) }} pts</td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $statusBadges[$w->status] ?? 'bg-slate-800 text-slate-300' }}">
                                            {{ in_array($w->status, ['approved', 'processed', 'settled']) ? 'APPROVED' : strtoupper($w->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-400">
                                        @if($w->status === 'rejected')
                                            <span class="text-red-400 text-[11px] block font-semibold">Rejected: {{ $w->rejection_remarks }}</span>
                                        @elseif(in_array($w->status, ['approved', 'processed', 'settled']))
                                            <span class="text-emerald-400 text-[11px] block font-semibold">Approved by Admin</span>
                                        @else
                                            <span class="text-slate-500 text-[11px]">Under Admin Review</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-500">No withdrawal requests yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($withdrawals->hasPages())
                    <div class="mt-4">
                        {{ $withdrawals->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
