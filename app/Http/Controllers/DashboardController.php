<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\PointRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $games = Game::with(['rooms' => function ($query) {
            $query->with('currentRound')->orderBy('id', 'asc');
        }])->where('status', '!=', 'closed')->get();

        $recentBets = $user->bets()
            ->with(['round.room.game'])
            ->latest()
            ->take(5)
            ->get();

        $recentTransactions = $user->walletTransactions()
            ->latest()
            ->take(5)
            ->get();

        $recentWithdrawals = $user->withdrawals()
            ->latest()
            ->take(5)
            ->get();

        $recentPointRequests = PointRequest::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $liveRoomsCount = Room::where('status', 'live')->count();

        return view('dashboard', compact('user', 'games', 'recentBets', 'recentTransactions', 'recentWithdrawals', 'recentPointRequests', 'liveRoomsCount'));
    }

    public function profile(): View
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    public function updateUsername(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'username' => [
                'required',
                'min:3',
                'max:25',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
        ], [
            'username.required' => 'Please provide a new username.',
            'username.unique' => 'This username has already been taken. Please choose another one.',
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'username.min' => 'Username must be at least 3 characters long.',
            'username.max' => 'Username cannot exceed 25 characters.',
        ]);

        $user->username = strtolower(trim($validated['username']));
        $user->save();

        return back()->with('success_status', "Username updated successfully! Your new login username is @{$user->username}.");
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'Confirm password does not match.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success_status', 'Password updated successfully! You can now log in with your new password.');
    }
}
