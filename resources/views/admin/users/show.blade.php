@extends('layouts.admin')

@section('page-title', 'Player Profile: ' . $user->name)

@section('content')
<div class="space-y-6">
    <!-- Top Action Card -->
    <div class="glass-panel p-6 border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center text-slate-950 font-black text-2xl font-royal shadow-lg">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-bold font-royal text-white">{{ $user->name }}</h1>
                <div class="flex items-center gap-3 mt-1 text-xs">
                    <span class="text-amber-400 font-mono font-bold">{{ '@' . $user->username }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                        Status: {{ $user->status }}
                    </span>
                    <span class="text-slate-400">Registered: {{ $user->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

        <!-- Status Modification Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
            @if($user->status === 'pending')
                <form method="POST" action="{{ route('admin.users.status', $user->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-xs shadow-lg transition">
                        ✓ Approve Account
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.users.status', $user->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="px-4 py-2 bg-red-800 hover:bg-red-700 text-white rounded-xl font-bold text-xs transition">
                        ✕ Reject Registration
                    </button>
                </form>
            @elseif(in_array($user->status, ['approved', 'active']))
                <form method="POST" action="{{ route('admin.users.status', $user->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="blocked">
                    <button type="submit" class="px-4 py-2 bg-red-900 hover:bg-red-800 text-red-200 rounded-xl font-semibold text-xs transition">
                        Block Player
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.users.status', $user->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white rounded-xl font-semibold text-xs transition">
                        Activate Account
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition border border-slate-700">
                &larr; Back to List
            </a>
        </div>
    </div>

    <!-- Personal & KYC Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-panel p-4 border-slate-800">
            <span class="text-slate-500 text-[11px] uppercase font-bold block mb-1">Wallet Balance</span>
            <span class="text-xl font-black text-amber-300 font-royal">{{ number_format($user->wallet_balance, 0) }} pts</span>
        </div>

        <div class="glass-panel p-4 border-slate-800">
            <span class="text-slate-500 text-[11px] uppercase font-bold block mb-1">Mobile</span>
            <span class="text-sm font-bold text-slate-200 font-mono">{{ $user->mobile }}</span>
        </div>

        <div class="glass-panel p-4 border-slate-800">
            <span class="text-slate-500 text-[11px] uppercase font-bold block mb-1">Email</span>
            <span class="text-sm font-semibold text-slate-200">{{ $user->email }}</span>
        </div>

        <div class="glass-panel p-4 border-slate-800">
            <span class="text-slate-500 text-[11px] uppercase font-bold block mb-1">Location</span>
            <span class="text-sm font-semibold text-slate-200">{{ $user->city ? "{$user->city}, {$user->state}" : ($user->country ?: 'India') }}</span>
        </div>
    </div>

    <!-- KYC / Address Box -->
    <div class="glass-panel p-5 border-slate-800">
        <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-2">KYC Details & Settlement Information</h3>
        <p class="text-xs text-slate-300 font-mono bg-slate-900 p-3 rounded-lg border border-slate-800">
            {{ $user->kyc_info ?: 'No KYC details submitted.' }}
        </p>
        <div class="mt-3 text-xs text-slate-400">
            <strong>Address:</strong> {{ $user->address ?: 'Not provided' }}
        </div>
    </div>

    <!-- Tabbed History: Bets & Ledger -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bets History -->
        <div class="glass-panel p-5 border-slate-800">
            <h3 class="text-sm font-bold font-royal text-white mb-4 flex items-center gap-2">
                <span>🎲</span> Player Bets (Last 20)
            </h3>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($user->bets as $bet)
                    <div class="p-2.5 bg-slate-900 rounded-lg border border-slate-800 flex items-center justify-between text-xs">
                        <div>
                            <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] {{ $bet->selection === 'andar' ? 'badge-andar' : 'badge-bahar' }}">
                                {{ $bet->selection }}
                            </span>
                            <span class="text-slate-300 ml-1.5">Round #{{ $bet->round->round_number ?? '-' }}</span>
                            <span class="text-slate-500 text-[10px] block mt-0.5">{{ $bet->created_at->format('d M, h:i A') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-white font-bold block">{{ number_format($bet->amount, 0) }} pts</span>
                            <span class="text-[10px] uppercase font-bold {{ $bet->status === 'won' ? 'text-emerald-400' : ($bet->status === 'lost' ? 'text-slate-500' : 'text-amber-400') }}">
                                {{ $bet->status }} ({{ number_format($bet->payout_amount, 0) }})
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs">No bets placed yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Ledger History -->
        <div class="glass-panel p-5 border-slate-800">
            <h3 class="text-sm font-bold font-royal text-white mb-4 flex items-center gap-2">
                <span>📜</span> Wallet Transactions (Last 20)
            </h3>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($user->walletTransactions as $txn)
                    @php
                        $isCredit = in_array($txn->type, ['points_added', 'winning_points_added', 'manual_credit', 'bet_refunded']);
                    @endphp
                    <div class="p-2.5 bg-slate-900 rounded-lg border border-slate-800 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-slate-200 font-semibold block">{{ $txn->remarks }}</span>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $txn->transaction_code }} &bull; {{ $txn->created_at->format('d M, h:i A') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold block {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $isCredit ? '+' : '-' }}{{ number_format($txn->amount, 0) }}
                            </span>
                            <span class="text-[10px] text-slate-400">Bal: {{ number_format($txn->updated_balance, 0) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs">No transactions in ledger.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
