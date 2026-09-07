@extends(auth()->check() && auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.app')

@section('title', 'Player Profile - Fun 2 Win')
@section('page-title', 'My Profile')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Player Profile Overview -->
    <div class="glass-panel p-6 border-amber-500/20">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-800">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-300 flex items-center justify-center text-slate-950 font-black text-2xl font-royal shadow-lg">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-bold font-royal text-white">{{ $user->name }}</h1>
                <div class="flex items-center gap-3 mt-1 text-xs">
                    <span class="text-amber-400 font-mono font-semibold cursor-pointer hover:underline" onclick="openProfileTab('username')" title="Change Username">{{ '@' . $user->username }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-950 text-emerald-300 border border-emerald-500/50">
                        {{ $user->status }}
                    </span>
                    <span class="text-slate-400 font-medium">Joined {{ $user->created_at->format('M Y') }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
            <div>
                <span class="text-slate-500 uppercase font-bold block mb-1">Email Address</span>
                <span class="text-slate-200 font-semibold text-sm">{{ $user->email ?: 'Not Provided' }}</span>
            </div>

            <div>
                <span class="text-slate-500 uppercase font-bold block mb-1">Mobile Number</span>
                <span class="text-slate-200 font-semibold text-sm font-mono">{{ $user->mobile ?: 'Not Provided' }}</span>
            </div>

            <div>
                <span class="text-slate-500 uppercase font-bold block mb-1">Date of Birth</span>
                <span class="text-slate-200 font-semibold text-sm">{{ $user->dob ? $user->dob->format('d M Y') : 'Not Provided' }}</span>
            </div>

            <div>
                <span class="text-slate-500 uppercase font-bold block mb-1">Location</span>
                <span class="text-slate-200 font-semibold text-sm">{{ $user->city ? "{$user->city}, {$user->state}, {$user->country}" : ($user->country ?: 'India') }}</span>
            </div>

            <div class="md:col-span-2">
                <span class="text-slate-500 uppercase font-bold block mb-1">Residential Address</span>
                <span class="text-slate-200 font-semibold text-sm">{{ $user->address ?: 'Not provided' }}</span>
            </div>
        </div>

        <!-- Username & Password in Middle -->
        <div class="mt-6 pt-6 border-t border-slate-800">
            <span class="text-slate-400 uppercase font-bold text-[11px] tracking-wider block mb-3">Security & Account Credentials</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Username Display Card -->
                <div class="p-4 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between hover:border-amber-500/40 transition">
                    <div>
                        <span class="text-slate-500 uppercase font-bold text-[10px] block mb-1">Username</span>
                        <span class="text-slate-200 font-bold text-sm font-mono cursor-pointer hover:text-amber-300 transition" onclick="openProfileTab('username')">{{ '@' . $user->username }}</span>
                    </div>
                    <button type="button" onclick="openProfileTab('username')" class="p-2 rounded-lg bg-slate-800 hover:bg-amber-500/20 text-slate-300 hover:text-amber-300 border border-slate-700 hover:border-amber-500/40 transition flex items-center gap-1.5 text-xs font-semibold cursor-pointer" title="Edit Username">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        <span>Edit</span>
                    </button>
                </div>

                <!-- Password Display Card -->
                <div class="p-4 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between hover:border-amber-500/40 transition">
                    <div>
                        <span class="text-slate-500 uppercase font-bold text-[10px] block mb-1">Password</span>
                        <span class="text-slate-400 font-mono text-sm tracking-widest cursor-pointer hover:text-amber-300 transition" onclick="openProfileTab('password')">••••••••••••</span>
                    </div>
                    <button type="button" onclick="openProfileTab('password')" class="p-2 rounded-lg bg-slate-800 hover:bg-amber-500/20 text-slate-300 hover:text-amber-300 border border-slate-700 hover:border-amber-500/40 transition flex items-center gap-1.5 text-xs font-semibold cursor-pointer" title="Edit Password">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        <span>Edit</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- KYC Details Section at Last -->
        <div class="mt-6 pt-6 border-t border-slate-800">
            <div class="p-3 bg-slate-900 rounded-xl border border-slate-800">
                <span class="text-amber-400 uppercase font-bold block mb-1">KYC / Settlement Information</span>
                <span class="text-slate-300 font-mono text-xs">{{ $user->kyc_info ?: 'No KYC information provided' }}</span>
            </div>
        </div>
    </div>

    <!-- Credentials Modification Box: Change Username & Change Password Tabs (Only shows after clicking Edit) -->
    <div id="credentials-tabs-card" class="glass-panel p-6 border-amber-500/20 rounded-2xl bg-[#0d1322] shadow-xl hidden">
        <!-- Tabs Buttons and Close Button -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-6">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <button type="button" id="tab-btn-username" onclick="openProfileTab('username')" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-amber-500 text-slate-950 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Change Username</span>
                </button>
                <button type="button" id="tab-btn-password" onclick="openProfileTab('password')" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-slate-800/90 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>Change Password</span>
                </button>
            </div>
            <button type="button" onclick="closeProfileEdit()" class="p-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white transition text-xs font-bold flex items-center justify-center cursor-pointer" title="Close">
                ✕
            </button>
        </div>

        <!-- Tab Pane 1: Change Username -->
        <div id="tab-pane-username" class="space-y-4">
            <div class="mb-4">
                <h3 class="text-base font-bold font-royal text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Change Username
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Enter a new username. Once saved, this replaces your old username and you will log in with this new username.</p>
            </div>

            <form action="{{ route('profile.username.update') }}" method="POST" class="space-y-4 max-w-md">
                @csrf
                <div>
                    <label class="text-slate-400 text-xs font-semibold block mb-1.5">Current Username</label>
                    <input type="text" value="{{ '@' . $user->username }}" readonly class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 text-sm font-mono cursor-not-allowed">
                </div>

                <div>
                    <label for="new_username" class="text-slate-300 text-xs font-semibold block mb-1.5">New Username <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-amber-400 font-mono text-sm font-bold">@</span>
                        <input type="text" id="new_username" name="username" value="{{ old('username') }}" placeholder="Enter new username" required minlength="3" maxlength="25" pattern="[a-zA-Z0-9_]+" class="w-full pl-8 pr-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 font-mono">
                    </div>
                    @error('username')
                        <p class="text-red-400 text-xs mt-1.5 font-medium flex items-center gap-1">⚠️ {{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-slate-500 mt-1">Letters, numbers, and underscores only (3-25 characters).</p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-amber-500/20 cursor-pointer">
                        Save Username
                    </button>
                    <button type="button" onclick="closeProfileEdit()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition border border-slate-700 cursor-pointer">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab Pane 2: Change Password -->
        <div id="tab-pane-password" class="space-y-4 hidden">
            <div class="mb-4">
                <h3 class="text-base font-bold font-royal text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Change Password
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Enter and confirm your new password below.</p>
            </div>

            <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-4 max-w-md">
                @csrf
                <div>
                    <label for="new_password" class="text-slate-300 text-xs font-semibold block mb-1.5">New Password <span class="text-red-400">*</span></label>
                    <input type="password" id="new_password" name="password" placeholder="Enter new password" required minlength="6" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('password')
                        <p class="text-red-400 text-xs mt-1.5 font-medium flex items-center gap-1">⚠️ {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="confirm_password" class="text-slate-300 text-xs font-semibold block mb-1.5">Confirm Password <span class="text-red-400">*</span></label>
                    <input type="password" id="confirm_password" name="password_confirmation" placeholder="Confirm new password" required minlength="6" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-amber-500/20 cursor-pointer">
                        Save Password
                    </button>
                    <button type="button" onclick="closeProfileEdit()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition border border-slate-700 cursor-pointer">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openProfileTab(tabName) {
        const card = document.getElementById('credentials-tabs-card');
        if (card) {
            card.classList.remove('hidden');
        }

        const usernameBtn = document.getElementById('tab-btn-username');
        const passwordBtn = document.getElementById('tab-btn-password');
        const usernamePane = document.getElementById('tab-pane-username');
        const passwordPane = document.getElementById('tab-pane-password');

        if (tabName === 'username') {
            if (usernameBtn) usernameBtn.className = 'flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-amber-500 text-slate-950 shadow-md';
            if (passwordBtn) passwordBtn.className = 'flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-slate-800/90 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700';
            if (usernamePane) usernamePane.classList.remove('hidden');
            if (passwordPane) passwordPane.classList.add('hidden');
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            const input = document.getElementById('new_username');
            if (input) setTimeout(() => input.focus(), 250);
        } else if (tabName === 'password') {
            if (passwordBtn) passwordBtn.className = 'flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-amber-500 text-slate-950 shadow-md';
            if (usernameBtn) usernameBtn.className = 'flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer bg-slate-800/90 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700';
            if (passwordPane) passwordPane.classList.remove('hidden');
            if (usernamePane) usernamePane.classList.add('hidden');
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            const input = document.getElementById('new_password');
            if (input) setTimeout(() => input.focus(), 250);
        }
    }

    function closeProfileEdit() {
        const card = document.getElementById('credentials-tabs-card');
        if (card) {
            card.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(isset($errors) && ($errors->has('password') || $errors->has('password_confirmation')))
            openProfileTab('password');
        @elseif(isset($errors) && $errors->has('username'))
            openProfileTab('username');
        @endif
    });
</script>
@endpush
