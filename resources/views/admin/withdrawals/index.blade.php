@extends('layouts.admin')

@section('page-title', 'Withdrawal Settlements Management')

@section('content')
<div class="space-y-6">
    <!-- Header Filter Tabs & Search -->
    <div class="glass-panel p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.withdrawals.index', ['status' => 'all', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $status === 'all' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                All Requests ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'pending', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Pending ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'approved', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ in_array($status, ['approved', 'processed']) ? 'bg-emerald-600 text-white font-black shadow-lg shadow-emerald-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Approved ({{ $counts['approved'] }})
            </a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'rejected', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $status === 'rejected' ? 'bg-red-600 text-white font-black shadow-lg shadow-red-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Rejected ({{ $counts['rejected'] }})
            </a>
        </div>

        <!-- Search Box -->
        <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="flex items-center gap-2 w-full md:w-auto">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, name, username, mobile..."
                   class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs w-full md:w-64 focus:border-amber-400">
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition border border-slate-700">
                Search
            </button>
            @if($search)
                <a href="{{ route('admin.withdrawals.index', ['status' => $status]) }}" class="px-3 py-2 bg-red-950 hover:bg-red-900 text-red-300 text-xs font-bold rounded-xl transition border border-red-800/80">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Requests Table -->
    <div class="glass-panel overflow-hidden border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
        <!-- Mobile Card View (Shows ALL fields clearly without clipping) -->
        <div class="block md:hidden divide-y divide-slate-800/80">
            @forelse($withdrawals as $w)
                <div class="p-4 space-y-3 hover:bg-slate-800/30 transition">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-amber-400">
                            {{ $w->request_id ?: ('#WTH-' . str_pad($w->id, 5, '0', STR_PAD_LEFT)) }}
                        </span>
                        <x-status-badge :status="$w->status" />
                    </div>

                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-slate-100 text-sm">{{ $w->user->name ?? 'User' }}</div>
                            <span class="text-amber-400 font-mono text-xs">{{ '@' . ($w->user->username ?? '') }} &bull; #ID: {{ $w->user_id }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block uppercase">Requested</span>
                            <span class="font-black text-sm text-red-400 font-mono">-{{ number_format($w->amount_requested ?: $w->amount, 0) }} PTS</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-900/70 p-3 rounded-xl border border-slate-800/70">
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase">Current Wallet</span>
                            <span class="font-mono font-bold text-amber-300">{{ number_format($w->user->wallet_balance ?? 0, 0) }} PTS</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase">Date & Time</span>
                            <span class="text-slate-300 text-[11px]">{{ $w->created_at->format('d M, h:i A') }}</span>
                        </div>
                        <div class="col-span-2 pt-1.5 border-t border-slate-800/70">
                            <span class="text-[10px] text-slate-500 block uppercase mb-0.5">Settlement Details</span>
                            <div class="p-2 bg-slate-950 rounded-lg font-mono text-[11px] border border-slate-800 break-words select-all text-slate-300">
                                {{ $w->settlement_details }}
                            </div>
                        </div>
                        @if($w->status === 'rejected' && ($w->rejection_remark || $w->rejection_remarks))
                        <div class="col-span-2 pt-1 border-t border-slate-800/70 text-red-400 text-[11px]">
                            <span class="text-[10px] text-red-500 block uppercase">Rejection Reason</span>
                            {{ $w->rejection_remark ?: $w->rejection_remarks }}
                        </div>
                        @endif
                    </div>

                    @if($w->status === 'pending')
                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" 
                                onclick="openWithdrawalApproveModal('{{ $w->id }}', '{{ $w->request_id ?: ('#WTH-' . str_pad($w->id, 5, '0', STR_PAD_LEFT)) }}', '{{ $w->user->username ?? '' }}', '{{ number_format($w->amount_requested ?: $w->amount) }}')"
                                class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition shadow-sm text-center">
                            ✓ Approve
                        </button>
                        <button type="button" 
                                onclick="openWithdrawalRejectModal('{{ $w->id }}', '{{ $w->request_id }}', '{{ $w->user->username ?? '' }}', '{{ number_format($w->amount_requested ?: $w->amount) }}')"
                                class="py-2 px-4 bg-red-700 hover:bg-red-600 text-white rounded-xl text-xs font-bold transition text-center">
                            Reject
                        </button>
                    </div>
                    @else
                    <div class="text-right text-xs {{ in_array($w->status, ['approved', 'processed', 'settled']) ? 'text-emerald-400' : 'text-slate-500' }} font-semibold">
                        {{ in_array($w->status, ['approved', 'processed', 'settled']) ? '✓ Approved' : ucfirst($w->status) }}
                    </div>
                    @endif
                </div>
            @empty
                <div class="py-10 text-center text-slate-500 text-xs">
                    No withdrawal requests found.
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#090e1a]/95 text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                    <tr>
                        <th class="py-4 px-4 font-bold">User ID</th>
                        <th class="py-4 px-4 font-bold">Request ID & Date</th>
                        <th class="py-4 px-4 font-bold">Player</th>
                        <th class="py-4 px-4 font-bold">Current Wallet</th>
                        <th class="py-4 px-4 font-bold text-right">Requested Points</th>
                        <th class="py-4 px-4 font-bold">Settlement Details (Bank / UPI)</th>
                        <th class="py-4 px-4 font-bold">Status</th>
                        <th class="py-4 px-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($withdrawals as $w)
                        <tr class="hover:bg-slate-850/40 transition">
                            <td class="py-4 px-4 font-mono font-bold text-amber-400">
                                #{{ $w->user_id }}
                            </td>
                            <td class="py-4 px-4 font-medium text-slate-300">
                                <span class="font-mono font-bold block text-amber-400">
                                    {{ $w->request_id ?: ('#WTH-' . str_pad($w->id, 5, '0', STR_PAD_LEFT)) }}
                                </span>
                                <span class="text-[10px] text-slate-500">{{ $w->created_at->format('d M Y, h:i A') }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-100 text-sm">{{ $w->user->name ?? 'User' }}</div>
                                <span class="text-slate-400 font-mono text-[11px]">{{ '@' . ($w->user->username ?? '') }}</span>
                            </td>
                            <td class="py-4 px-4 font-black text-amber-300 text-sm whitespace-nowrap">
                                {{ number_format($w->user->wallet_balance ?? 0, 0) }} PTS
                            </td>
                            <td class="py-4 px-4 font-black text-red-400 text-sm text-right whitespace-nowrap">
                                -{{ number_format($w->amount_requested ?: $w->amount, 0) }} PTS
                            </td>
                            <td class="py-4 px-4 text-slate-300 max-w-xs">
                                <div class="p-2 bg-slate-950 rounded-lg font-mono text-[11px] border border-slate-800 select-all">
                                    {{ $w->settlement_details }}
                                </div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <x-status-badge :status="$w->status" />
                                @if($w->status === 'rejected' && ($w->rejection_remark || $w->rejection_remarks))
                                    <p class="text-[10px] text-red-400 mt-1 max-w-[150px] truncate" title="{{ $w->rejection_remark ?: $w->rejection_remarks }}">
                                        Reason: {{ $w->rejection_remark ?: $w->rejection_remarks }}
                                    </p>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                @if($w->status === 'pending')
                                    <div class="flex items-center justify-end gap-1.5 flex-nowrap">
                                        <!-- Approve Button -->
                                        <button type="button" 
                                                onclick="openWithdrawalApproveModal('{{ $w->id }}', '{{ $w->request_id ?: ('#WTH-' . str_pad($w->id, 5, '0', STR_PAD_LEFT)) }}', '{{ $w->user->username ?? '' }}', '{{ number_format($w->amount_requested ?: $w->amount) }}')"
                                                class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[11px] font-bold transition shadow-sm flex items-center gap-1 whitespace-nowrap cursor-pointer">
                                            <span>✓</span> Approve
                                        </button>

                                        <!-- Reject Button -->
                                        <button type="button" 
                                                onclick="openWithdrawalRejectModal('{{ $w->id }}', '{{ $w->request_id }}', '{{ $w->user->username ?? '' }}', '{{ number_format($w->amount_requested ?: $w->amount) }}')"
                                                class="px-2.5 py-1.5 bg-red-700 hover:bg-red-600 text-white rounded-lg text-[11px] font-bold transition shadow-sm flex items-center gap-1 whitespace-nowrap cursor-pointer">
                                            Reject
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs {{ in_array($w->status, ['approved', 'processed', 'settled']) ? 'text-emerald-400' : 'text-slate-500' }} font-semibold">
                                        {{ in_array($w->status, ['approved', 'processed', 'settled']) ? '✓ Approved' : ucfirst($w->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                No withdrawal requests found matching this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
            <div class="p-4 border-t border-slate-800 bg-[#090e1a]/60">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Centered Square Approve Modal in Center of Laptop -->
<div id="withdrawalApproveModal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4" style="background: rgba(0,0,0,0.65); backdrop-filter: blur(5px);">
    <div class="relative flex flex-col items-center justify-between p-6 sm:p-7 rounded-3xl shadow-2xl border transition-all"
         style="width: 360px; height: 360px; max-width: 92vw; max-height: 92vw; animation: alertPopIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
                background: radial-gradient(circle at 50% 20%, #064e3b 0%, #022c22 60%, #061814 100%); border-color: #10b981; box-shadow: 0 0 50px rgba(16,185,129,0.35);">
        
        {{-- Close X Button at Top-Right --}}
        <button type="button" onclick="closeWithdrawalApproveModal()" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-lg font-bold transition border border-white/20 hover:scale-110 active:scale-95" title="Close (X)">
            ✕
        </button>

        {{-- Icon --}}
        <div class="mt-2">
            <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 border-2 border-emerald-400 flex items-center justify-center text-emerald-400 text-3xl font-black shadow-lg shadow-emerald-500/30">
                ✓
            </div>
        </div>

        {{-- Title & Body --}}
        <div class="text-center px-2 my-2 flex flex-col items-center justify-center flex-grow">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-300 mb-1.5 font-royal">
                Confirm Payout Approval
            </h3>
            <p id="withdrawalApproveModalText" class="text-white text-xs sm:text-sm font-semibold leading-relaxed">
                Confirm payout approval? This will atomically deduct PTS from wallet.
            </p>
        </div>

        {{-- Actions Form (Cancel + OK) --}}
        <form id="withdrawalApproveModalForm" method="POST" action="" class="w-full flex gap-3">
            @csrf
            <button type="button" onclick="closeWithdrawalApproveModal()" class="flex-1 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-300 bg-slate-800 hover:bg-slate-700 transition">
                Cancel
            </button>
            <button type="submit" class="flex-1 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-500 transition shadow-lg shadow-emerald-600/40 hover:brightness-110 active:scale-95">
                OK
            </button>
        </form>
    </div>
</div>

<!-- Reject Withdrawal Modal -->
<div id="withdrawalRejectModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-[#0f172a] border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                <span>⚠️</span> Reject Withdrawal Request
            </h3>
            <button type="button" onclick="closeWithdrawalRejectModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <p class="text-xs text-slate-300" id="withdrawalRejectModalText">
            Are you sure you want to reject this withdrawal request?
        </p>

        <form id="withdrawalRejectModalForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label for="withdrawalRejectionRemark" class="block text-xs font-semibold text-slate-300 uppercase mb-1">
                    Rejection Reason (Required) *
                </label>
                <textarea id="withdrawalRejectionRemark" 
                          name="rejection_remark" 
                          rows="3" 
                          required
                          class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs"
                          placeholder="e.g. Invalid bank account details / IFSC code incorrect / Suspicious betting activity">Bank settlement details could not be verified.</textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeWithdrawalRejectModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-semibold transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-xs font-bold transition">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes alertPopIn {
        from { opacity: 0; transform: scale(0.85); }
        to   { opacity: 1; transform: scale(1); }
    }
</style>

@push('scripts')
<script>
    function openWithdrawalApproveModal(id, reqId, username, points) {
        const modal = document.getElementById('withdrawalApproveModal');
        const form = document.getElementById('withdrawalApproveModalForm');
        const text = document.getElementById('withdrawalApproveModalText');

        form.action = `/admin/withdrawals/${id}/process`;
        text.innerHTML = `Confirm payout approval? This will atomically deduct <span class="text-amber-400 font-bold font-mono">${points} PTS</span> from <span class="text-emerald-300 font-bold">@${username}</span>'s wallet.`;
        modal.classList.remove('hidden');
    }

    function closeWithdrawalApproveModal() {
        document.getElementById('withdrawalApproveModal').classList.add('hidden');
    }

    document.getElementById('withdrawalApproveModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeWithdrawalApproveModal();
    });

    function openWithdrawalRejectModal(id, reqId, username, points) {
        const modal = document.getElementById('withdrawalRejectModal');
        const form = document.getElementById('withdrawalRejectModalForm');
        const text = document.getElementById('withdrawalRejectModalText');

        form.action = `/admin/withdrawals/${id}/reject`;
        text.innerText = `Reject withdrawal request #${reqId} for ${points} PTS from @${username}?`;
        modal.classList.remove('hidden');
    }

    function closeWithdrawalRejectModal() {
        document.getElementById('withdrawalRejectModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
