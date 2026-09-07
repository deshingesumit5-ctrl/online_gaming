@extends('layouts.app')

@section('title', 'My Withdrawals - Fun 2 Win')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="glass-panel p-6 sm:p-7 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 border-amber-500/20 bg-[#0d1526]/90 backdrop-blur rounded-2xl">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-amber-400 block mb-1">Settlement History</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-royal text-white">My Withdrawal Requests</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Track the status of your points withdrawal requests and payout settlements.</p>
        </div>

        <div class="flex items-center gap-3">
            <x-wallet-balance-pill :balance="$user->wallet_balance" />
            <a href="{{ route('withdrawals.create') }}" class="btn-gold text-xs uppercase tracking-wider py-3">
                Request Withdrawal
            </a>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="glass-panel overflow-hidden border-slate-800 rounded-2xl bg-[#0c1324]/80 shadow-xl">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">
                All Withdrawal Requests ({{ $withdrawals->total() }})
            </span>
            <span class="text-xs text-slate-500 font-mono">Most Recent First</span>
        </div>

        <!-- Mobile Card View (Shows ALL fields clearly without clipping) -->
        <div class="block md:hidden divide-y divide-slate-800/80">
            @forelse($withdrawals as $w)
                <div class="p-4 space-y-3 hover:bg-slate-800/30 transition">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-slate-300">
                            {{ $w->request_id ?: ('WTH-' . str_pad($w->id, 5, '0', STR_PAD_LEFT)) }}
                        </span>
                        <x-status-badge :status="$w->status" />
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-slate-400">Requested Amount</span>
                        <span class="font-black text-sm text-red-400 font-mono">
                            -{{ number_format($w->amount_requested ?: $w->amount, 0) }} PTS
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-2 text-xs bg-slate-900/70 p-3 rounded-xl border border-slate-800/70">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-slate-500 uppercase">Date & Time</span>
                            <span class="text-slate-300">{{ $w->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="pt-1.5 border-t border-slate-800/70">
                            <span class="text-[10px] text-slate-500 block uppercase mb-0.5">Settlement Details</span>
                            <span class="text-slate-200 font-mono text-[11px] block break-words">{{ $w->settlement_details }}</span>
                        </div>
                        <div class="pt-1.5 border-t border-slate-800/70">
                            <span class="text-[10px] text-slate-500 block uppercase mb-0.5">Processed Note</span>
                            @if(in_array($w->status, ['processed', 'approved', 'settled']))
                                <span class="text-emerald-400 font-semibold">
                                    Approved {{ $w->processed_at ? $w->processed_at->format('d M Y') : '' }}
                                </span>
                            @elseif($w->status === 'rejected')
                                <span class="text-red-400 font-medium">
                                    Reason: {{ $w->rejection_remark ?: ($w->rejection_remarks ?: 'Rejected') }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">Pending Accounts Verification</span>
                            @endif
                        </div>
                    </div>
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
                        <th class="py-4 px-5 font-bold">Request ID</th>
                        <th class="py-4 px-5 font-bold">Requested At</th>
                        <th class="py-4 px-5 font-bold text-right">Amount</th>
                        <th class="py-4 px-5 font-bold">Status</th>
                        <th class="py-4 px-5 font-bold">Settlement Details</th>
                        <th class="py-4 px-5 font-bold">Processed Date / Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($withdrawals as $w)
                        <tr class="hover:bg-slate-850/40 transition">
                            <td class="py-4 px-5 font-mono font-medium text-slate-300">
                                {{ $w->request_id ?: ('WTH-' . str_pad($w->id, 5, '0', STR_PAD_LEFT)) }}
                            </td>
                            <td class="py-4 px-5 text-slate-400 whitespace-nowrap">
                                {{ $w->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-4 px-5 text-right font-black text-sm text-red-400 whitespace-nowrap">
                                -{{ number_format($w->amount_requested ?: $w->amount, 0) }} PTS
                            </td>
                            <td class="py-4 px-5 whitespace-nowrap">
                                <x-status-badge :status="$w->status" />
                            </td>
                            <td class="py-4 px-5 text-slate-300 max-w-xs truncate">
                                {{ $w->settlement_details }}
                            </td>
                            <td class="py-4 px-5 text-slate-400 max-w-xs">
                                @if(in_array($w->status, ['processed', 'approved', 'settled']))
                                    <span class="text-emerald-400 font-semibold">
                                        Approved {{ $w->processed_at ? $w->processed_at->format('d M Y') : '' }}
                                    </span>
                                @elseif($w->status === 'rejected')
                                    <span class="text-red-400 font-medium">
                                        Reason: {{ $w->rejection_remark ?: ($w->rejection_remarks ?: 'Rejected') }}
                                    </span>
                                @else
                                    <span class="text-slate-500 italic">Pending Accounts Verification</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="text-3xl">🏦</span>
                                    <span class="text-sm font-semibold">No withdrawal requests found.</span>
                                    <a href="{{ route('withdrawals.create') }}" class="text-xs text-amber-400 hover:underline">
                                        Submit your first withdrawal request
                                    </a>
                                </div>
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
@endsection
