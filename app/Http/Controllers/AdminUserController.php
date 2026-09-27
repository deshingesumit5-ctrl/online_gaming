<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddPointsRequest;
use App\Http\Requests\AdminStoreUserRequest;
use App\Http\Requests\ManualDebitRequest;
use App\Models\User;
use App\Services\UserStatusManager;
use App\Services\WalletService;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public function index(Request $request): View
    {
        $this->ensureAdminCreatedColumn();

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
        } elseif ($tab === 'admin_created') {
            $query->where('created_by_admin', true);
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
            'admin_created' => User::where('role', 'player')->where('created_by_admin', true)->count(),
            'all' => User::where('role', 'player')->count(),
        ];

        return view('admin.users.index', compact('users', 'tab', 'search', 'counts'));
    }

    public function store(AdminStoreUserRequest $request): RedirectResponse
    {
        $this->ensureAdminCreatedColumn();
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => !empty($validated['email']) ? strtolower($validated['email']) : null,
            'mobile' => $validated['mobile'],
            'password' => Hash::make($validated['password']),
            'dob' => $validated['dob'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? 'India',
            'kyc_info' => $validated['kyc_info'] ?? null,
            'status' => 'approved',
            'role' => 'player',
            'created_by_admin' => true,
            'wallet_balance' => 0,
        ]);

        return redirect()->route('admin.users.index', ['tab' => 'admin_created'])->with(
            'success',
            "Player @{$user->username} was added. They can log in with this username and password."
        );
    }

    private function ensureAdminCreatedColumn(): void
    {
        if (Schema::hasColumn('users', 'created_by_admin')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('created_by_admin')->default(false);
        });
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
