<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }

    /**
     * Display the Points Ledger & History page.
     */
    public function transactions(Request $request): View
    {
        $user = Auth::user();

        $query = WalletTransaction::where('user_id', $user->id);

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $transactions = $query->latest('id')->paginate(15)->withQueryString();

        return view('wallet.transactions', compact('user', 'transactions'));
    }

    /**
     * Alias for ledger.
     */
    public function ledger(Request $request): View
    {
        return $this->transactions($request);
    }

    /**
     * Backward-compatible route for /withdraw -> redirects to /withdrawals/create.
     */
    public function showWithdraw(): RedirectResponse
    {
        return redirect()->route('withdrawals.create');
    }
}
