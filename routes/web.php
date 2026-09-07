<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminGameController;
use App\Http\Controllers\AdminGameManagementController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminWalletController;
use App\Http\Controllers\AdminWithdrawalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PointRequestController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    // Player Auth
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Admin Auth (redirect /admin/login to /login)
    Route::match(['get', 'post'], '/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
});

// Public Terms & Privacy Policy view
Route::get('/terms-and-conditions', [AuthController::class, 'showTermsAgreement'])->name('terms.public');
Route::get('/privacy-policy', [AuthController::class, 'showTermsAgreement'])->name('privacy.public');

// Global Logout (Instant GET / POST support, failsafe)
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
Route::match(['get', 'post'], '/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Notifications Routes (Player and Admin shared)
Route::middleware('auth')->group(function () {
    Route::get('/notifications/recent', [NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

// Player Routes (Protected by Auth & ApprovedUserMiddleware)
Route::middleware(['auth', 'approved'])->group(function () {
    // Post-Login Terms & Agreement Routes
    Route::get('/terms-agreement', [AuthController::class, 'showTermsAgreement'])->name('terms.agreement');
    Route::post('/terms-agreement/accept', [AuthController::class, 'acceptTermsAgreement'])->name('terms.accept');
    Route::post('/terms-agreement/decline', [AuthController::class, 'declineTermsAgreement'])->name('terms.decline');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::post('/profile/username', [DashboardController::class, 'updateUsername'])->name('profile.username.update');
    Route::post('/profile/password', [DashboardController::class, 'updatePassword'])->name('profile.password.update');

    // Game Room & Live Play
    Route::get('/game/{roomId}', [GameController::class, 'play'])->name('game.play');
    Route::get('/game/{roomId}/state', [GameController::class, 'getState'])->name('game.state');
    Route::post('/game/{roomId}/bet', [GameController::class, 'placeBet'])->name('game.bet');
    Route::post('/game/bet/{betId}/cancel', [GameController::class, 'cancelBet'])->name('game.cancel.bet');

    // Points Ledger
    Route::get('/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');
    Route::get('/ledger', [WalletController::class, 'transactions'])->name('wallet.ledger');

    // Request Points Flow (User side)
    Route::get('/point-requests', [PointRequestController::class, 'index'])->name('points.request');
    Route::get('/points/request', [PointRequestController::class, 'index'])->name('points.request.alias');
    Route::post('/point-requests', [PointRequestController::class, 'store'])->name('points.request.store');

    // Withdrawals Flow (User side)
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/create', [WithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::get('/withdraw', [WithdrawalController::class, 'create'])->name('wallet.withdraw');
});

// Admin Protected Routes (Protected by Auth & AdminMiddleware)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Master & Approvals
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{id}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');
    Route::post('/users/{id}/points/add', [AdminUserController::class, 'addPoints'])->name('users.points.add');
    Route::post('/users/{id}/points/deduct', [AdminUserController::class, 'deductPoints'])->name('users.points.deduct');

    // Points Management & Manual Adjustments & Points Requests Approvals
    Route::get('/wallet', [AdminWalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/adjust', [AdminWalletController::class, 'adjust'])->name('wallet.adjust');
    Route::post('/wallet/point-requests/{id}/approve', [AdminWalletController::class, 'approvePointRequest'])->name('wallet.point-requests.approve');
    Route::post('/wallet/point-requests/{id}/reject', [AdminWalletController::class, 'rejectPointRequest'])->name('wallet.point-requests.reject');

    // Live Game Control Panel
    Route::get('/game-control', [AdminGameController::class, 'roomsList'])->name('game.control.index');
    Route::get('/game-control/{roomId}', [AdminGameController::class, 'controlPanel'])->name('game.control');
    Route::post('/game-control/{roomId}/action', [AdminGameController::class, 'handleAction'])->name('game.action');

    // Game & Room Management (Specification Section 33)
    Route::get('/games', [AdminGameManagementController::class, 'index'])->name('games.index');
    Route::post('/games', [AdminGameManagementController::class, 'storeGame'])->name('games.store');
    Route::put('/games/{id}', [AdminGameManagementController::class, 'updateGame'])->name('games.update');
    Route::post('/games/{id}/toggle', [AdminGameManagementController::class, 'toggleGameStatus'])->name('games.toggle');

    Route::post('/games/{gameId}/rooms', [AdminGameManagementController::class, 'storeRoom'])->name('rooms.store');
    Route::put('/rooms/{id}', [AdminGameManagementController::class, 'updateRoom'])->name('rooms.update');
    Route::post('/rooms/{id}/toggle', [AdminGameManagementController::class, 'toggleRoomStatus'])->name('rooms.toggle');

    // Withdrawals Settlement
    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals/{id}/process', [AdminWithdrawalController::class, 'process'])->name('withdrawals.process');
    Route::post('/withdrawals/{id}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Reports & Analytics
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
});
