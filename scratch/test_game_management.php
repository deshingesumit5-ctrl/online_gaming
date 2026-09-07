<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Game;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$admin = User::where('role', 'admin')->firstOrFail();
Auth::login($admin);

$controller = app(\App\Http\Controllers\AdminGameManagementController::class);

// 1. Test Game update
$game = Game::first();
$req = Request::create('/admin/games/' . $game->id, 'PUT', [
    'name' => $game->name,
    'status' => 'open',
    'start_time' => now()->format('Y-m-d\TH:i'),
]);
$controller->updateGame($req, $game->id);
echo "Game Update OK!\n";

// 2. Test Game Toggle
$controller->toggleGameStatus($game->id);
echo "Game Toggle 1 OK! Status: " . $game->fresh()->status . "\n";
$controller->toggleGameStatus($game->id);
echo "Game Toggle 2 OK! Status: " . $game->fresh()->status . "\n";

// 3. Test Room Update
$room = Room::first();
$roomReq = Request::create('/admin/rooms/' . $room->id, 'PUT', [
    'name' => $room->name,
    'betting_duration' => 30,
    'cancellation_duration' => 10,
    'allowed_denominations' => '100, 500, 1000, 2000, 5000',
    'live_stream_url' => $room->live_stream_url,
    'status' => 'live',
    'start_time' => null,
]);
$controller->updateRoom($roomReq, $room->id);
echo "Room Update OK! Allowed denoms: " . json_encode($room->fresh()->allowed_denominations) . "\n";

echo "ALL CONTROLLER ACTIONS TESTED WITH ZERO ERRORS!\n";
