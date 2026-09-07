@extends('layouts.admin')

@section('page-title', 'Points Management & Operator Wallet')

@section('content')
<div class="space-y-6">
    <!-- Header Filter Tabs & Search -->
    <div class="glass-panel p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.wallet.index', ['req_status' => 'all', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ ($reqStatus ?? 'all') === 'all' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                All Requests ({{ $allPointRequestsCount }})
            </a>
            <a href="{{ route('admin.wallet.index', ['req_status' => 'pending', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ ($reqStatus ?? '') === 'pending' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Pending ({{ $pendingRequestsCount }})
            </a>
            <a href="{{ route('admin.wallet.index', ['req_status' => 'approved', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ ($reqStatus ?? '') === 'approved' ? 'bg-emerald-600 text-white font-black shadow-lg shadow-emerald-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Approved ({{ $approvedRequestsCount }})
            </a>
            <a href="{{ route('admin.wallet.index', ['req_status' => 'rejected', 'search' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ ($reqStatus ?? '') === 'rejected' ? 'bg-red-600 text-white font-black shadow-lg shadow-red-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Rejected ({{ $rejectedRequestsCount }})
            </a>
        </div>

        <!-- Search Box -->
        <form method="GET" action="{{ route('admin.wallet.index') }}" class="flex items-center gap-2 w-full md:w-auto">
            <input type="hidden" name="req_status" value="{{ $reqStatus ?? 'all' }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, name, username, mobile..."
                   class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs w-full md:w-64 focus:border-amber-400">
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition border border-slate-700">
                Search
            </button>
            @if($search)
                <a href="{{ route('admin.wallet.index', ['req_status' => $reqStatus ?? 'all']) }}" class="px-3 py-2 bg-red-950 hover:bg-red-900 text-red-300 text-xs font-bold rounded-xl transition border border-red-800/80">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- 1. PLAYER POINT REQUESTS SECTION -->
    <div class="glass-panel overflow-hidden border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
        <!-- Mobile Card View (Shows ALL fields clearly without clipping) -->
        <div class="block md:hidden divide-y divide-slate-800/80">
            @forelse($pointRequests as $pReq)
                <div class="p-4 space-y-3 hover:bg-slate-800/30 transition">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-amber-400">#{{ $pReq->request_id }}</span>
                        <x-status-badge :status="$pReq->status" />
                    </div>

                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-slate-100 text-sm">{{ $pReq->user->name ?? 'User' }}</div>
                            <div class="text-xs text-amber-400 font-mono">{{ '@' . ($pReq->user->username ?? '') }} &bull; #ID: {{ $pReq->user_id }}</div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block uppercase">Requested</span>
                            <span class="font-black text-sm text-emerald-400 font-mono">+{{ number_format($pReq->points_requested, 0) }} PTS</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-900/70 p-3 rounded-xl border border-slate-800/70">
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase">Current Balance</span>
                            <span class="font-mono font-bold text-amber-300">{{ number_format($pReq->user->wallet_balance ?? 0, 0) }} PTS</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase">Requested At</span>
                            <span class="text-slate-300 text-[11px]">{{ $pReq->created_at->format('d M, h:i A') }}</span>
                        </div>
                        @if($pReq->remarks)
                        <div class="col-span-2 pt-1 border-t border-slate-800/70">
                            <span class="text-[10px] text-slate-500 block uppercase">User Note / Ref</span>
                            <span class="text-slate-300 text-[11px]">{{ $pReq->remarks }}</span>
                        </div>
                        @endif
                        @if($pReq->rejection_remark)
                        <div class="col-span-2 pt-1 border-t border-slate-800/70 text-red-400">
                            <span class="text-[10px] text-red-500 block uppercase">Rejection Reason</span>
                            <span class="text-[11px]">{{ $pReq->rejection_remark }}</span>
                        </div>
                        @endif
                    </div>

                    @if($pReq->status === 'pending')
                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" 
                                onclick="openApproveModal('{{ $pReq->id }}', '{{ $pReq->request_id }}', '{{ $pReq->user->username }}', '{{ number_format($pReq->points_requested) }}')"
                                class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition shadow-sm text-center">
                            ✓ Approve
                        </button>
                        <button type="button" 
                                onclick="openRejectModal('{{ $pReq->id }}', '{{ $pReq->request_id }}', '{{ $pReq->user->username }}', '{{ number_format($pReq->points_requested) }}')"
                                class="py-2 px-4 bg-red-700 hover:bg-red-600 text-white rounded-xl text-xs font-bold transition text-center">
                            Reject
                        </button>
                    </div>
                    @elseif($pReq->status === 'approved')
                    <div class="text-right text-xs text-emerald-400 font-semibold">
                        ✓ Approved & Credited
                    </div>
                    @else
                    <div class="text-right text-xs text-red-400 font-semibold">
                        ✕ Rejected by Admin
                    </div>
                    @endif
                </div>
            @empty
                <div class="py-10 text-center text-slate-500 text-xs">
                    No point requests found for this filter.
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#090e1a]/95 text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                    <tr>
                        <th class="py-4 px-4 font-bold">User ID</th>
                        <th class="py-4 px-4 font-bold">Request ID</th>
                        <th class="py-4 px-4 font-bold">Player</th>
                        <th class="py-4 px-4 font-bold">Current Balance</th>
                        <th class="py-4 px-4 font-bold text-right">Points Requested</th>
                        <th class="py-4 px-4 font-bold">User Note / Reference</th>
                        <th class="py-4 px-4 font-bold">Date & Time</th>
                        <th class="py-4 px-4 font-bold">Status</th>
                        <th class="py-4 px-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($pointRequests as $pReq)
                        <tr class="hover:bg-slate-850/40 transition">
                            <td class="py-4 px-4 font-mono font-bold text-amber-400">
                                #{{ $pReq->user_id }}
                            </td>
                            <td class="py-4 px-4 font-mono font-bold text-amber-400">
                                {{ $pReq->request_id }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-100 text-sm">{{ $pReq->user->name ?? 'User' }}</div>
                                <span class="text-slate-400 font-mono text-[11px]">{{ '@' . ($pReq->user->username ?? '') }}</span>
                            </td>
                            <td class="py-4 px-4 font-black text-amber-300 text-sm whitespace-nowrap">
                                {{ number_format($pReq->user->wallet_balance ?? 0, 0) }} PTS
                            </td>
                            <td class="py-4 px-4 text-right font-black text-sm text-emerald-400 whitespace-nowrap">
                                +{{ number_format($pReq->points_requested, 0) }} PTS
                            </td>
                            <td class="py-4 px-4 text-slate-300 max-w-xs">
                                {{ $pReq->remarks ?: '-' }}
                            </td>
                            <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                                {{ $pReq->created_at->format('d M, h:i A') }}
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <x-status-badge :status="$pReq->status" />
                            </td>
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                @if($pReq->status === 'pending')
                                <div class="flex items-center justify-end gap-1.5 flex-nowrap">
                                    <!-- Approve Button (Modal Trigger) -->
                                    <button type="button" 
                                            onclick="openApproveModal('{{ $pReq->id }}', '{{ $pReq->request_id }}', '{{ $pReq->user->username }}', '{{ number_format($pReq->points_requested) }}')"
                                            class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[11px] font-bold transition shadow-sm flex items-center gap-1 whitespace-nowrap cursor-pointer">
                                        <span>✓</span> Approve
                                    </button>

                                    <!-- Reject Button (Modal Trigger) -->
                                    <button type="button" 
                                            onclick="openRejectModal('{{ $pReq->id }}', '{{ $pReq->request_id }}', '{{ $pReq->user->username }}', '{{ number_format($pReq->points_requested) }}')"
                                            class="px-2.5 py-1.5 bg-red-700 hover:bg-red-600 text-white rounded-lg text-[11px] font-bold transition shadow-sm flex items-center gap-1 whitespace-nowrap cursor-pointer">
                                        Reject
                                    </button>
                                </div>
                                @elseif($pReq->status === 'approved')
                                    <span class="text-xs text-emerald-400 font-semibold inline-flex items-center gap-1">
                                        ✓ Credited
                                    </span>
                                @else
                                    <span class="text-xs text-red-400 font-semibold inline-flex items-center gap-1" title="{{ $pReq->rejection_remark }}">
                                        ✕ Rejected
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-500">
                                No point requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pointRequests->hasPages())
            <div class="p-3.5 border-t border-slate-800 bg-[#090e1a]/60">
                {{ $pointRequests->links() }}
            </div>
        @endif
    </div>

    <!-- 2. MANUAL ADJUSTMENT & GLOBAL AUDIT LEDGER -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left 5 cols: Manual Adjustment Form -->
        <div class="lg:col-span-5">
            <div class="glass-panel p-6 border-slate-800 rounded-2xl bg-[#0c1324]/85 shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl">💳</span>
                    <div>
                        <h2 class="text-base font-bold font-royal text-white">Manual Point Adjustment</h2>
                        <span class="text-xs text-slate-400">Add or deduct player wallet points with mandatory audit remarks</span>
                    </div>
                </div>

                @if(isset($errors) && $errors->any())
                    <div class="p-3 rounded-xl bg-red-950/80 border border-red-500 text-red-200 text-xs mb-4">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.wallet.adjust') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="user_id" class="block text-xs font-semibold text-slate-300 uppercase mb-1">Select Player *</label>
                        <select id="user_id" name="user_id" required class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                            <option value="">-- Choose Player --</option>
                            @foreach($players as $player)
                                <option value="{{ $player->id }}" {{ old('user_id') == $player->id ? 'selected' : '' }}>
                                    {{ $player->name }} ({{ '@' . $player->username }}) &bull; Bal: {{ number_format($player->wallet_balance, 0) }} pts
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Adjustment Type *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="action_type" value="credit" class="peer hidden" checked>
                                <div class="p-2.5 text-center rounded-xl bg-slate-900 border border-slate-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-950/40 text-xs font-bold transition text-emerald-400">
                                    + Add Points (Credit)
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="action_type" value="debit" class="peer hidden">
                                <div class="p-2.5 text-center rounded-xl bg-slate-900 border border-slate-700 peer-checked:border-red-500 peer-checked:bg-red-950/40 text-xs font-bold transition text-red-400">
                                    - Deduct Points (Debit)
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="amount" class="block text-xs font-semibold text-slate-300 uppercase mb-1">Amount (Points) *</label>
                        <input type="number" id="amount" name="amount" min="1" step="1" value="{{ old('amount') }}" required placeholder="e.g. 5000"
                               class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono font-bold text-sm">
                    </div>

                    <div>
                        <label for="remarks" class="block text-xs font-semibold text-slate-300 uppercase mb-1">Audit Remark (Mandatory) *</label>
                        <textarea id="remarks" name="remarks" rows="2" required placeholder="Specify why points are adjusted (e.g. Bank Deposit verified / Promo bonus / Dispute settlement)"
                                  class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">{{ old('remarks') }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-amber-500 via-yellow-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-amber-500/20">
                        Execute Adjustment
                    </button>
                </form>
            </div>
        </div>

        <!-- Right 7 cols: Audit Trail -->
        <div class="lg:col-span-7 space-y-4">
            <div class="glass-panel p-5 border-slate-800 rounded-2xl bg-[#0c1324]/85 shadow-xl">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                    <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                        <span>📜</span> System Ledger & Adjustment Audit
                    </h3>

                    <!-- Filter -->
                    <form method="GET" action="{{ route('admin.wallet.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search txn or player..." class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-xs text-slate-200">
                        <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-semibold">Filter</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Player</th>
                                <th class="py-2.5 px-3">Type</th>
                                <th class="py-2.5 px-3">Points</th>
                                <th class="py-2.5 px-3">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            @forelse($transactions as $txn)
                                @php
                                    $isCredit = $txn->amount > 0 || in_array($txn->type, ['points_added', 'winning_points_added', 'manual_credit', 'bet_refunded', 'bet_cancelled_refunded']);
                                @endphp
                                <tr class="hover:bg-slate-900/40 transition">
                                    <td class="py-2.5 px-3 text-slate-400 whitespace-nowrap">
                                        {{ $txn->created_at->format('M d, H:i') }}
                                    </td>
                                    <td class="py-2.5 px-3 font-semibold text-slate-200">
                                        {{ '@' . ($txn->user?->username ?? 'deleted') }}
                                    </td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <x-transaction-badge :type="$txn->type" />
                                    </td>
                                    <td class="py-2.5 px-3 font-bold whitespace-nowrap {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $isCredit ? '+' : '-' }}{{ number_format(abs($txn->amount), 0) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-300 max-w-xs truncate" title="{{ $txn->remarks }}">
                                        {{ $txn->remarks }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-500">No transactions recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                    <div class="pt-3 border-t border-slate-800">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approve Point Request Modal -->
<div id="approveModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-[#0f172a] border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                <span class="text-emerald-400">✓</span> Approve Point Request
            </h3>
            <button type="button" onclick="closeApproveModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <p class="text-xs text-slate-300" id="approveModalText">
            Confirm approving points request and crediting points to player wallet.
        </p>

        <form id="approveModalForm" method="POST" action="" class="space-y-4">
            @csrf
            <div class="p-3.5 rounded-xl bg-slate-900/90 border border-emerald-500/30 text-xs space-y-1 text-slate-300">
                <p>• The requested points will be <strong>atomically credited</strong> to the player's wallet.</p>
                <p>• A transaction entry of type <code>points_added</code> will be recorded in the financial ledger.</p>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeApproveModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-semibold transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition shadow-md">
                    Confirm & Credit Points
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Point Request Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-[#0f172a] border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                <span>⚠️</span> Reject Point Request
            </h3>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <p class="text-xs text-slate-300" id="rejectModalText">
            Are you sure you want to reject this points request?
        </p>

        <form id="rejectModalForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label for="rejectionRemark" class="block text-xs font-semibold text-slate-300 uppercase mb-1">
                    Rejection Reason / Remark
                </label>
                <textarea id="rejectionRemark" 
                          name="rejection_remark" 
                          rows="3" 
                          required
                          class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs"
                          placeholder="e.g. Payment not received / Invalid UTR / Incorrect details">Payment reference could not be verified.</textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-semibold transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-xs font-bold transition">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openApproveModal(id, reqId, username, points) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('approveModalForm');
        const text = document.getElementById('approveModalText');

        form.action = `/admin/wallet/point-requests/${id}/approve`;
        text.innerHTML = `Are you sure you want to approve request <strong>#${reqId}</strong> and credit <strong>${points} PTS</strong> to <strong>@${username}</strong>?`;
        modal.classList.remove('hidden');
    }

    function closeApproveModal() {
        document.getElementById('approveModal').classList.add('hidden');
    }

    function openRejectModal(id, reqId, username, points) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectModalForm');
        const text = document.getElementById('rejectModalText');

        form.action = `/admin/wallet/point-requests/${id}/reject`;
        text.innerText = `Reject request #${reqId} for ${points} PTS from @${username}?`;
        modal.classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
