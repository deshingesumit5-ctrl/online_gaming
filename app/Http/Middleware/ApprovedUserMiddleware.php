<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApprovedUserMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Admins are exempt from player approval checks
        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($user->status === 'pending') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning_status', 'Your account is awaiting Admin approval. You will be able to go inside after admin approves your request.');
        }

        if (in_array($user->status, ['rejected', 'blocked', 'inactive'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error_status', 'Your account has been deactivated or rejected. Please contact administrator.');
        }

        // Enforce Terms & Conditions agreement acceptance after login
        if (!$request->session()->get('terms_accepted') && 
            !$request->routeIs('terms.*') && 
            !$request->routeIs('logout')) {
            return redirect()->route('terms.agreement');
        }

        return $next($request);
    }
}
