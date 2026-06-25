<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Controllers\PrijavaController;
use App\Presentation\Controllers\SankcijaController;
use App\Presentation\Controllers\SuspendController;
use App\Presentation\Controllers\HealthController;

Route::get('health', [HealthController::class, 'index']);

Route::middleware('identity.auth')->group(function () {
    Route::get('prijava', [PrijavaController::class, 'index']);
    Route::post('prijava', [PrijavaController::class, 'store']);
    Route::get('prijava/{id}', [PrijavaController::class, 'show']);
    Route::patch('prijava/{id}/status', [PrijavaController::class, 'updateStatus']);

    Route::get('sankcija', [SankcijaController::class, 'index']);
    Route::post('sankcija', [SankcijaController::class, 'store']);

    Route::patch('korisnik/{id}/suspend', [SuspendController::class, 'suspend']);
});
