<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\PointRequest;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Exception;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Add points (Deposit / Approved Point Request / Manual Credit).
     */
    public function addPoints(
        User $user,
        int $amount,
        string $remarks,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $performedByAdminId = null,
        string $type = 'points_added'
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new Exception('Point amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $remarks, $referenceType, $referenceId, $performedByAdminId, $type) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $prevBalance = (int) $lockedUser->wallet_balance;
            $newBalance = $prevBalance + $amount;

            $lockedUser->wallet_balance = $newBalance;
            $lockedUser->save();

            $prefix = match ($type) {
                'manual_credit' => 'MC',
                'adjustment' => 'ADJ',
                default => 'DEP',
            };

            $txnId = WalletTransaction::generateId($prefix);

            $txn = WalletTransaction::create([
                'transaction_id' => $txnId,
                'transaction_code' => $txnId,
                'user_id' => $lockedUser->id,
                'type' => $type,
                'amount' => $amount, // Positive for credit
                'balance_after' => $newBalance,
                'previous_balance' => $prevBalance,
                'updated_balance' => $newBalance,
                'remarks' => $remarks,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'performed_by' => $performedByAdminId ?: $lockedUser->id,
            ]);

            // Send notification for points added
            try {
                app(\App\Services\NotificationService::class)->sendToUser(
                    user: $lockedUser,
                    type: 'points_added',
                    title: 'Points Added',
                    message: number_format($amount) . ' points have been added to your wallet.',
                    link: route('wallet.transactions')
                );
            } catch (\Throwable $e) {
                // Ignore notification failure to ensure transaction integrity
            }

            return $txn;
        });
    }

    /**
     * Deduct points for a bet.
     */
    public function deductPoints(
        User $user,
        int $amount,
        string $remarks,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new Exception('Deduction amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $remarks, $referenceType, $referenceId) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $currentBalance = (int) $lockedUser->wallet_balance;
            if ($currentBalance < $amount) {
                throw new InsufficientBalanceException(
                    "Insufficient points balance. You have {$currentBalance} PTS, but {$amount} PTS is required.",
                    $currentBalance,
                    $amount
                );
            }

            $newBalance = $currentBalance - $amount;
            $lockedUser->wallet_balance = $newBalance;
            $lockedUser->save();

            $txnId = WalletTransaction::generateId('BET');

            return WalletTransaction::create([
                'transaction_id' => $txnId,
                'transaction_code' => $txnId,
                'user_id' => $lockedUser->id,
                'type' => 'bet_deducted',
                'amount' => -$amount, // Negative for debit
                'balance_after' => $newBalance,
                'previous_balance' => $currentBalance,
                'updated_balance' => $newBalance,
                'remarks' => $remarks,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'performed_by' => $lockedUser->id,
            ]);
        });
    }

    /**
     * Refund points when a bet or round is cancelled.
     */
    public function refundPoints(
        User $user,
        int $amount,
        string $remarks,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new Exception('Refund amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $remarks, $referenceType, $referenceId) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $prevBalance = (int) $lockedUser->wallet_balance;
            $newBalance = $prevBalance + $amount;

            $lockedUser->wallet_balance = $newBalance;
            $lockedUser->save();

            $txnId = WalletTransaction::generateId('REF');

            return WalletTransaction::create([
                'transaction_id' => $txnId,
                'transaction_code' => $txnId,
                'user_id' => $lockedUser->id,
                'type' => 'bet_cancelled_refunded',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'previous_balance' => $prevBalance,
                'updated_balance' => $newBalance,
                'remarks' => $remarks,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'performed_by' => $lockedUser->id,
            ]);
        });
    }

    /**
     * Add winnings to user wallet.
     */
    public function addWinnings(
        User $user,
        int $amount,
        string $remarks,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $performedByAdminId = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new Exception('Winning amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $remarks, $referenceType, $referenceId, $performedByAdminId) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $prevBalance = (int) $lockedUser->wallet_balance;
            $newBalance = $prevBalance + $amount;

            $lockedUser->wallet_balance = $newBalance;
            $lockedUser->save();

            $txnId = WalletTransaction::generateId('WIN');

            return WalletTransaction::create([
                'transaction_id' => $txnId,
                'transaction_code' => $txnId,
                'user_id' => $lockedUser->id,
                'type' => 'winning_points_added',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'previous_balance' => $prevBalance,
                'updated_balance' => $newBalance,
                'remarks' => $remarks,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'performed_by' => $performedByAdminId ?: $lockedUser->id,
            ]);
        });
    }

    /**
     * Manual debit triggered by an admin.
     */
    public function manualDebit(
        User $user,
        int $amount,
        string $remarks,
        ?int $adminId = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new Exception('Debit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $remarks, $adminId) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $currentBalance = (int) $lockedUser->wallet_balance;
            if ($currentBalance < $amount) {
                throw new InsufficientBalanceException(
                    "Cannot debit {$amount} points. User @{$lockedUser->username} only has {$currentBalance} points.",
                    $currentBalance,
                    $amount
                );
            }

            $newBalance = $currentBalance - $amount;
            $lockedUser->wallet_balance = $newBalance;
            $lockedUser->save();

            $txnId = WalletTransaction::generateId('MD');

            return WalletTransaction::create([
                'transaction_id' => $txnId,
                'transaction_code' => $txnId,
                'user_id' => $lockedUser->id,
                'type' => 'manual_debit',
                'amount' => -$amount,
                'balance_after' => $newBalance,
                'previous_balance' => $currentBalance,
                'updated_balance' => $newBalance,
                'remarks' => $remarks,
                'performed_by' => $adminId,
            ]);
        });
    }

    /**
     * Adjust balance (positive or negative).
     */
    public function adjustBalance(
        User $user,
        int $amount,
        string $remarks,
        ?int $adminId = null
    ): WalletTransaction {
        if ($amount === 0) {
            throw new Exception('Adjustment amount cannot be zero.');
        }

        return DB::transaction(function () use ($user, $amount, $remarks, $adminId) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $currentBalance = (int) $lockedUser->wallet_balance;
            $newBalance = $currentBalance + $amount;

            if ($newBalance < 0) {
                throw new InsufficientBalanceException(
                    "Adjustment would result in negative balance. Current: {$currentBalance} PTS, Adjustment: {$amount} PTS.",
                    $currentBalance,
                    abs($amount)
                );
            }

            $lockedUser->wallet_balance = $newBalance;
            $lockedUser->save();

            $txnId = WalletTransaction::generateId('ADJ');

            return WalletTransaction::create([
                'transaction_id' => $txnId,
                'transaction_code' => $txnId,
                'user_id' => $lockedUser->id,
                'type' => 'adjustment',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'previous_balance' => $currentBalance,
                'updated_balance' => $newBalance,
                'remarks' => $remarks,
                'performed_by' => $adminId,
            ]);
        });
    }

    /**
     * User initiates a withdrawal request (balance not deducted yet, but reserved against pending requests).
     */
    public function createWithdrawal(User $user, int $amount, string $settlementDetails): Withdrawal
    {
        if ($amount <= 0) {
            throw new Exception('Withdrawal amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $settlementDetails) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $pendingAmount = (int) Withdrawal::where('user_id', $lockedUser->id)
                ->where('status', 'pending')
                ->sum('amount_requested');

            $availableBalance = (int) $lockedUser->wallet_balance - $pendingAmount;

            if ($availableBalance < $amount) {
                throw new InsufficientBalanceException(
                    "Insufficient available balance. You have {$availableBalance} PTS available for withdrawal ({$pendingAmount} PTS currently in pending requests).",
                    $availableBalance,
                    $amount
                );
            }

            $requestId = Withdrawal::generateRequestId();

            $withdrawal = Withdrawal::create([
                'request_id' => $requestId,
                'user_id' => $lockedUser->id,
                'available_balance_at_request' => $availableBalance,
                'amount_requested' => $amount,
                'amount' => $amount,
                'settlement_details' => $settlementDetails,
                'status' => 'pending',
            ]);

            // Send withdrawal notifications
            try {
                $notifService = app(\App\Services\NotificationService::class);
                $notifService->sendToUser(
                    user: $lockedUser,
                    type: 'withdrawal',
                    title: 'Withdrawal',
                    message: 'Your withdrawal request has been submitted successfully.',
                    link: route('withdrawals.index')
                );

                $notifService->sendToAdmin(
                    type: 'admin_withdrawal',
                    title: 'New Withdrawal Request',
                    message: "New withdrawal request of " . number_format($amount) . " points submitted by @{$lockedUser->username}.",
                    link: route('admin.withdrawals.index', ['status' => 'pending'])
                );
            } catch (\Throwable $e) {
                // Keep transaction safe
            }

            return $withdrawal;
        });
    }

    /**
     * Process/Approve withdrawal and atomically deduct points from user.
     */
    public function processWithdrawal(Withdrawal $withdrawal, int $adminId): WalletTransaction
    {
        return DB::transaction(function () use ($withdrawal, $adminId) {
            $lockedWithdrawal = Withdrawal::where('id', $withdrawal->id)->lockForUpdate()->firstOrFail();

            if (!in_array($lockedWithdrawal->status, ['pending'])) {
                throw new Exception('This withdrawal has already been approved or rejected.');
            }

            $amount = (int) $lockedWithdrawal->amount_requested;
            $lockedUser = User::where('id', $lockedWithdrawal->user_id)->lockForUpdate()->firstOrFail();

            $currentBalance = (int) $lockedUser->wallet_balance;
            if ($currentBalance < $amount) {
                throw new InsufficientBalanceException(
                    "User balance has fallen below requested withdrawal amount. Balance: {$currentBalance} PTS, Requested: {$amount} PTS.",
                    $currentBalance,
                    $amount
                );
            }

            $newBalance = $currentBalance - $amount;
            $lockedUser->wallet_balance = $newBalance;
            $lockedUser->save();

            $txnId = WalletTransaction::generateId('WD');

            $transaction = WalletTransaction::create([
                'transaction_id' => $txnId,
                'transaction_code' => $txnId,
                'user_id' => $lockedUser->id,
                'type' => 'withdrawal',
                'amount' => -$amount,
                'balance_after' => $newBalance,
                'previous_balance' => $currentBalance,
                'updated_balance' => $newBalance,
                'remarks' => "Withdrawal request #{$lockedWithdrawal->request_id} approved and processed",
                'reference_type' => Withdrawal::class,
                'reference_id' => $lockedWithdrawal->id,
                'performed_by' => $adminId,
            ]);

            $lockedWithdrawal->update([
                'status' => 'approved',
                'processed_by' => $adminId,
                'approved_by' => $adminId,
                'processed_at' => now(),
            ]);

            try {
                app(\App\Services\NotificationService::class)->sendToUser(
                    user: $lockedUser,
                    type: 'withdrawal_approved',
                    title: 'Withdrawal Approved',
                    message: "Your withdrawal request of " . number_format($amount) . " points has been approved.",
                    link: route('withdrawals.index')
                );
            } catch (\Throwable $e) {
            }

            return $transaction;
        });
    }

    /**
     * Reject a withdrawal request.
     */
    public function rejectWithdrawal(Withdrawal $withdrawal, string $rejectionRemark, int $adminId): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $rejectionRemark, $adminId) {
            $lockedWithdrawal = Withdrawal::where('id', $withdrawal->id)->lockForUpdate()->firstOrFail();

            if (!in_array($lockedWithdrawal->status, ['pending'])) {
                throw new Exception('This withdrawal request cannot be rejected as it is already ' . $lockedWithdrawal->status);
            }

            $lockedWithdrawal->update([
                'status' => 'rejected',
                'rejection_remark' => $rejectionRemark,
                'rejection_remarks' => $rejectionRemark,
                'processed_by' => $adminId,
                'approved_by' => $adminId,
                'processed_at' => now(),
            ]);

            try {
                app(\App\Services\NotificationService::class)->sendToUser(
                    user: $lockedWithdrawal->user_id,
                    type: 'withdrawal_rejected',
                    title: 'Withdrawal Rejected',
                    message: "Your withdrawal request of " . number_format($lockedWithdrawal->amount_requested) . " points was rejected.",
                    link: route('withdrawals.index')
                );
            } catch (\Throwable $e) {
            }

            return $lockedWithdrawal;
        });
    }

    /**
     * User submits a request for points.
     */
    public function createPointRequest(User $user, int $points, ?string $remarks = null): PointRequest
    {
        if ($points <= 0) {
            throw new Exception('Requested points must be greater than zero.');
        }

        $req = PointRequest::create([
            'request_id' => PointRequest::generateRequestId(),
            'user_id' => $user->id,
            'current_balance_at_request' => (int) $user->wallet_balance,
            'points_requested' => $points,
            'remarks' => $remarks,
            'status' => 'pending',
        ]);

        try {
            app(\App\Services\NotificationService::class)->sendToAdmin(
                type: 'admin_point_request',
                title: 'New Point Request',
                message: "Player @{$user->username} requested " . number_format($points) . " points.",
                link: route('admin.wallet.index', ['tab' => 'requests'])
            );
        } catch (\Throwable $e) {
        }

        return $req;
    }

    /**
     * Admin approves a point request -> points added to user's wallet.
     */
    public function approvePointRequest(PointRequest $pointRequest, int $adminId): WalletTransaction
    {
        return DB::transaction(function () use ($pointRequest, $adminId) {
            $lockedRequest = PointRequest::where('id', $pointRequest->id)->lockForUpdate()->firstOrFail();

            if ($lockedRequest->status !== 'pending') {
                throw new Exception('This point request has already been ' . $lockedRequest->status);
            }

            $user = User::where('id', $lockedRequest->user_id)->firstOrFail();
            $amount = (int) $lockedRequest->points_requested;

            $remarks = "Point Request #{$lockedRequest->request_id} approved by admin";
            if (!empty($lockedRequest->remarks)) {
                $remarks .= " (Note: {$lockedRequest->remarks})";
            }

            $transaction = $this->addPoints(
                user: $user,
                amount: $amount,
                remarks: $remarks,
                referenceType: PointRequest::class,
                referenceId: $lockedRequest->id,
                performedByAdminId: $adminId,
                type: 'points_added'
            );

            $lockedRequest->update([
                'status' => 'approved',
                'processed_by' => $adminId,
                'processed_at' => now(),
            ]);

            return $transaction;
        });
    }

    /**
     * Admin rejects a point request.
     */
    public function rejectPointRequest(PointRequest $pointRequest, ?string $rejectionRemark, int $adminId): PointRequest
    {
        return DB::transaction(function () use ($pointRequest, $rejectionRemark, $adminId) {
            $lockedRequest = PointRequest::where('id', $pointRequest->id)->lockForUpdate()->firstOrFail();

            if ($lockedRequest->status !== 'pending') {
                throw new Exception('This point request has already been ' . $lockedRequest->status);
            }

            $lockedRequest->update([
                'status' => 'rejected',
                'rejection_remark' => $rejectionRemark,
                'processed_by' => $adminId,
                'processed_at' => now(),
            ]);

            return $lockedRequest;
        });
    }
}
