<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddPointsRequest;
use App\Http\Requests\ManualDebitRequest;
use App\Http\Requests\WalletAdjustmentRequest;
use App\Models\PointRequest;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminWalletController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }

    /**
     * Display wallet operations: ledger history, pending point requests, manual points adjustments.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'requests'); // 'requests', 'ledger', 'adjust'
        $search = $request->query('search');
        $type = $request->query('type');

        $reqStatus = $request->query('req_status', 'all'); // 'all', 'pending', 'approved', 'rejected'

        // 1. Point Requests (Show all real data and only status changes on approve/reject)
        $requestsQuery = PointRequest::with('user');
        if ($reqStatus === 'pending') {
            $requestsQuery->where('status', 'pending');
        } elseif ($reqStatus === 'approved') {
            $requestsQuery->where('status', 'approved');
        } elseif ($reqStatus === 'rejected') {
            $requestsQuery->where('status', 'rejected');
        }

        if ($search) {
            $requestsQuery->where(function ($q) use ($search) {
                $q->where('request_id', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhere('user_id', $search)
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        $pointRequests = $requestsQuery->latest('created_at')->orderByDesc('id')->paginate(15, ['*'], 'requests_page')->withQueryString();
        $pendingPointRequests = $pointRequests; // keep backward compatibility

        $allPointRequestsCount = PointRequest::count();
        $pendingRequestsCount = PointRequest::where('status', 'pending')->count();
        $approvedRequestsCount = PointRequest::where('status', 'approved')->count();
        $rejectedRequestsCount = PointRequest::where('status', 'rejected')->count();

        // 2. Global Ledger Transactions
        $query = WalletTransaction::with(['user', 'performer']);

        if ($type) {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('transaction_code', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        $transactions = $query->latest('id')->paginate(20, ['*'], 'tx_page')->withQueryString();
        $players = User::where('role', 'player')->orderBy('name')->get(['id', 'name', 'username', 'wallet_balance']);

        return view('admin.wallet.index', compact(
            'pointRequests',
            'pendingPointRequests',
            'pendingRequestsCount',
            'approvedRequestsCount',
            'rejectedRequestsCount',
            'allPointRequestsCount',
            'reqStatus',
            'transactions',
            'players',
            'tab',
            'search',
            'type'
        ));
    }

    /**
     * Approve user point request and credit points.
     */
    public function approvePointRequest(Request $request, int $id): RedirectResponse
    {
        $admin = Auth::user();
        $pointRequest = PointRequest::findOrFail($id);

        try {
            $this->walletService->approvePointRequest($pointRequest, $admin->id);

            return back()->with(
                'success',
                "Point request #{$pointRequest->request_id} for " . number_format($pointRequest->points_requested) . " PTS has been APPROVED! Points added to user @{$pointRequest->user->username}."
            );
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject user point request.
     */
    public function rejectPointRequest(Request $request, int $id): RedirectResponse
    {
        $admin = Auth::user();
        $pointRequest = PointRequest::findOrFail($id);
        $remark = $request->input('rejection_remark', 'Rejected by administrator.');

        try {
            $this->walletService->rejectPointRequest($pointRequest, $remark, $admin->id);

            return back()->with(
                'success',
                "Point request #{$pointRequest->request_id} from @{$pointRequest->user->username} has been REJECTED."
            );
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin manual balance adjustment (Credit or Debit).
     */
    public function adjust(WalletAdjustmentRequest $request): RedirectResponse
    {
        $admin = Auth::user();
        $validated = $request->validated();
        $userId = $validated['user_id'];
        $action = $validated['action_type'];
        $amount = (int) $validated['amount'];
        $remarks = "Admin ({$admin->name}) adjustment: {$validated['remarks']}";

        $targetUser = User::findOrFail($userId);

        try {
            if ($action === 'credit') {
                $this->walletService->addPoints(
                    user: $targetUser,
                    amount: $amount,
                    remarks: $remarks,
                    performedByAdminId: $admin->id,
                    type: 'manual_credit'
                );
                $msg = "Successfully credited {$amount} points to @{$targetUser->username}.";
            } else {
                $this->walletService->manualDebit(
                    user: $targetUser,
                    amount: $amount,
                    remarks: $remarks,
                    adminId: $admin->id
                );
                $msg = "Successfully debited {$amount} points from @{$targetUser->username}.";
            }

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Quick Add Points endpoint (e.g. from User Master modal).
     */
    public function addPoints(AddPointsRequest $request): RedirectResponse
    {
        $admin = Auth::user();
        $validated = $request->validated();
        $targetUser = User::findOrFail($validated['user_id']);
        $amount = (int) $validated['amount'];
        $remarks = "Admin ({$admin->name}) credit: {$validated['remarks']}";

        try {
            $this->walletService->addPoints(
                user: $targetUser,
                amount: $amount,
                remarks: $remarks,
                performedByAdminId: $admin->id,
                type: 'manual_credit'
            );

            return back()->with('success', "Added {$amount} points to @{$targetUser->username}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Quick Deduct Points endpoint (e.g. from User Master modal).
     */
    public function deductPoints(ManualDebitRequest $request): RedirectResponse
    {
        $admin = Auth::user();
        $validated = $request->validated();
        $targetUser = User::findOrFail($validated['user_id']);
        $amount = (int) $validated['amount'];
        $remarks = "Admin ({$admin->name}) debit: {$validated['remarks']}";

        try {
            $this->walletService->manualDebit(
                user: $targetUser,
                amount: $amount,
                remarks: $remarks,
                adminId: $admin->id
            );

            return back()->with('success', "Debited {$amount} points from @{$targetUser->username}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
