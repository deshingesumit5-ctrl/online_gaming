@extends('layouts.admin')

@section('page-title', 'User Master & Player Management')

@section('content')
<div class="space-y-6">
    <!-- Header & Search Tabs -->
    <div class="glass-panel p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
        <!-- Tabs -->
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.users.index', ['tab' => 'all']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $tab === 'all' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                All Users ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.users.index', ['tab' => 'pending']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $tab === 'pending' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Pending ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('admin.users.index', ['tab' => 'active']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $tab === 'active' ? 'bg-emerald-600 text-white font-black shadow-lg shadow-emerald-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Active ({{ $counts['active'] }})
            </a>
            <a href="{{ route('admin.users.index', ['tab' => 'inactive']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $tab === 'inactive' ? 'bg-slate-700 text-white font-black' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Inactive ({{ $counts['inactive'] }})
            </a>
            <a href="{{ route('admin.users.index', ['tab' => 'blocked']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $tab === 'blocked' ? 'bg-red-600 text-white font-black shadow-lg shadow-red-600/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Blocked ({{ $counts['blocked'] }})
            </a>
            <a href="{{ route('admin.users.index', ['tab' => 'admin_created']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition {{ $tab === 'admin_created' ? 'bg-amber-500 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                Admin Created ({{ $counts['admin_created'] }})
            </a>
        </div>

        <div class="flex flex-col items-stretch md:items-end gap-2 w-full md:w-auto">
            <button type="button" onclick="openAddUserModal()" class="self-end px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black uppercase tracking-wider shadow-lg shadow-amber-500/20">
                + Add User
            </button>
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 w-full md:w-auto">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search ID, name, username, mobile..."
                   class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs w-full md:w-64 focus:border-amber-400">
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition border border-slate-700">
                Search
            </button>
        </form>
        </div>
    </div>

    <!-- User Master Table Matching Image 2 Specs -->
    <div class="glass-panel overflow-hidden border-slate-800 rounded-2xl bg-[#0c1324]/90 shadow-xl">
        <!-- Mobile Card View (Shows ALL fields clearly without clipping) -->
        <div class="block md:hidden divide-y divide-slate-800/80">
            @forelse($users as $user)
                <div class="p-4 space-y-3 hover:bg-slate-800/30 transition">
                    <!-- Top ID & Status -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-amber-400">#{{ $user->id }}</span>
                        <x-status-badge :status="$user->status" />
                    </div>

                    <!-- Name & Username -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-slate-100 text-sm">{{ $user->name }}</div>
                            <span class="text-slate-400 font-mono text-xs">{{ '@' . $user->username }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block uppercase">Balance</span>
                            <span class="font-black text-sm text-amber-300 font-mono">{{ number_format($user->wallet_balance, 0) }} PTS</span>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-900/70 p-3 rounded-xl border border-slate-800/70">
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase">Mobile</span>
                            <span class="font-mono text-slate-200">{{ $user->mobile }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 block uppercase">Registered</span>
                            <span class="text-slate-300">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="col-span-2 pt-1.5 border-t border-slate-800/70">
                            <span class="text-[10px] text-slate-500 block uppercase">Last Login</span>
                            <span class="text-slate-300">{{ $user->last_login_at ? $user->last_login_at->format('d M Y, h:i A') : 'Never' }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1.5 flex-wrap pt-1">
                        <a href="{{ route('admin.users.show', $user->id) }}" 
                           class="flex-1 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition border border-slate-700 text-center">
                            View
                        </a>

                        <button type="button" 
                                onclick="openAddPointsModal({{ $user->id }}, '{{ $user->username }}', {{ $user->wallet_balance }})"
                                class="flex-1 py-2 rounded-lg bg-emerald-950/90 hover:bg-emerald-900 text-emerald-300 border border-emerald-500/50 text-xs font-bold transition text-center">
                            + Points
                        </button>

                        <button type="button" 
                                onclick="openDeductPointsModal({{ $user->id }}, '{{ $user->username }}', {{ $user->wallet_balance }})"
                                class="flex-1 py-2 rounded-lg bg-red-950/90 hover:bg-red-900 text-red-300 border border-red-800/50 text-xs font-bold transition text-center">
                            - Points
                        </button>

                        @if($user->status === 'pending')
                            <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="w-full mt-1">
                                @csrf
                                <input type="hidden" name="tab" value="{{ $tab }}">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="w-full py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition">
                                    Approve Registration
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="w-full">
                                @csrf
                                <input type="hidden" name="tab" value="{{ $tab }}">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="w-full py-2 rounded-lg bg-red-950 hover:bg-red-900 text-red-300 font-bold text-xs border border-red-800/80 transition">
                                    Reject Registration
                                </button>
                            </form>
                        @elseif($user->status === 'active')
                            <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="w-full mt-1">
                                @csrf
                                <input type="hidden" name="tab" value="{{ $tab }}">
                                <input type="hidden" name="status" value="blocked">
                                <button type="submit" class="w-full py-2 rounded-lg bg-red-950 hover:bg-red-900 text-red-400 text-xs font-bold border border-red-800/80 transition">
                                    Block Account
                                </button>
                            </form>
                        @elseif($user->status === 'inactive')
                            <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="w-full mt-1">
                                @csrf
                                <input type="hidden" name="tab" value="{{ $tab }}">
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="w-full py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition">
                                    Activate Account
                                </button>
                            </form>
                        @elseif($user->status === 'blocked')
                            <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="w-full mt-1">
                                @csrf
                                <input type="hidden" name="tab" value="{{ $tab }}">
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="w-full py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition">
                                    Unblock / Activate
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-slate-500 text-xs">No players found.</div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#090e1a]/95 text-slate-400 uppercase tracking-wider border-b border-slate-800 text-[11px]">
                    <tr>
                        <th class="py-4 px-4 font-bold">User ID</th>
                        <th class="py-4 px-4 font-bold">Name</th>
                        <th class="py-4 px-4 font-bold">Mobile</th>
                        <th class="py-4 px-4 font-bold">Registration Date</th>
                        <th class="py-4 px-4 font-bold">Status</th>
                        <th class="py-4 px-4 font-bold text-right">Wallet Balance</th>
                        <th class="py-4 px-4 font-bold">Last Login</th>
                        <th class="py-4 px-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-850/40 transition">
                            <!-- User ID -->
                            <td class="py-4 px-4 font-mono font-bold text-amber-400">
                                #{{ $user->id }}
                            </td>

                            <!-- Name -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-100 text-sm">{{ $user->name }}</div>
                                <span class="text-slate-400 font-mono text-[11px]">{{ '@' . $user->username }}</span>
                            </td>

                            <!-- Mobile -->
                            <td class="py-4 px-4 font-mono text-slate-300">
                                {{ $user->mobile }}
                            </td>

                            <!-- Registration Date -->
                            <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                                {{ $user->created_at->format('d M Y') }}
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <x-status-badge :status="$user->status" />
                            </td>

                            <!-- Wallet Balance -->
                            <td class="py-4 px-4 font-black text-amber-300 text-sm text-right whitespace-nowrap">
                                {{ number_format($user->wallet_balance, 0) }} PTS
                            </td>

                            <!-- Last Login -->
                            <td class="py-4 px-4 text-slate-400 whitespace-nowrap">
                                {{ $user->last_login_at ? $user->last_login_at->format('d M, h:i A') : 'Never' }}
                            </td>

                            <!-- Actions Menu -->
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    <!-- View Details -->
                                    <a href="{{ route('admin.users.show', $user->id) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold transition border border-slate-700" title="View Full Profile & History">
                                        View
                                    </a>

                                    <!-- Add Points Trigger -->
                                    <button type="button" 
                                            onclick="openAddPointsModal({{ $user->id }}, '{{ $user->username }}', {{ $user->wallet_balance }})"
                                            class="px-2.5 py-1.5 rounded-lg bg-emerald-950/90 hover:bg-emerald-900 text-emerald-300 border border-emerald-500/50 text-[11px] font-bold transition flex items-center gap-1" title="Add Points to User Wallet">
                                        <span>+</span> Points
                                    </button>

                                    <!-- Deduct Points Trigger -->
                                    <button type="button" 
                                            onclick="openDeductPointsModal({{ $user->id }}, '{{ $user->username }}', {{ $user->wallet_balance }})"
                                            class="px-2.5 py-1.5 rounded-lg bg-red-950/90 hover:bg-red-900 text-red-300 border border-red-800/50 text-[11px] font-bold transition flex items-center gap-1" title="Deduct Points from User Wallet">
                                        <span>-</span> Points
                                    </button>

                                    <!-- Status Transitions Dropdown / Quick Buttons -->
                                    @if($user->status === 'pending')
                                        <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="tab" value="{{ $tab }}">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] transition">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="tab" value="{{ $tab }}">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-red-700 hover:bg-red-600 text-white font-bold text-[11px] transition">
                                                Reject
                                            </button>
                                        </form>
                                    @elseif($user->status === 'active' || $user->status === 'approved')
                                        <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="tab" value="{{ $tab }}">
                                            <input type="hidden" name="status" value="inactive">
                                            <button type="submit" class="px-2 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-semibold border border-slate-700 transition" title="Deactivate account">
                                                Deactivate
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="inline" onsubmit="return confirm('Block player @{{ $user->username }}?');">
                                            @csrf
                                            <input type="hidden" name="tab" value="{{ $tab }}">
                                            <input type="hidden" name="status" value="blocked">
                                            <button type="submit" class="px-2 py-1.5 rounded-lg bg-red-950 hover:bg-red-900 text-red-400 text-[10px] font-bold border border-red-800/80 transition" title="Block account">
                                                Block
                                            </button>
                                        </form>
                                    @elseif($user->status === 'inactive')
                                        <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="tab" value="{{ $tab }}">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] transition">
                                                Activate
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="tab" value="{{ $tab }}">
                                            <input type="hidden" name="status" value="blocked">
                                            <button type="submit" class="px-2 py-1.5 rounded-lg bg-red-950 text-red-400 text-[10px] font-bold border border-red-800/80">
                                                Block
                                            </button>
                                        </form>
                                    @elseif($user->status === 'blocked')
                                        <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="tab" value="{{ $tab }}">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] transition">
                                                Unblock / Activate
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">No players found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800 bg-[#090e1a]/60">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Add User (same fields as player registration) -->
<div id="addUserModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm {{ ($errors->any() && old('form') === 'add_user') ? 'flex' : 'hidden' }} items-center justify-center p-4">
    <div class="relative w-full max-w-2xl max-h-[92vh] overflow-y-auto rounded-2xl bg-[#0e1626] border border-slate-800 p-5 sm:p-7 shadow-2xl">
        <button type="button" onclick="closeAddUserModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/10 text-white" aria-label="Close">&times;</button>
        <div class="text-center mb-4 pr-8">
            <div class="w-14 h-14 mx-auto mb-2 bg-black rounded-xl overflow-hidden flex items-center justify-center shadow-lg border border-slate-800/80">
                <img src="{{ asset('images/logo.png') }}" alt="Fun 2 Win" class="w-full h-full object-contain">
            </div>
            <h2 class="text-xl sm:text-2xl font-bold font-royal text-white tracking-wide">CREATE PLAYER ACCOUNT</h2>
            <p class="text-xs text-slate-400 mt-0.5">Register for exclusive access to live tables. Admin approval required.</p>
        </div>

        @if($errors->any() && old('form') === 'add_user')
            <div class="p-3.5 rounded-xl bg-red-950/80 border border-red-500/70 text-red-200 text-xs mb-4">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST" id="admin-add-user-form" class="needs-validation space-y-3" autocomplete="off">
            @csrf
            <input type="hidden" name="form" value="add_user">
            <div style="position: absolute; opacity: 0; pointer-events: none; height: 0; width: 0; overflow: hidden;" aria-hidden="true">
                <input type="text" name="decoy_username" tabindex="-1" autocomplete="username">
                <input type="password" name="decoy_password" tabindex="-1" autocomplete="new-password">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="admin-reg-name" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="admin-reg-name" name="name" value="{{ old('form') === 'add_user' ? old('name') : '' }}" required placeholder="e.g. Rahul Sharma" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label for="admin-reg-username" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" id="admin-reg-username" name="username" value="{{ old('form') === 'add_user' ? old('username') : '' }}" required placeholder="e.g. rahul_king" autocomplete="off" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="admin-reg-mobile" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">Mobile <span class="text-red-500">*</span></label>
                    <input type="tel" id="admin-reg-mobile" name="mobile" value="{{ old('form') === 'add_user' ? old('mobile') : '' }}" required maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" placeholder="10-digit number" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label for="admin-reg-email" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">Email(non mandatory)</label>
                    <input type="email" id="admin-reg-email" name="email" value="{{ old('form') === 'add_user' ? old('email') : '' }}" placeholder="e.g. rahul@example.com" autocomplete="off" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="admin-reg-password" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="admin-reg-password" name="password" required placeholder="••••••••" autocomplete="new-password" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl pl-3 pr-10 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                        <button type="button" onclick="toggleAdminPassword('admin-reg-password', this)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-400 p-1" aria-label="Toggle password visibility">
                            <svg class="w-4 h-4 eye-icon-show" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4 h-4 eye-icon-hide hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="admin-reg-password-confirm" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="admin-reg-password-confirm" name="password_confirmation" required placeholder="••••••••" autocomplete="new-password" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl pl-3 pr-10 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                        <button type="button" onclick="toggleAdminPassword('admin-reg-password-confirm', this)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-400 p-1" aria-label="Toggle password visibility">
                            <svg class="w-4 h-4 eye-icon-show" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4 h-4 eye-icon-hide hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="admin-reg-dob" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">DOB</label>
                    <input type="date" id="admin-reg-dob" name="dob" value="{{ old('form') === 'add_user' ? old('dob') : '' }}" max="{{ date('Y-m-d') }}" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label for="admin-reg-address" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">Address</label>
                    <input type="text" id="admin-reg-address" name="address" value="{{ old('form') === 'add_user' ? old('address') : '' }}" placeholder="Street, landmark, pincode" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="admin-reg-city" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">City</label>
                    <input type="text" id="admin-reg-city" name="city" value="{{ old('form') === 'add_user' ? old('city') : '' }}" placeholder="e.g. Mumbai" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label for="admin-reg-state" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">State</label>
                    <input type="text" id="admin-reg-state" name="state" value="{{ old('form') === 'add_user' ? old('state') : '' }}" placeholder="e.g. Maharashtra" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="admin-reg-country" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">Country</label>
                    <input type="text" id="admin-reg-country" name="country" value="{{ old('form') === 'add_user' ? old('country', 'India') : 'India' }}" placeholder="Country" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label for="admin-reg-kyc" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">Required verification/ KYC details</label>
                    <input type="text" id="admin-reg-kyc" name="kyc_info" value="{{ old('form') === 'add_user' ? old('kyc_info') : '' }}" placeholder="Aadhaar / PAN / ID number" class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
            </div>
            <div class="pt-1">
                <label class="flex items-start gap-2 cursor-pointer text-[11px] text-slate-300">
                    <input type="checkbox" id="admin-reg-terms" name="terms" value="1" class="mt-0.5 rounded text-amber-500 focus:ring-amber-500 bg-slate-900 border-slate-700" {{ old('form') === 'add_user' && old('terms') ? 'checked' : '' }}>
                    <span>Terms and conditions acceptance (I confirm that I am at least 18 years old and agree to the Terms of Service. Admin approval required.)</span>
                </label>
            </div>
            <button type="submit" class="w-full py-3 px-4 rounded-xl font-black text-xs uppercase tracking-widest text-slate-950 bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500 hover:from-amber-400 hover:to-yellow-300 shadow-lg shadow-amber-500/25 mt-2">
                Add User
            </button>
        </form>
    </div>
</div>

<!-- Modal: Add Points -->
<div id="addPointsModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-[#0f172a] border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                <span>➕</span> Add Points to Player Wallet
            </h3>
            <button type="button" onclick="closeAddPointsModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <p class="text-xs text-slate-300" id="addPointsModalSubtitle">
            Credit points directly to player wallet.
        </p>

        <form method="POST" action="{{ route('admin.users.points.add', 0) }}" id="addPointsForm" class="space-y-4">
            @csrf
            <input type="hidden" name="user_id" id="addPointsUserId">

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">
                    Points to Credit *
                </label>
                <input type="number" 
                       name="amount" 
                       min="1" 
                       step="1" 
                       required 
                       placeholder="e.g. 5000"
                       class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono font-bold text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">
                    Reason / Remarks (Mandatory Audit Trail) *
                </label>
                <textarea name="remarks" 
                          rows="2" 
                          required 
                          placeholder="e.g. Bank deposit verified / promotional credit / manual top-up"
                          class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeAddPointsModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-semibold transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition">
                    Credit Points
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Deduct Points -->
<div id="deductPointsModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-[#0f172a] border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold font-royal text-white flex items-center gap-2">
                <span>➖</span> Deduct Points from Player Wallet
            </h3>
            <button type="button" onclick="closeDeductPointsModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <p class="text-xs text-slate-300" id="deductPointsModalSubtitle">
            Debit points directly from player wallet.
        </p>

        <form method="POST" action="{{ route('admin.users.points.deduct', 0) }}" id="deductPointsForm" class="space-y-4">
            @csrf
            <input type="hidden" name="user_id" id="deductPointsUserId">

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">
                    Points to Debit *
                </label>
                <input type="number" 
                       id="deductPointsAmount"
                       name="amount" 
                       min="1" 
                       step="1" 
                       required 
                       placeholder="e.g. 1000"
                       class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono font-bold text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">
                    Reason / Remarks (Mandatory Audit Trail) *
                </label>
                <textarea name="remarks" 
                          rows="2" 
                          required 
                          placeholder="e.g. Correction of duplicate credit / Penalty / Chargeback"
                          class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeDeductPointsModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-semibold transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-xs font-bold transition">
                    Debit Points
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddPointsModal(userId, username, balance) {
        document.getElementById('addPointsUserId').value = userId;
        document.getElementById('addPointsForm').action = `/admin/users/${userId}/points/add`;
        document.getElementById('addPointsModalSubtitle').innerText = `Credit points to @${username} (Current Balance: ${balance.toLocaleString()} PTS)`;
        document.getElementById('addPointsModal').classList.remove('hidden');
    }

    function closeAddPointsModal() {
        document.getElementById('addPointsModal').classList.add('hidden');
    }

    function openDeductPointsModal(userId, username, balance) {
        document.getElementById('deductPointsUserId').value = userId;
        document.getElementById('deductPointsForm').action = `/admin/users/${userId}/points/deduct`;
        document.getElementById('deductPointsAmount').max = balance;
        document.getElementById('deductPointsModalSubtitle').innerText = `Debit points from @${username} (Max: ${balance.toLocaleString()} PTS)`;
        document.getElementById('deductPointsModal').classList.remove('hidden');
    }

    function closeDeductPointsModal() {
        document.getElementById('deductPointsModal').classList.add('hidden');
    }

    function openAddUserModal() {
        const modal = document.getElementById('addUserModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeAddUserModal() {
        const modal = document.getElementById('addUserModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function toggleAdminPassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const showIcon = btn.querySelector('.eye-icon-show');
        const hideIcon = btn.querySelector('.eye-icon-hide');
        if (input.type === 'password') {
            input.type = 'text';
            if (showIcon) showIcon.classList.add('hidden');
            if (hideIcon) hideIcon.classList.remove('hidden');
        } else {
            input.type = 'password';
            if (showIcon) showIcon.classList.remove('hidden');
            if (hideIcon) hideIcon.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection
