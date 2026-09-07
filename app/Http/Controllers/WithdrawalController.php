<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Requests\WithdrawalRequestRequest;
use App\Models\Withdrawal;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }

    /**
     * Display list of user's withdrawals.
     */
    public function index(): View
    {
        $user = Auth::user();
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('withdrawals.index', compact('user', 'withdrawals'));
    }

    /**
     * Show withdrawal request form.
     */
    public function create(): View
    {
        $user = Auth::user();
        $recentWithdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('withdrawals.create', compact('user', 'recentWithdrawals'));
    }

    /**
     * Store new withdrawal request.
     */
    public function store(WithdrawalRequestRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $amount = (int) $validated['amount'];

        try {
            $withdrawal = $this->walletService->createWithdrawal(
                user: $user,
                amount: $amount,
                settlementDetails: $validated['settlement_details']
            );

            return redirect()->route('withdrawals.index')->with(
                'success_status',
                "Withdrawal request #{$withdrawal->request_id} for " . number_format($amount) . " PTS submitted successfully. Awaiting admin settlement."
            );
        } catch (InsufficientBalanceException $e) {
            return back()->withInput()->withErrors([
                'amount' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            return back()->withInput()->with('error_status', $e->getMessage());
        }
    }
}
