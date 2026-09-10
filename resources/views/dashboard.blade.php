@extends('layouts.app')

@section('title', 'Player Dashboard - Fun 2 Win')

@section('content')
@php $tab = request()->get('tab', 'dashboard'); @endphp
<div class="space-y-6 sm:space-y-8">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 1: USER PROFILE SUMMARY BANNER (Dashboard Tab Only) --}}
    {{-- Shows: User Name, User ID, Wallet/Points Balance --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($tab !== 'lobby')
    <div class="glass-panel p-5 sm:p-7 md:p-8 relative overflow-hidden border-amber-500/30">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-950 text-emerald-300 border border-emerald-500/50">
                        ● Account Active &amp; Approved
                    </span>
                    {{-- USER ID --}}
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold uppercase tracking-wider bg-slate-900 text-amber-400 border border-slate-700">
                        User ID: #{{ $user->id }}
                    </span>
                    <span class="text-xs text-slate-400 font-mono">
                        {{ '@' . $user->username }}
                    </span>
                </div>
                {{-- USER NAME --}}
                <h1 class="text-2xl md:text-3xl font-bold font-royal text-white">
                    Welcome back, <span class="text-amber-300">{{ $user->name }}</span>
                </h1>
                <p class="text-xs text-slate-300 mt-1">Select a live table below to enter the private dealer room and start placing your bets.</p>
            </div>

            {{-- WALLET / POINTS BALANCE --}}
            <div class="bg-slate-900/90 border border-amber-500/30 p-4 rounded-2xl flex items-center gap-5 min-w-[240px] shadow-lg">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center text-slate-950 text-xl font-bold">
                    💰
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Available Balance</span>
                    <span class="text-xl md:text-2xl font-black text-amber-300 user-wallet-balance">
                        {{ number_format($user->wallet_balance, 0) }} <span class="text-xs font-bold text-amber-500">PTS</span>
                    </span>
                </div>
                <div class="ml-auto flex flex-col gap-1.5">
                    <a href="{{ route('points.request') }}" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-400 text-slate-950 text-[10px] font-black rounded-lg transition text-center uppercase tracking-wider">
                        + Top Up
                    </a>
                    <a href="{{ route('withdrawals.create') }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[10px] font-bold rounded-lg transition border border-slate-700 text-center uppercase tracking-wider">
                        Withdraw
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

@if($tab !== 'lobby')
    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 2: DASHBOARD QUICK STATS --}}
    {{-- Shows: Active Game, Available Rooms, Game Status --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Active Games Count — links to Lobby --}}
        <a href="{{ route('dashboard') }}?tab=lobby" class="glass-panel p-4 rounded-2xl flex flex-col items-center justify-center text-center gap-1 border-slate-700 hover:border-amber-500/60 hover:scale-105 transition-all duration-200 cursor-pointer group">
            <span class="text-2xl font-black text-amber-400 group-hover:text-amber-300">{{ $games->count() > 0 ? $games->count() : '—' }}</span>
            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Active Games</span>
        </a>

        {{-- Available (Live) Rooms Count — links to Lobby --}}
        <a href="{{ route('dashboard') }}?tab=lobby" class="glass-panel p-4 rounded-2xl flex flex-col items-center justify-center text-center gap-1 border-slate-700 hover:border-emerald-500/60 hover:scale-105 transition-all duration-200 cursor-pointer group">
            <span class="text-2xl font-black text-emerald-400 group-hover:text-emerald-300">{{ $liveRoomsCount > 0 ? $liveRoomsCount : '—' }}</span>
            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Available Rooms</span>
        </a>

        {{-- Points Balance — links to Transactions/Ledger --}}
        <a href="{{ route('wallet.transactions') }}" class="glass-panel p-4 rounded-2xl flex flex-col items-center justify-center text-center gap-1 border-slate-700 hover:border-amber-500/60 hover:scale-105 transition-all duration-200 cursor-pointer group">
            <span class="text-2xl font-black text-amber-300 group-hover:text-amber-200">{{ $user->wallet_balance > 0 ? number_format($user->wallet_balance, 0) : '0' }}</span>
            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Points Balance</span>
        </a>

        {{-- Total Bets Placed — links to Transactions/Ledger --}}
        <a href="{{ route('wallet.transactions') }}" class="glass-panel p-4 rounded-2xl flex flex-col items-center justify-center text-center gap-1 border-slate-700 hover:border-sky-500/60 hover:scale-105 transition-all duration-200 cursor-pointer group">
            <span class="text-2xl font-black text-sky-400 group-hover:text-sky-300">{{ $recentBets->count() > 0 ? $recentBets->count() . '+' : '—' }}</span>
            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Recent Bets</span>
        </a>
    </div>

@endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 3: LIVE GAMING ROOMS                                --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($tab === 'lobby')
    <div class="w-full my-2 sm:my-4">
        @php 
            $allRoomsList = [];
            foreach($games as $game) {
                foreach($game->rooms as $r) {
                    $allRoomsList[] = $r;
                }
            }
        @endphp

        @if(count($allRoomsList) > 0)
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 max-w-6xl mx-auto">
                @foreach($allRoomsList as $roomIndex => $room)
                    <div class="flex flex-col items-center">
                        {{-- Room Card with White Border & Dealer Thumbnail --}}
                      <a href="{{ route('game.play', $room->id) }}" class="casino-room-card block w-full aspect-[4/3] rounded-xl border-2 border-white overflow-hidden shadow-2xl group cursor-pointer relative" title="Enter {{ $room->name }}">
                            <img src="{{ asset('images/room-thumb.jpg') }}" alt="{{ $room->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <div class="absolute inset-0 bg-black/40 group-hover:bg-black/20 transition flex items-center justify-center p-2">
                                <span class="px-3 py-1 rounded-md bg-black/70 border border-white/30 text-white font-black text-xs sm:text-sm tracking-wider uppercase text-center shadow-lg font-royal">
                                    {{ $room->name }}
                                </span>
                            </div>
                        </a>

                        {{-- Status & Metadata (Online/Offline, Users, Opening/Closing Hours) --}}
                        <div class="w-full text-center mt-2.5">
                            @if($room->status === 'live')
                                <div class="inline-flex items-center justify-center gap-1.5 text-emerald-400 font-black text-xs sm:text-sm">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_#10b981] animate-pulse"></span>
                                    <span>Online</span>
                                </div>
                                <div class="text-[10px] sm:text-xs text-slate-300 mt-1 font-medium space-y-0.5">
                                    <div>Users: {{ $room->active_users_count }}</div>
                                    <div>Opening: {{ $room->opening_time ?? '11:15 AM' }}</div>
                                    <div>Closing: {{ $room->closing_time ?? '10:00 PM' }}</div>
                                </div>
                            @else
                                <div class="inline-flex items-center justify-center gap-1.5 text-red-500 font-black text-xs sm:text-sm">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_8px_#ef4444]"></span>
                                    <span>Offline</span>
                                </div>
                                <div class="text-[10px] sm:text-xs text-slate-400 mt-1 font-medium space-y-0.5">
                                    <div>Users: 0</div>
                                    <div>Opening: {{ $room->opening_time ?? '11:15 AM' }}</div>
                                    <div>Closing: {{ $room->closing_time ?? '10:00 PM' }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-slate-400 text-sm">
                No gaming rooms available at the moment.
            </div>
        @endif
    </div>
    @endif

    @if($tab !== 'lobby')
    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 4: BETTING HISTORY & TRANSACTION HISTORY --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- BETTING HISTORY --}}
        <div class="glass-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span>🎲</span> Betting History
                </h3>
                <a href="{{ route('wallet.transactions') }}" class="text-xs text-amber-400 hover:underline">View All</a>
            </div>

            <div class="space-y-2.5">
                @forelse($recentBets as $bet)
                    <div class="flex items-center justify-between p-3 bg-slate-900/70 rounded-xl border border-slate-800 text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded font-bold uppercase {{ $bet->selection === 'andar' ? 'badge-andar' : 'badge-bahar' }}">
                                    {{ $bet->selection }}
                                </span>
                                <span class="text-slate-300">Round #{{ $bet->round->round_number ?? '—' }}</span>
                            </div>
                            <span class="text-[10px] text-slate-500 block mt-0.5">
                                {{ $bet->round->room->game->name ?? '—' }} &bull; {{ $bet->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <div class="text-right">
                            @if($bet->status === 'won')
                                <span class="text-white font-bold block">{{ number_format($bet->amount, 0) }} pts</span>
                                <span class="text-emerald-400 font-bold text-[11px]">+{{ number_format($bet->payout_amount, 0) }} WON</span>
                            @elseif($bet->status === 'lost')
                                <span class="text-white font-bold block">{{ number_format($bet->amount, 0) }} pts</span>
                                <span class="text-red-400 font-bold text-[11px]">-{{ number_format($bet->amount, 0) }} Lost</span>
                            @elseif($bet->status === 'cancelled')
                                <span class="text-white font-bold block">{{ number_format($bet->amount, 0) }} pts</span>
                                <span class="text-slate-400 text-[11px]">CANCELLED</span>
                            @else
                                <span class="text-white font-bold block">{{ number_format($bet->amount, 0) }} pts</span>
                                <span class="text-amber-400 text-[11px]">ACTIVE</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs">No data available</div>
                @endforelse
            </div>
        </div>

        {{-- TRANSACTION HISTORY --}}
        <div class="glass-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span>📜</span> Transaction History
                </h3>
                <a href="{{ route('wallet.transactions') }}" class="text-xs text-amber-400 hover:underline">Full Statement</a>
            </div>

            <div class="space-y-2.5">
                @forelse($recentTransactions as $txn)
                    <div class="flex items-center justify-between p-3 bg-slate-900/70 rounded-xl border border-slate-800 text-xs">
                        <div>
                            <span class="text-slate-200 font-semibold block">{{ $txn->remarks ?? '—' }}</span>
                            <span class="text-[10px] text-slate-500 font-mono">
                                {{ $txn->transaction_code ?? $txn->transaction_id ?? '—' }} &bull; {{ $txn->created_at->format('M d, H:i') }}
                            </span>
                        </div>

                        <div class="text-right font-bold">
                            @php
                                $isCredit = in_array($txn->type, ['points_added', 'winning_points_added', 'manual_credit', 'bet_refunded', 'bet_cancelled_refunded']) || $txn->amount > 0;
                                $displayAmount = abs($txn->amount);
                            @endphp
                            @if($isCredit)
                                <span class="text-emerald-400 font-bold">+{{ number_format($displayAmount, 0) }}</span>
                            @else
                                <span class="text-red-500 font-bold">-{{ number_format($displayAmount, 0) }}</span>
                            @endif
                            <span class="text-[10px] text-slate-400 block font-normal">Bal: {{ number_format($txn->updated_balance ?? $txn->balance_after, 0) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs">No data available</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SECTION 5: WITHDRAWAL HISTORY & MY REQUEST POINTS --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- WITHDRAWAL HISTORY --}}
        <div class="glass-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span>🏦</span> Withdrawal History
                </h3>
                <a href="{{ route('withdrawals.create') }}" class="text-xs text-amber-400 hover:underline">New Withdrawal</a>
            </div>

            <div class="space-y-2.5">
                @forelse($recentWithdrawals as $withdrawal)
                    <div class="flex items-center justify-between p-3 bg-slate-900/70 rounded-xl border border-slate-800 text-xs">
                        <div>
                            <span class="text-slate-200 font-semibold block font-mono">{{ $withdrawal->request_id ?? '—' }}</span>
                            <span class="text-[10px] text-slate-500">{{ $withdrawal->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-white font-bold block">{{ number_format($withdrawal->amount_requested, 0) }} pts</span>
                            @if(in_array(strtolower($withdrawal->status), ['approved', 'processed', 'settled']))
                                <span class="text-emerald-400 text-[11px] font-bold">APPROVED</span>
                            @elseif(strtolower($withdrawal->status) === 'rejected')
                                <span class="text-red-400 text-[11px] font-bold">REJECTED</span>
                            @else
                                <span class="text-amber-400 text-[11px] font-bold">PENDING</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs">No data available</div>
                @endforelse
            </div>
        </div>

        {{-- MY REQUEST POINTS HISTORY --}}
        <div class="glass-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                    <span>📋</span> My Request Points
                </h3>
                <a href="{{ route('points.request') }}" class="text-xs text-amber-400 hover:underline">{{ $recentPointRequests->count() }} Total</a>
            </div>

            <div class="space-y-2.5">
                @forelse($recentPointRequests as $req)
                    <div class="flex items-center justify-between p-3 bg-slate-900/70 rounded-xl border border-slate-800 text-xs">
                        <div>
                            <span class="text-slate-200 font-semibold block font-mono">{{ $req->request_id }}</span>
                            <span class="text-[10px] text-slate-500">{{ $req->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-amber-300 font-black block">+{{ number_format($req->points_requested, 0) }} PTS</span>
                            <x-status-badge :status="$req->status" />
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-500 text-xs">No data available</div>
                @endforelse
            </div>
        </div>
    </div>

    @endif
</div>
@endsection
