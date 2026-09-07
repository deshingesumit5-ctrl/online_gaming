<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddPointsRequest;
use App\Http\Requests\ManualDebitRequest;
use App\Models\User;
use App\Services\UserStatusManager;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'all');
        $search = $request->query('search');

        $query = User::where('role', 'player');

        if ($tab === 'pending') {
            $query->where('status', 'pending');
        } elseif ($tab === 'active') {
            $query->whereIn('status', ['active', 'approved']);
        } elseif ($tab === 'inactive') {
            $query->where('status', 'inactive');
        } elseif ($tab === 'blocked') {
            $query->whereIn('status', ['blocked', 'rejected']);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        $users = $query->latest('created_at')->orderByDesc('id')->paginate(15)->withQueryString();

        $counts = [
            'pending' => User::where('role', 'player')->where('status', 'pending')->count(),
            'active' => User::where('role', 'player')->whereIn('status', ['active', 'approved'])->count(),
            'inactive' => User::where('role', 'player')->where('status', 'inactive')->count(),
            'blocked' => User::where('role', 'player')->whereIn('status', ['blocked', 'rejected'])->count(),
            'all' => User::where('role', 'player')->count(),
        ];

        return view('admin.users.index', compact('users', 'tab', 'search', 'counts'));
    }

    public function show(int $id): View
    {
        $user = User::with([
            'bets' => function ($q) {
                $q->with('round.room')->latest()->take(25);
            },
            'walletTransactions' => function ($q) {
                $q->latest()->take(25);
            },
            'withdrawals' => function ($q) {
                $q->latest()->take(15);
            },
            'pointRequests' => function ($q) {
                $q->latest()->take(15);
            }
        ])->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:approved,active,pending,rejected,blocked,inactive'],
            'tab' => ['nullable', 'string'],
        ]);

        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot alter status of administrator accounts.');
        }

        try {
            UserStatusManager::transition($user, $request->status);

            if (in_array($request->status, ['approved', 'active'])) {
                app(\App\Services\NotificationService::class)->sendToUser(
                    user: $user,
                    type: 'account_approval',
                    title: 'Account Approval',
                    message: 'Your account has been approved successfully.',
                    link: route('dashboard')
                );
            }
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $actionMessages = [
            'approved' => "Player @{$user->username} has been APPROVED! They can now log in and play.",
            'active' => "Player @{$user->username} status set to ACTIVE.",
            'rejected' => "Player @{$user->username} registration REJECTED.",
            'blocked' => "Player @{$user->username} has been BLOCKED.",
            'inactive' => "Player @{$user->username} set to INACTIVE.",
        ];

        $msg = $actionMessages[$request->status] ?? "Player @{$user->username} status updated to {$request->status}.";

        $tab = $request->input('tab', 'all');

        return redirect()->route('admin.users.index', ['tab' => $tab])->with('success', $msg);
    }

    /**
     * Add points to a specific user from modal.
     */
    public function addPoints(AddPointsRequest $request): RedirectResponse
    {
        $admin = Auth::user();
        $validated = $request->validated();
        $targetUser = User::findOrFail($validated['user_id']);
        $amount = (int) $validated['amount'];
        $remarks = "Admin ({$admin->name}) manual credit: {$validated['remarks']}";

        try {
            $this->walletService->addPoints(
                user: $targetUser,
                amount: $amount,
                remarks: $remarks,
                performedByAdminId: $admin->id,
                type: 'manual_credit'
            );

            return back()->with('success', "Successfully added {$amount} points to @{$targetUser->username}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Deduct points from a specific user from modal.
     */
    public function deductPoints(ManualDebitRequest $request): RedirectResponse
    {
        $admin = Auth::user();
        $validated = $request->validated();
        $targetUser = User::findOrFail($validated['user_id']);
        $amount = (int) $validated['amount'];
        $remarks = "Admin ({$admin->name}) manual debit: {$validated['remarks']}";

        try {
            $this->walletService->manualDebit(
                user: $targetUser,
                amount: $amount,
                remarks: $remarks,
                adminId: $admin->id
            );

            return back()->with('success', "Successfully debited {$amount} points from @{$targetUser->username}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
