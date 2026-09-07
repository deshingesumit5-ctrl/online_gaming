<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        AppNotification::truncate();

        $rohan = User::where('username', 'rohan_14')->first();
        $admin = User::where('role', 'admin')->first();

        if ($rohan) {
            $rohan->password = \Illuminate\Support\Facades\Hash::make('password123');
            $rohan->status = 'approved';
            $rohan->save();

            AppNotification::create([
                'user_id' => $rohan->id,
                'role' => 'player',
                'type' => 'registration',
                'title' => 'Registration',
                'message' => 'Registration submitted successfully. Your account is awaiting Admin approval.',
                'link' => route('dashboard'),
                'icon' => '📝',
                'read_at' => now()->subHours(2),
                'created_at' => now()->subHours(2),
            ]);

            AppNotification::create([
                'user_id' => $rohan->id,
                'role' => 'player',
                'type' => 'account_approval',
                'title' => 'Account Approval',
                'message' => 'Your account has been approved successfully.',
                'link' => route('dashboard'),
                'icon' => '✅',
                'read_at' => now()->subHours(1),
                'created_at' => now()->subHours(1),
            ]);

            AppNotification::create([
                'user_id' => $rohan->id,
                'role' => 'player',
                'type' => 'points_added',
                'title' => 'Points Added',
                'message' => '1,000 points have been added to your wallet.',
                'link' => route('wallet.transactions'),
                'icon' => '🪙',
                'read_at' => now()->subMinutes(45),
                'created_at' => now()->subMinutes(45),
            ]);

            AppNotification::create([
                'user_id' => $rohan->id,
                'role' => 'player',
                'type' => 'bet_confirmation',
                'title' => 'Bet Confirmation',
                'message' => 'Your 500-point bet on ANDAR has been placed successfully.',
                'link' => route('dashboard'),
                'icon' => '🎲',
                'read_at' => now()->subMinutes(20),
                'created_at' => now()->subMinutes(20),
            ]);

            AppNotification::create([
                'user_id' => $rohan->id,
                'role' => 'player',
                'type' => 'game_result',
                'title' => 'Game Result',
                'message' => 'ANDAR WON. You won 500 points.',
                'link' => route('dashboard'),
                'icon' => '🏆',
                'read_at' => null, // Unread, so badge (1) displays!
                'created_at' => now()->subMinutes(5),
            ]);
        }

        if ($admin) {
            AppNotification::create([
                'user_id' => null,
                'role' => 'admin',
                'type' => 'admin_registration',
                'title' => 'New User Registration',
                'message' => 'New user @amit_patel (Amit Patel) submitted registration awaiting approval.',
                'link' => route('admin.users.index', ['tab' => 'pending']),
                'icon' => '👤',
                'read_at' => null, // Unread, so badge (1) displays for admin!
                'created_at' => now()->subMinutes(10),
            ]);

            AppNotification::create([
                'user_id' => null,
                'role' => 'admin',
                'type' => 'admin_game_result',
                'title' => 'Game Result Settled',
                'message' => 'Round #1 settled: ANDAR WON.',
                'link' => route('admin.game.control.index'),
                'icon' => '🎯',
                'read_at' => now()->subMinutes(30),
                'created_at' => now()->subMinutes(30),
            ]);
        }
    }
}
