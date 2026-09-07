@extends('layouts.app')

@section('title', 'Points & History - Fun 2 Win')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card Matching Reference Screenshot -->
    <div class="glass-panel p-6 sm:p-7 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 border-amber-500/20 shadow-2xl bg-[#0d1526]/90 backdrop-blur rounded-2xl">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-[#f5a623] block mb-1">Financial Statement</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-royal text-white tracking-wide">Points & History</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Audit trail of all bets, winnings, refunds, withdrawals, and balance updates.</p>
        </div>

        <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto justify-start lg:justify-end">
            <!-- Current Balance Box -->
            <div class="bg-[#070b14]/90 border border-amber-500/40 px-5 py-3.5 rounded-xl shadow-inner min-w-[170px]">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-0.5">Current Balance</span>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-black text-amber-300 user-wallet-balance">
                        {{ number_format($user->wallet_balance, 0) }}
                    </span>
                    <span class="text-xs font-bold text-amber-500">PTS</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2.5">
                <a href="{{ route('points.request') }}" class="px-5 py-3 bg-gradient-to-r from-amber-500 via-yellow-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-amber-500/25 flex items-center gap-1.5 transform hover:-translate-y-0.5 active:translate-y-0">
                    <span>➕</span> Request Points
                </a>
            </div>
        </div>
    </div>

    <!-- Ledger Table Card -->
    <div class="glass-panel overflow-hidden border-slate-800/90 rounded-2xl shadow-xl bg-[#0c1324]/80">
        <!-- Table Filter / Count Bar -->
        <div class="px-5 py-3.5 border-b border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-300">Transaction Records</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-[11px] font-mono font-semibold">{{ $transactions->total() }} total</span>
            </div>
            @if(request('type'))
                <a href="{{ route('wallet.transactions') }}" class="text-amber-400 hover:underline">Clear Filter</a>
            @endif
        </div>

        <!-- Mobile Card View (Shows ALL fields clearly without clipping) -->
        <div class="block md:hidden divide-y divide-slate-800/80">
            @forelse($transactions as $txn)
                @php
                    $isCredit = $txn->amount > 0 || in_array($txn->type, ['points_added', 'winning_points_added', 'manual_credit', 'bet_refunded', 'bet_cancelled_refunded']);
                    $displayAmount = abs($txn->amount);
                @endphp
                <div class="p-4 space-y-3 hover:bg-slate-800/30 transition">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-slate-300">
                            {{ $txn->transaction_id ?: $txn->transaction_code }}
                        </span>
                        <x-transaction-badge :type="$txn->type" />
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-slate-400">Points</span>
                        <span class="font-black text-sm font-mono {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $isCredit ? '+' : '-' }}{{ number_format($displayAmount, 0) }} PTS
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-900/70 p-3 rounded-xl border border-slate-800/70">
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase">Updated Balance</span>
                            <span class="font-bold text-slate-200 font-mono">{{ number_format($txn->balance_after ?: $txn->updated_balance, 0) }} pts</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase">Date & Time</span>
                            <span class="text-slate-300 text-[11px]">{{ $txn->created_at->format('d M, h:i A') }}</span>
                        </div>
                        @if($txn->remarks)
                        <div class="col-span-2 pt-1.5 border-t border-slate-800/70">
                            <span class="text-[10px] text-slate-500 block uppercase mb-0.5">Remarks</span>
                            <span class="text-slate-300 text-[11px] break-words">{{ $txn->remarks }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-slate-500 text-xs">
                    No transactions recorded.
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#090e1a]/95 text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                    <tr>
                        <th class="py-4 px-5 font-bold">Transaction ID</th>
                        <th class="py-4 px-5 font-bold">Date & Time</th>
                        <th class="py-4 px-5 font-bold">Type</th>
                        <th class="py-4 px-5 font-bold">Remarks</th>
                        <th class="py-4 px-5 font-bold text-right">Points</th>
                        <th class="py-4 px-5 font-bold text-right">Updated Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($transactions as $txn)
                        @php
                            $isCredit = $txn->amount > 0 || in_array($txn->type, ['points_added', 'winning_points_added', 'manual_credit', 'bet_refunded', 'bet_cancelled_refunded']);
                            $displayAmount = abs($txn->amount);
                        @endphp
                        <tr class="hover:bg-slate-850/40 transition">
                            <!-- Transaction ID -->
                            <td class="py-4 px-5 font-mono font-medium text-slate-300 tracking-wide">
                                {{ $txn->transaction_id ?: $txn->transaction_code }}
                            </td>

                            <!-- Date & Time -->
                            <td class="py-4 px-5 text-slate-400 whitespace-nowrap">
                                {{ $txn->created_at->format('d M Y, h:i A') }}
                            </td>

                            <!-- Type Badge -->
                            <td class="py-4 px-5 whitespace-nowrap">
                                <x-transaction-badge :type="$txn->type" />
                            </td>

                            <!-- Remarks -->
                            <td class="py-4 px-5 text-slate-300 max-w-sm">
                                {{ $txn->remarks ?: '-' }}
                            </td>

                            <!-- Points (+ / -) -->
                            <td class="py-4 px-5 text-right font-black text-sm whitespace-nowrap {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $isCredit ? '+' : '-' }}{{ number_format($displayAmount, 0) }}
                            </td>

                            <!-- Updated Balance -->
                            <td class="py-4 px-5 text-right font-bold text-slate-200 whitespace-nowrap">
                                {{ number_format($txn->balance_after ?: $txn->updated_balance, 0) }} pts
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="text-3xl">📜</span>
                                    <span class="text-sm font-semibold">No transaction records found.</span>
                                    <p class="text-xs text-slate-600">Your points movements and game settlements will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-800 bg-[#090e1a]/60">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
