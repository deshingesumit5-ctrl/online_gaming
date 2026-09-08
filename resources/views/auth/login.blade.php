@extends('layouts.app')

@section('title', 'Sign In - Fun 2 Win Private Gaming Club')

@section('content')
<div class="w-full max-w-md mx-auto px-4 py-2 flex items-center justify-center min-h-[calc(100vh-6rem)] my-auto">

    <!-- Auth Box (Centered on mobile and desktop) -->
    <div class="w-full transition-all">
        
        <!-- PANEL 1: LOGIN SQUARE BOX -->
        <div id="auth-box-login" class="relative rounded-2xl bg-[#0e1626]/95 backdrop-blur-xl border border-slate-800/90 p-5 sm:p-7 shadow-[0_20px_50px_rgba(0,0,0,0.8),0_0_30px_rgba(245,158,11,0.06)]">
            
            <!-- Cards Design First & Fun 2 Win Logo After in ONE LINE -->
            <div class="flex items-center justify-center gap-3 sm:gap-5 mb-2 sm:mb-3 select-none">
                <!-- Cards Design (Fanned Casino Cards with Glowing Aura) -->
                <div class="relative w-28 sm:w-32 h-20 sm:h-22 flex items-center justify-center shrink-0">
                    <!-- Ambient Glow -->
                    <div class="absolute inset-0 rounded-full bg-amber-500/20 blur-xl pointer-events-none"></div>

                    <!-- Fanned Cards -->
                    <div class="relative w-24 h-20 flex items-center justify-center">
                        <!-- Card 1: Queen of Spades (Left tilt) -->
                        <div class="absolute w-12 sm:w-14 h-16 sm:h-18 rounded-lg bg-gradient-to-b from-[#1c2438] via-[#0d1424] to-[#080d18] border border-amber-400/80 shadow-md transform -translate-x-4 -rotate-12 p-1 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-amber-300 font-bold text-[8px] leading-none">
                                <span>Q</span>
                                <span>♠</span>
                            </div>
                            <div class="my-auto text-center">
                                <span class="text-base sm:text-lg text-amber-400 drop-shadow">♛</span>
                            </div>
                            <div class="flex items-center justify-between text-amber-300 font-bold text-[8px] leading-none transform rotate-180">
                                <span>Q</span>
                                <span>♠</span>
                            </div>
                        </div>

                        <!-- Card 3: Ace of Spades (Right tilt) -->
                        <div class="absolute w-12 sm:w-14 h-16 sm:h-18 rounded-lg bg-gradient-to-b from-[#1c2438] via-[#0d1424] to-[#080d18] border border-amber-400/80 shadow-md transform translate-x-4 rotate-12 p-1 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-amber-300 font-bold text-[8px] leading-none">
                                <span>A</span>
                                <span>♠</span>
                            </div>
                            <div class="my-auto text-center">
                                <span class="text-base sm:text-lg text-amber-400 drop-shadow">♠</span>
                            </div>
                            <div class="flex items-center justify-between text-amber-300 font-bold text-[8px] leading-none transform rotate-180">
                                <span>A</span>
                                <span>♠</span>
                            </div>
                        </div>

                        <!-- Card 2: King of Spades (Center, elevated) -->
                        <div class="absolute w-13 sm:w-15 h-18 sm:h-20 rounded-lg bg-gradient-to-b from-[#242f49] via-[#111a2e] to-[#0a101d] border-2 border-amber-300 shadow-lg transform -translate-y-1 z-10 p-1 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-yellow-300 font-bold text-[9px] leading-none">
                                <span>K</span>
                                <span>♠</span>
                            </div>
                            <div class="my-auto text-center">
                                <span class="text-lg sm:text-xl text-yellow-300 drop-shadow">♚</span>
                            </div>
                            <div class="flex items-center justify-between text-yellow-300 font-bold text-[9px] leading-none transform rotate-180">
                                <span>K</span>
                                <span>♠</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sparkles -->
                    <span class="absolute top-1 left-1 text-amber-300 text-[10px] animate-ping">✦</span>
                    <span class="absolute bottom-1 right-1 text-yellow-200 text-[10px] animate-pulse">✨</span>
                </div>

                <!-- Fun 2 Win Logo (Shown after cards design in ONE line) -->
                <div class="w-16 h-16 sm:w-18 sm:h-18 bg-black rounded-xl overflow-hidden flex items-center justify-center shadow-lg border border-amber-500/40 shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Fun 2 Win" class="w-full h-full object-contain">
                </div>
            </div>

            <!-- Title & Subtitle -->
            <div class="text-center mb-4 sm:mb-5">
                <h2 class="text-xl sm:text-2xl font-bold font-royal text-white tracking-wide">FUN 2 WIN LOGIN</h2>
                <p class="text-xs text-slate-400 mt-1">Enter your credentials to enter the live game arena</p>
            </div>

            <!-- Errors Banner (Login) -->
            @if($errors->has('login'))
                <div class="p-3 rounded-xl bg-red-950/80 border border-red-500/70 text-red-200 text-xs mb-4 flex items-start gap-2">
                    <span class="text-sm shrink-0">⚠️</span>
                    <span>{{ $errors->first('login') }}</span>
                </div>
            @endif

            @if(session('approved_status'))
                <div class="p-3 rounded-xl bg-emerald-950/80 border border-emerald-500/70 text-emerald-200 text-xs mb-4 flex items-start gap-2">
                    <span class="text-sm shrink-0">✅</span>
                    <span>{{ session('approved_status') }}</span>
                </div>
            @endif

            @if(session('success_status'))
                <div class="p-3 rounded-xl bg-emerald-950/80 border border-emerald-500/70 text-emerald-200 text-xs mb-4 flex items-start gap-2">
                    <span class="text-sm shrink-0">✓</span>
                    <span>{{ session('success_status') }}</span>
                </div>
            @endif

            @if(session('warning_status'))
                <div class="p-3 rounded-xl bg-amber-950/80 border border-amber-500/70 text-amber-200 text-xs mb-4 flex items-start gap-2">
                    <span class="text-sm shrink-0">⏳</span>
                    <span>{{ session('warning_status') }}</span>
                </div>
            @endif

            @if(session('error_status'))
                <div class="p-3 rounded-xl bg-red-950/80 border border-red-500/70 text-red-200 text-xs mb-4 flex items-start gap-2">
                    <span class="text-sm shrink-0">⚠️</span>
                    <span>{{ session('error_status') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.post') }}" method="POST" class="needs-validation space-y-4" data-validate="true" autocomplete="off">
                @csrf

                <!-- Decoy inputs to prevent browser autofilling saved login credentials -->
                <div style="position: absolute; opacity: 0; pointer-events: none; height: 0; width: 0; overflow: hidden;" aria-hidden="true">
                    <input type="text" name="decoy_login_username" tabindex="-1" autocomplete="username">
                    <input type="password" name="decoy_login_password" tabindex="-1" autocomplete="new-password">
                </div>

                <div>
                    <label for="login" class="block text-[11px] font-bold tracking-wider text-slate-300 mb-1.5">
                        Username or Mobile Number
                    </label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required 
                           placeholder="Enter username / 10-digit mobile"
                           class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-4 py-2.5 sm:py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('login') border-red-500 @enderror" autocomplete="off">
                </div>

                <div>
                    <label for="password" class="block text-[11px] font-bold tracking-wider text-slate-300 mb-1.5">
                        Password
                    </label>
                    <div class="relative" style="min-height:44px;">
                        <input type="password" id="password" name="password" value="{{ old('password') }}" required placeholder="••••••••"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl pl-4 pr-11 py-2.5 sm:py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('password') border-red-500 @enderror" autocomplete="new-password">
                        <button type="button" onclick="togglePasswordVisibility('password', this)" style="position:absolute;top:50%;right:0.75rem;transform:translateY(-50%);" class="text-slate-400 hover:text-amber-400 focus:outline-none p-1 transition" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg class="w-5 h-5 eye-icon-show" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-5 h-5 eye-icon-hide hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex items-center justify-end mt-1.5">
                        <button type="button" onclick="switchAuthMode('forgot')" class="text-xs text-amber-400 hover:text-amber-300 font-bold transition hover:underline cursor-pointer">
                            Forget Password?
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 sm:py-3.5 px-4 rounded-xl font-black text-xs uppercase tracking-widest text-slate-950 bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500 hover:from-amber-400 hover:to-yellow-300 shadow-lg shadow-amber-500/25 transition duration-200 mt-3">
                    LOGIN
                </button>
            </form>

            <!-- Don't have an account? Sign up -->
            <div class="text-center mt-4 pt-3 border-t border-slate-800/60">
                <p class="text-xs text-slate-400">
                    Don't have an account? 
                    <a href="{{ route('register') }}" onclick="event.preventDefault(); switchAuthMode('register');" class="text-amber-400 font-bold hover:underline ml-1">
                        Sign up!
                    </a>
                </p>
            </div>

        </div>

        <!-- PANEL 2: REGISTRATION FORM (In This Current Design) -->
        <div id="auth-box-register" class="relative rounded-2xl bg-[#0e1626]/90 backdrop-blur-xl border border-slate-800/90 p-5 sm:p-7 shadow-[0_20px_50px_rgba(0,0,0,0.8),0_0_30px_rgba(245,158,11,0.06)] hidden">
            
            <!-- Brand Logo Emblem -->
            <div class="text-center mb-4">
                <div class="w-14 h-14 mx-auto mb-2 bg-black rounded-xl overflow-hidden flex items-center justify-center shadow-lg border border-slate-800/80">
                    <img src="{{ asset('images/logo.png') }}" alt="Fun 2 Win" class="w-full h-full object-contain">
                </div>
                <h2 class="text-xl sm:text-2xl font-bold font-royal text-white tracking-wide">CREATE PLAYER ACCOUNT</h2>
                <p class="text-xs text-slate-400 mt-0.5">Register for exclusive access to live tables. Admin approval required.</p>
            </div>

            <!-- Errors Banner (Register) -->
            @if($errors->any() && !$errors->has('login'))
                <div class="p-3.5 rounded-xl bg-red-950/80 border border-red-500/70 text-red-200 text-xs mb-4 flex items-start gap-2">
                    <span class="text-sm shrink-0">⚠️</span>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Registration Form -->
            <form action="{{ route('register.post') }}" method="POST" id="player-register-form" class="needs-validation space-y-3" data-validate="true" autocomplete="off">
                @csrf

                <!-- Decoy inputs to prevent browser autofilling saved login credentials -->
                <div style="position: absolute; opacity: 0; pointer-events: none; height: 0; width: 0; overflow: hidden;" aria-hidden="true">
                    <input type="text" name="decoy_username" tabindex="-1" autocomplete="username">
                    <input type="password" name="decoy_password" tabindex="-1" autocomplete="new-password">
                </div>

                <!-- Row 1: Full Name | Username -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="reg-name" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="reg-name" name="name" value="{{ old('name') }}" required placeholder="e.g. Rahul Sharma"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('name') border-red-500 @enderror">
                    </div>

                    <div>
                        <label for="reg-username" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="reg-username" name="username" value="{{ old('username') }}" data-type="username" required placeholder="e.g. rahul_king" autocomplete="off"
                               oninput="this.dataset.userTyped='true'"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('username') border-red-500 @enderror">
                    </div>
                </div>

                <!-- Row 2: Mobile | Email(non mandatory) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="reg-mobile" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            Mobile <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" id="reg-mobile" name="mobile" value="{{ old('mobile') }}" data-type="mobile" required maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); this.dataset.userTyped='true';"
                               placeholder="10-digit number" autocomplete="off"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('mobile') border-red-500 @enderror">
                    </div>

                    <div>
                        <label for="reg-email" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            Email(non mandatory)
                        </label>
                        <input type="email" id="reg-email" name="email" value="{{ old('email') }}" placeholder="e.g. rahul@example.com" autocomplete="off"
                               oninput="this.dataset.userTyped='true'"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('email') border-red-500 @enderror">
                    </div>
                </div>

                <!-- Row 3: Password | Confirm Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="reg-password" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="reg-password" name="password" required placeholder="••••••••" autocomplete="new-password"
                                   oninput="this.dataset.userTyped='true'"
                                   class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl pl-3 pr-10 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('password') border-red-500 @enderror">
                            <button type="button" onclick="togglePasswordVisibility('reg-password', this)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-400 p-1" title="Show/Hide Password" aria-label="Toggle password visibility">
                                <svg class="w-4 h-4 eye-icon-show" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="w-4 h-4 eye-icon-hide hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="reg-password-confirm" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="reg-password-confirm" name="password_confirmation" required placeholder="••••••••" autocomplete="new-password"
                                   oninput="this.dataset.userTyped='true'"
                                   class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl pl-3 pr-10 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('password_confirmation') border-red-500 @enderror">
                            <button type="button" onclick="togglePasswordVisibility('reg-password-confirm', this)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-400 p-1" title="Show/Hide Password" aria-label="Toggle password visibility">
                                <svg class="w-4 h-4 eye-icon-show" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="w-4 h-4 eye-icon-hide hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Row 4: DOB | Address -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="reg-dob" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            DOB
                        </label>
                        <input type="date" id="reg-dob" name="dob" value="{{ old('dob') }}" max="{{ date('Y-m-d') }}"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('dob') border-red-500 @enderror">
                    </div>

                    <div>
                        <label for="reg-address" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            Address
                        </label>
                        <input type="text" id="reg-address" name="address" value="{{ old('address') }}" placeholder="Street, landmark, pincode"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('address') border-red-500 @enderror">
                    </div>
                </div>

                <!-- Row 5: City | State -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="reg-city" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            City
                        </label>
                        <input type="text" id="reg-city" name="city" value="{{ old('city') }}" placeholder="e.g. Mumbai"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('city') border-red-500 @enderror">
                    </div>

                    <div>
                        <label for="reg-state" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            State
                        </label>
                        <input type="text" id="reg-state" name="state" value="{{ old('state') }}" placeholder="e.g. Maharashtra"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('state') border-red-500 @enderror">
                    </div>
                </div>

                <!-- Row 6: Country | Required verification/ KYC details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="reg-country" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            Country
                        </label>
                        <input type="text" id="reg-country" name="country" value="{{ old('country', 'India') }}" placeholder="Country"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('country') border-red-500 @enderror">
                    </div>

                    <div>
                        <label for="reg-kyc" class="block text-[11px] font-semibold tracking-wider text-slate-300 mb-1">
                            Required verification/ KYC details
                        </label>
                        <input type="text" id="reg-kyc" name="kyc_info" value="{{ old('kyc_info') }}" placeholder="Aadhaar / PAN / ID number"
                               class="w-full bg-[#0a101d] border border-slate-700/60 rounded-xl px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition @error('kyc_info') border-red-500 @enderror">
                    </div>
                </div>

                <!-- Row 7: Terms and conditions acceptance -->
                <div class="pt-1">
                    <label class="flex items-start gap-2 cursor-pointer text-[11px] text-slate-300">
                        <input type="checkbox" id="reg-terms" name="terms" value="1" class="mt-0.5 rounded text-amber-500 focus:ring-amber-500 bg-slate-900 border-slate-700">
                        <span>Terms and conditions acceptance (I confirm that I am at least 18 years old and agree to the Terms of Service. Admin approval required.)</span>
                    </label>
                    <div id="reg-terms-error" class="text-red-400 text-xs font-semibold mt-1 {{ $errors->has('terms') ? '' : 'hidden' }}">
                        {{ $errors->first('terms') ?: 'Please accept the Terms and Conditions before submitting.' }}
                    </div>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl font-black text-xs uppercase tracking-widest text-slate-950 bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500 hover:from-amber-400 hover:to-yellow-300 shadow-lg shadow-amber-500/25 transition duration-200 mt-2">
                    SUBMIT REGISTRATION
                </button>
            </form>

            <!-- Already have an account?  -->
            <div class="text-center mt-3.5 pt-2.5 border-t border-slate-800/60">
                <p class="text-xs text-slate-400">
                    Already have an account? 
                    <a href="{{ route('login') }}" onclick="event.preventDefault(); switchAuthMode('login');" class="text-amber-400 font-bold hover:underline ml-1">
                        Login
                    </a>
                </p>
            </div>

        </div>

        <!-- PANEL 3: FORGET PASSWORD / CHANGE PASSWORD BOX (Exact Style of Image 1) -->
        <div id="auth-box-forgot" class="relative rounded-2xl bg-[#0e1626]/95 backdrop-blur-xl border border-slate-800/90 p-5 sm:p-7 shadow-[0_20px_50px_rgba(0,0,0,0.8),0_0_30px_rgba(245,158,11,0.06)] hidden">
            <!-- Tabs Buttons & Close Button matching Image 1 -->
            <div class="flex items-center justify-between border-b border-slate-800/90 pb-3 sm:pb-4 mb-4 sm:mb-5">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-slate-900/90 text-slate-400 border border-slate-800 opacity-60 select-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>Change Username</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-amber-500 text-slate-950 shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <span>Change Password</span>
                    </div>
                </div>
                <button type="button" onclick="switchAuthMode('login')" class="p-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white transition text-xs font-bold flex items-center justify-center cursor-pointer" title="Close">
                    ✕
                </button>
            </div>

            <!-- Title & Subtitle matching Image 1 -->
            <div class="mb-4">
                <h3 class="text-sm sm:text-base font-bold font-royal text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    CHANGE PASSWORD
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Enter and confirm your new password below.</p>
            </div>

            <!-- Errors Banner (Forgot Password) -->
            @if($errors->has('reset_login') || (session('active_tab') === 'forgot' && $errors->any()))
                <div class="p-3 rounded-xl bg-red-950/80 border border-red-500/70 text-red-200 text-xs mb-4 flex items-start gap-2">
                    <span class="text-sm shrink-0">⚠️</span>
                    <ul class="list-disc list-inside space-y-0.5">
                        @if($errors->has('reset_login'))
                            <li>{{ $errors->first('reset_login') }}</li>
                        @endif
                        @foreach($errors->get('login') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                        @foreach($errors->get('password') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('password.reset.post') }}" method="POST" class="space-y-4" autocomplete="off">
                @csrf

                <!-- Username or Mobile to identify account -->
                <div>
                    <label for="forgot-login" class="text-slate-300 text-xs font-semibold block mb-1.5">
                        Username or Mobile Number <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="forgot-login" name="login" value="{{ old('login') }}" required 
                           placeholder="Enter your username or 10-digit mobile"
                           class="w-full px-4 py-2.5 sm:py-3 rounded-xl bg-[#0a101d] border border-slate-700/60 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">
                </div>

                <!-- New Password matching Image 1 -->
                <div>
                    <label for="forgot-new-password" class="text-slate-300 text-xs font-semibold block mb-1.5">
                        New Password <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="forgot-new-password" name="password" required minlength="6" placeholder="Enter new password"
                               class="w-full px-4 pr-11 py-2.5 sm:py-3 rounded-xl bg-[#0a101d] border border-slate-700/60 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" autocomplete="new-password">
                        <button type="button" onclick="togglePasswordVisibility('forgot-new-password', this)" style="position:absolute;top:50%;right:0.75rem;transform:translateY(-50%);" class="text-slate-400 hover:text-amber-400 focus:outline-none p-1 transition" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg class="w-5 h-5 eye-icon-show" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-5 h-5 eye-icon-hide hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password matching Image 1 -->
                <div>
                    <label for="forgot-confirm-password" class="text-slate-300 text-xs font-semibold block mb-1.5">
                        Confirm Password <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="forgot-confirm-password" name="password_confirmation" required minlength="6" placeholder="Confirm new password"
                               class="w-full px-4 pr-11 py-2.5 sm:py-3 rounded-xl bg-[#0a101d] border border-slate-700/60 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" autocomplete="new-password">
                        <button type="button" onclick="togglePasswordVisibility('forgot-confirm-password', this)" style="position:absolute;top:50%;right:0.75rem;transform:translateY(-50%);" class="text-slate-400 hover:text-amber-400 focus:outline-none p-1 transition" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg class="w-5 h-5 eye-icon-show" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-5 h-5 eye-icon-hide hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Buttons: Cancel on left side, SAVE PASSWORD on right side -->
                <div class="flex items-center justify-between gap-3 pt-2">
                    <button type="button" onclick="switchAuthMode('login')" class="px-5 py-2.5 sm:py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition border border-slate-700 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 sm:py-3 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-amber-500/20 cursor-pointer">
                        SAVE PASSWORD
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>

@if(session('approved_status'))
<script>
    // User is logged in and just saw the approved message — auto-redirect to dashboard
    setTimeout(function () {
        window.location.href = '{{ route('dashboard') }}';
    }, 3000);
</script>
@endif

<script>
    function togglePasswordVisibility(inputId, btn) {
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

    function clearLoginFields() {
        // Keep credentials intact as requested
    }

    function clearRegisterAutofill() {
        @if(!$errors->any())
            const ids = ['reg-name', 'reg-username', 'reg-mobile', 'reg-email', 'reg-password', 'reg-password-confirm', 'reg-dob', 'reg-address', 'reg-city', 'reg-state', 'reg-kyc'];
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (el && !el.dataset.userTyped) {
                    el.value = '';
                }
            });
            const terms = document.getElementById('reg-terms');
            if (terms) terms.checked = false;
        @endif
    }

    function switchAuthMode(mode) {
        const loginCard = document.getElementById('auth-box-login');
        const registerCard = document.getElementById('auth-box-register');
        const forgotCard = document.getElementById('auth-box-forgot');
        if (!loginCard || !registerCard) return;

        if (mode === 'register') {
            loginCard.classList.add('hidden');
            if (forgotCard) forgotCard.classList.add('hidden');
            registerCard.classList.remove('hidden');
            clearRegisterAutofill();
            try {
                window.history.pushState({ mode: 'register' }, '', '{{ route('register') }}');
            } catch (e) {}
        } else if (mode === 'forgot') {
            loginCard.classList.add('hidden');
            registerCard.classList.add('hidden');
            if (forgotCard) {
                forgotCard.classList.remove('hidden');
                const loginInput = document.getElementById('login');
                const forgotLoginInput = document.getElementById('forgot-login');
                if (loginInput && forgotLoginInput && loginInput.value && !forgotLoginInput.value) {
                    forgotLoginInput.value = loginInput.value;
                }
                const newPassInput = document.getElementById('forgot-new-password');
                if (forgotLoginInput && !forgotLoginInput.value) {
                    forgotLoginInput.focus();
                } else if (newPassInput) {
                    newPassInput.focus();
                }
            }
        } else {
            registerCard.classList.add('hidden');
            if (forgotCard) forgotCard.classList.add('hidden');
            loginCard.classList.remove('hidden');
            try {
                window.history.pushState({ mode: 'login' }, '', '{{ route('login') }}');
            } catch (e) {}
        }
    }

    window.addEventListener('popstate', function () {
        if (window.location.pathname.includes('register')) {
            switchAuthMode('register');
        } else {
            switchAuthMode('login');
        }
    });

    function cleanAllFields() {
        clearRegisterAutofill();
    }

    window.addEventListener('pageshow', function () {
        cleanAllFields();
    });

    document.addEventListener('DOMContentLoaded', function () {
        const dobInput = document.getElementById('reg-dob');
        if (dobInput) {
            dobInput.max = new Date().toISOString().split('T')[0];
        }

        // Terms and conditions validation
        const regForm = document.getElementById('player-register-form');
        const termsCb = document.getElementById('reg-terms');
        const termsErr = document.getElementById('reg-terms-error');

        if (regForm) {
            regForm.addEventListener('submit', function (e) {
                if (termsCb && !termsCb.checked) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (termsErr) {
                        termsErr.classList.remove('hidden');
                    }
                    termsCb.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    termsCb.focus();
                    return false;
                }
            });
        }

        if (termsCb) {
            termsCb.addEventListener('change', function () {
                if (this.checked && termsErr) {
                    termsErr.classList.add('hidden');
                }
            });
        }

        @if(session('active_tab') === 'forgot' || $errors->has('reset_login'))
            switchAuthMode('forgot');
        @elseif($errors->has('name') || $errors->has('username') || $errors->has('mobile') || (old('password_confirmation') && !$errors->has('reset_login')) || old('name') || request()->routeIs('register') || (isset($initialTab) && $initialTab === 'register') || request()->query('tab') === 'register')
            switchAuthMode('register');
        @endif
    });
</script>
@endsection
