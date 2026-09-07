<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\WalletService;
use App\Models\User;
use App\Exceptions\InsufficientBalanceException;

$service = app(WalletService::class);
$user = User::where('username', 'sumit_10')->firstOrFail();
echo "Initial balance: {$user->wallet_balance}\n";

// 1. Point request
$req = $service->createPointRequest($user, 1500, 'Test request 1500');
echo "Created point request #{$req->request_id}, status: {$req->status}\n";

// 2. Approve point request
$admin = User::where('role', 'admin')->firstOrFail();
$txn = $service->approvePointRequest($req, $admin->id);
$user->refresh();
echo "After approval balance: {$user->wallet_balance} (Txn: {$txn->transaction_id})\n";

// 3. Create withdrawal
$wd = $service->createWithdrawal($user, 1000, 'UPI: test@upi');
echo "Created withdrawal #{$wd->request_id}, user balance: {$user->wallet_balance}, available: {$user->available_balance}\n";

// 4. Process withdrawal
$wdTxn = $service->processWithdrawal($wd, $admin->id);
$user->refresh();
echo "After withdrawal processed balance: {$user->wallet_balance} (Txn: {$wdTxn->transaction_id})\n";

// 5. Test InsufficientBalanceException
try {
    $service->deductPoints($user, 999999, 'Overdraw test');
    echo "ERROR: should have failed!\n";
} catch (InsufficientBalanceException $e) {
    echo "SUCCESS: Caught expected InsufficientBalanceException: {$e->getMessage()}\n";
}

echo "All WalletService tests completed successfully!\n";
