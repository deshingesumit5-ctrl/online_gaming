<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fun 2 Win</title>
    <!-- Favicon / Logo -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/gaming-theme.css') }}">
</head>
<body class="bg-[#070b14] text-slate-100 min-h-screen flex items-center justify-center p-4 selection:bg-amber-500 selection:text-black">
    <div class="max-w-md w-full glass-panel p-8 shadow-2xl border-amber-500/30">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Fun 2 Win" class="w-20 h-20 object-contain mx-auto mb-3 drop-shadow-xl">
            <h1 class="text-2xl font-bold font-royal text-amber-300">FUN 2 WIN ADMIN</h1>
            <p class="text-xs text-slate-400 mt-1">Authorized Super Administrators Only</p>
        </div>

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-red-950/80 border border-red-500 text-red-200 text-xs mb-5">
                {{ $errors->first() }}
            </div>
        @endif


        <form action="{{ route('admin.login.post') }}" method="POST" class="needs-validation space-y-5" data-validate="true" autocomplete="off">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" 
                       class="form-input-custom text-sm" autocomplete="off">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                <input type="password" id="password" name="password" 
                       class="form-input-custom text-sm" autocomplete="new-password">
            </div>

            <button type="submit" class="btn-gold w-full py-3 text-sm uppercase tracking-wider font-bold mt-2">
                Access Admin Panel
            </button>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if(!$errors->any())
                    const emailInput = document.getElementById('email');
                    const passwordInput = document.getElementById('password');
                    if (emailInput) emailInput.value = '';
                    if (passwordInput) passwordInput.value = '';
                @endif
            });
        </script>

        <div class="text-center mt-6 pt-6 border-t border-slate-800/80">
            <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-amber-400 transition">
                &larr; Switch to Player Sign In
            </a>
        </div>
    </div>
</body>
</html>
