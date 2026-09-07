<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameRound;
use App\Models\PointRequest;
use App\Models\Room;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Administrator',
                'username' => 'admin',
                'mobile' => '9307324546',
                'password' => Hash::make('admin123'),
                'status' => 'active',
                'role' => 'admin',
                'wallet_balance' => 0,
                'country' => 'India',
            ]
        );

        // 2. Player from screenshot: sumit_10
        $sumit = User::firstOrCreate(
            ['username' => 'sumit_10'],
            [
                'name' => 'Sumit Deshinge',
                'email' => 'deshingesumit5@gmail.com',
                'mobile' => '9822334455',
                'password' => Hash::make('password123'),
                'status' => 'approved',
                'role' => 'player',
                'wallet_balance' => 5000,
                'country' => 'India',
                'kyc_info' => 'UPI ID: sumit@okhdfcbank | SBI A/C: 30123456789 (IFSC: SBIN0001234)',
                'last_login_at' => now(),
            ]
        );

        // Exact transaction from reference screenshot
        $txn = WalletTransaction::firstOrCreate(
            ['transaction_id' => 'ADJ-20260906071108-CQ7G8'],
            [
                'transaction_code' => 'ADJ-20260906071108-CQ7G8',
                'user_id' => $sumit->id,
                'type' => 'manual_credit',
                'amount' => 5000,
                'balance_after' => 5000,
                'previous_balance' => 0,
                'updated_balance' => 5000,
                'remarks' => 'Admin (Super Administrator) adjustment: For testing',
                'performed_by' => $admin->id,
                'created_at' => '2026-09-06 07:11:08',
            ]
        );

        // 3. Sample Approved Player: Rahul Sharma
        $player = User::firstOrCreate(
            ['email' => 'player@gmail.com'],
            [
                'name' => 'Rahul Sharma',
                'username' => 'rahul_player',
                'mobile' => '9876543210',
                'password' => Hash::make('password123'),
                'dob' => '1995-05-12',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'kyc_info' => 'HDFC Bank A/C: 50100123456789 | IFSC: HDFC0001234 | UPI: rahul@okhdfcbank',
                'status' => 'approved',
                'role' => 'player',
                'wallet_balance' => 10000,
            ]
        );

        if ($player->walletTransactions()->count() === 0) {
            $depTxnId = WalletTransaction::generateId('DEP');
            WalletTransaction::create([
                'transaction_id' => $depTxnId,
                'transaction_code' => $depTxnId,
                'user_id' => $player->id,
                'type' => 'points_added',
                'amount' => 10000,
                'balance_after' => 10000,
                'previous_balance' => 0,
                'updated_balance' => 10000,
                'remarks' => 'Welcome Deposit Points credited',
                'performed_by' => $admin->id,
            ]);
        }

        // 4. Sample Pending Player (Approval Gate)
        User::firstOrCreate(
            ['email' => 'amit@gmail.com'],
            [
                'name' => 'Amit Patel',
                'username' => 'amit_patel',
                'mobile' => '9876543211',
                'password' => Hash::make('password123'),
                'dob' => '1998-09-20',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'country' => 'India',
                'kyc_info' => 'ICICI Bank A/C: 001201987654 | IFSC: ICIC0000012',
                'status' => 'pending',
                'role' => 'player',
                'wallet_balance' => 0,
            ]
        );

        // 5. Default Game & Live Room
        $game = Game::firstOrCreate(
            ['name' => 'Royal Andar Bahar'],
            [
                'status' => 'open',
                'created_by' => $admin->id,
            ]
        );

        $room = Room::firstOrCreate(
            ['name' => 'VIP Lounge Table 1'],
            [
                'game_id' => $game->id,
                'live_stream_url' => null,
                'betting_duration' => 30,
                'cancellation_duration' => 30,
                'allowed_denominations' => [100, 500, 1000, 1500, 2000],
                'status' => 'live',
            ]
        );

        if ($room->rounds()->count() === 0) {
            GameRound::create([
                'room_id' => $room->id,
                'round_number' => 1,
                'first_card' => 'king_hearts',
                'status' => 'open',
            ]);
        }

        // 6. Sample Pending Point Request for testing
        if (PointRequest::where('status', 'pending')->count() === 0) {
            PointRequest::create([
                'request_id' => PointRequest::generateRequestId(),
                'user_id' => $sumit->id,
                'current_balance_at_request' => 5000,
                'points_requested' => 2000,
                'remarks' => 'UPI Ref: 429182910293 paid via GPay',
                'status' => 'pending',
            ]);
        }

        // 7. Sample Pending Withdrawal for testing
        if (Withdrawal::where('status', 'pending')->count() === 0) {
            Withdrawal::create([
                'request_id' => Withdrawal::generateRequestId(),
                'user_id' => $player->id,
                'available_balance_at_request' => 10000,
                'amount_requested' => 1500,
                'amount' => 1500,
                'settlement_details' => 'HDFC A/C: 50100123456789 | IFSC: HDFC0001234',
                'status' => 'pending',
            ]);
        }

        // 8. Notifications Seeder
        $this->call(NotificationSeeder::class);
    }
}
