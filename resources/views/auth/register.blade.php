@extends('layouts.app')

@section('title', 'Player Registration - Fun 2 Win')

@section('content')
<div class="max-w-2xl mx-auto my-6">
    <div class="glass-panel p-8 shadow-2xl border-amber-500/20">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Fun 2 Win" class="w-20 h-20 object-contain mx-auto mb-3 drop-shadow-xl">
            <h2 class="text-2xl font-bold font-royal text-white">Create Player Account</h2>
            <p class="text-xs text-slate-400 mt-1">Register for exclusive access to controlled private tables. Admin approval required.</p>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-950/80 border border-red-500 text-red-200 text-xs mb-6">
                <p class="font-bold mb-1">Please correct the following errors:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="needs-validation space-y-4" data-validate="true" autocomplete="off">
            @csrf

            <!-- Decoy inputs to prevent browser autofilling saved login credentials -->
            <div style="position: absolute; opacity: 0; pointer-events: none; height: 0; width: 0; overflow: hidden;" aria-hidden="true">
                <input type="text" name="decoy_username" tabindex="-1" autocomplete="username">
                <input type="password" name="decoy_password" tabindex="-1" autocomplete="new-password">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Row 1: Full Name | Username -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 mb-1">Full Name <span class="text-red-500 font-bold">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Rahul Sharma"
                           class="form-input-custom text-sm @error('name') is-invalid @enderror">
                </div>

                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-300 mb-1">Username <span class="text-red-500 font-bold">*</span></label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" data-type="username" required placeholder="e.g. rahul_king" autocomplete="off"
                           oninput="this.dataset.userTyped='true'"
                           class="form-input-custom text-sm @error('username') is-invalid @enderror">
                </div>

                <!-- Row 2: Mobile | Email(non mandatory) -->
                <div>
                    <label for="mobile" class="block text-xs font-semibold text-slate-300 mb-1">Mobile <span class="text-red-500 font-bold">*</span></label>
                    <input type="tel" id="mobile" name="mobile" value="{{ old('mobile') }}" data-type="mobile" required maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); this.dataset.userTyped='true';"
                           placeholder="10-digit number" autocomplete="off"
                           class="form-input-custom text-sm @error('mobile') is-invalid @enderror">
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1">Email(non mandatory)</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="e.g. rahul@example.com" autocomplete="off"
                           oninput="this.dataset.userTyped='true'"
                           class="form-input-custom text-sm @error('email') is-invalid @enderror">
                </div>

                <!-- Row 3: Password | Confirm Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-1">Password <span class="text-red-500 font-bold">*</span></label>
                    <div class="relative" style="height: 42px;">
                        <input type="password" id="password" name="password" required placeholder="••••••••" autocomplete="new-password"
                               oninput="this.dataset.userTyped='true'; checkPasswordMatch();"
                               class="form-input-custom text-sm pr-10 @error('password') is-invalid @enderror" style="position:absolute;top:0;left:0;right:0;height:42px;">
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 text-slate-400 hover:text-amber-400 p-1" style="top:50%;transform:translateY(-50%);z-index:2;" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg class="w-4 h-4 eye-icon-show" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4 h-4 eye-icon-hide hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1">Confirm Password <span class="text-red-500 font-bold">*</span></label>
                    <div class="relative" style="height: 42px;">
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••" autocomplete="new-password"
                               oninput="this.dataset.userTyped='true'; checkPasswordMatch();"
                               class="form-input-custom text-sm pr-10 @error('password_confirmation') is-invalid @enderror" style="position:absolute;top:0;left:0;right:0;height:42px;">
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute right-3 text-slate-400 hover:text-amber-400 p-1" style="top:50%;transform:translateY(-50%);z-index:2;" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg class="w-4 h-4 eye-icon-show" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4 h-4 eye-icon-hide hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                    <div id="password-match-error" class="text-red-400 text-xs font-semibold mt-1 hidden">Passwords do not match.</div>
                </div>

                <!-- Row 4: DOB | Address -->
                <div>
                    <label for="dob" class="block text-xs font-semibold text-slate-300 mb-1">DOB</label>
                    <input type="date" id="dob" name="dob" value="{{ old('dob') }}" max="{{ date('Y-m-d') }}"
                           class="form-input-custom text-sm @error('dob') is-invalid @enderror">
                </div>

                <div>
                    <label for="address" class="block text-xs font-semibold text-slate-300 mb-1">Address</label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}" placeholder="Street, landmark, pincode"
                           class="form-input-custom text-sm @error('address') is-invalid @enderror">
                </div>

                <!-- Row 5: City | State -->
                <div>
                    <label for="city" class="block text-xs font-semibold text-slate-300 mb-1">City</label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="e.g. Mumbai"
                           class="form-input-custom text-sm @error('city') is-invalid @enderror">
                </div>

                <div>
                    <label for="state" class="block text-xs font-semibold text-slate-300 mb-1">State</label>
                    <input type="text" id="state" name="state" value="{{ old('state') }}" placeholder="e.g. Maharashtra"
                           class="form-input-custom text-sm @error('state') is-invalid @enderror">
                </div>

                <!-- Row 6: Country | Required verification/ KYC details -->
                <div>
                    <label for="country" class="block text-xs font-semibold text-slate-300 mb-1">Country</label>
                    <input type="text" id="country" name="country" value="{{ old('country', 'India') }}"
                           class="form-input-custom text-sm @error('country') is-invalid @enderror">
                </div>

                <div>
                    <label for="kyc_info" class="block text-xs font-semibold text-slate-300 mb-1">Required verification/ KYC details</label>
                    <input type="text" id="kyc_info" name="kyc_info" value="{{ old('kyc_info') }}" placeholder="Aadhaar / PAN / ID number"
                           class="form-input-custom text-sm @error('kyc_info') is-invalid @enderror">
                </div>
            </div>

            <!-- Terms and conditions acceptance -->
            <div class="pt-2">
                <label class="flex items-start gap-3 cursor-pointer text-xs text-slate-300">
                    <input type="checkbox" id="terms" name="terms" value="1" class="mt-0.5 rounded text-amber-500 focus:ring-amber-500 bg-slate-900 border-slate-700">
                    <span>
                        Terms and conditions acceptance (I confirm that I am at least 18 years old, and I agree to the <a href="#" class="text-amber-400 underline">Terms of Service</a> and <a href="#" class="text-amber-400 underline">Gaming Policy</a>. I understand that account access is subject to admin approval.)
                    </span>
                </label>
                <div id="terms-error" class="text-red-400 text-xs font-semibold mt-1.5 {{ $errors->has('terms') ? '' : 'hidden' }}">
                    {{ $errors->first('terms') ?: 'Please accept the Terms and Conditions before submitting.' }}
                </div>
            </div>

            <button type="submit" class="btn-gold w-full py-3.5 text-sm uppercase tracking-wider font-bold mt-4">
                Submit Registration for Approval
            </button>
        </form>

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

            function checkPasswordMatch() {
                const pw = document.getElementById('password');
                const cpw = document.getElementById('password_confirmation');
                const errEl = document.getElementById('password-match-error');
                if (!pw || !cpw || !errEl) return;
                if (cpw.value.length === 0) {
                    errEl.classList.add('hidden');
                    return;
                }
                if (pw.value === cpw.value) {
                    errEl.classList.add('hidden');
                } else {
                    errEl.classList.remove('hidden');
                }
            }

            function clearRegisterAutofill() {
                @if(!$errors->any())
                    const ids = ['username', 'email'];
                    ids.forEach(id => {
                        const el = document.getElementById(id);
                        if (el && !el.dataset.userTyped) {
                            el.value = '';
                        }
                    });
                @endif
            }
            document.addEventListener('DOMContentLoaded', function() {
                const dob = document.getElementById('dob');
                if (dob) dob.max = new Date().toISOString().split('T')[0];

                const regForm = document.querySelector('form');
                const termsCb = document.getElementById('terms');
                const termsErr = document.getElementById('terms-error');

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

                clearRegisterAutofill();
                setTimeout(clearRegisterAutofill, 60);
                setTimeout(clearRegisterAutofill, 200);
            });
            window.addEventListener('load', function() {
                clearRegisterAutofill();
                setTimeout(clearRegisterAutofill, 100);
            });
        </script>

        <div class="text-center mt-6 pt-6 border-t border-slate-800/80">
            <p class="text-xs text-slate-400">
                Already registered? 
                <a href="{{ route('login') }}" class="text-amber-400 font-bold hover:underline ml-1">
                    Login here
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
