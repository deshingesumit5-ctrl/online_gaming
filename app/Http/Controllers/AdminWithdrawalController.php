<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectWithdrawalRequest;
use App\Models\Withdrawal;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminWithdrawalController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search');

        $query = Withdrawal::with(['user', 'processor']);

        if ($status !== 'all') {
            if ($status === 'approved') {
                $query->whereIn('status', ['approved', 'processed', 'settled']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('request_id', 'like', "%{$search}%")
                    ->orWhere('settlement_details', 'like', "%{$search}%")
                    ->orWhere('rejection_remark', 'like', "%{$search}%")
                    ->orWhere('user_id', $search)
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        $withdrawals = $query->latest('id')->paginate(15)->withQueryString();

        $counts = [
            'pending' => Withdrawal::where('status', 'pending')->count(),
            'approved' => Withdrawal::whereIn('status', ['approved', 'processed', 'settled'])->count(),
            'rejected' => Withdrawal::where('status', 'rejected')->count(),
            'all' => Withdrawal::count(),
        ];

        return view('admin.withdrawals.index', compact('withdrawals', 'status', 'search', 'counts'));
    }

    /**
     * Process/Approve withdrawal and deduct points from player wallet.
     */
    public function process(Request $request, int $id): RedirectResponse
    {
        $admin = Auth::user();
        $withdrawal = Withdrawal::findOrFail($id);

        try {
            $this->walletService->processWithdrawal($withdrawal, $admin->id);

            return back()->with(
                'success',
                "Withdrawal request #{$withdrawal->request_id} for " . number_format($withdrawal->amount_requested) . " PTS has been approved. Points debited from user @{$withdrawal->user->username}."
            );
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject withdrawal with reason.
     */
    public function reject(RejectWithdrawalRequest $request, int $id): RedirectResponse
    {
        $admin = Auth::user();
        $withdrawal = Withdrawal::findOrFail($id);
        $validated = $request->validated();

        try {
            $this->walletService->rejectWithdrawal(
                withdrawal: $withdrawal,
                rejectionRemark: $validated['rejection_remark'],
                adminId: $admin->id
            );

            return back()->with(
                'success',
                "Withdrawal #{$withdrawal->request_id} rejected. Reason recorded."
            );
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
