<?php

use App\Http\Controllers\CardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    Route::apiResource('cards', CardController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('api.cards');
});
