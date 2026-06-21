<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Presentation\Controllers\UlogaController;
use App\Presentation\Controllers\KorisnikController;
use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\StatsController;
use App\Presentation\Controllers\InternalController;

// Auth routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/stats', [StatsController::class, 'index']);

    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load('uloga')
        ], 200);
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('uloga', [UlogaController::class, 'store']);
    Route::put('uloga/{uloga}', [UlogaController::class, 'update']);
    Route::patch('uloga/{uloga}', [UlogaController::class, 'update']);
    Route::delete('uloga/{uloga}', [UlogaController::class, 'destroy']);

    Route::post('korisnik', [KorisnikController::class, 'store']);
    Route::put('korisnik/{korisnik}', [KorisnikController::class, 'update']);
    Route::patch('korisnik/{korisnik}', [KorisnikController::class, 'update']);
    Route::patch('korisnik/{korisnik}/lozinka', [KorisnikController::class, 'changePassword']);
    Route::delete('korisnik/{korisnik}', [KorisnikController::class, 'destroy']);
});

// Internal routes (service-to-service)
Route::middleware('internal')->prefix('internal')->group(function () {
    Route::patch('korisnik/{korisnik}/suspend', [InternalController::class, 'suspend']);
    Route::get('korisnik/{korisnik}', [InternalController::class, 'showKorisnik']);
});

// Public GET routes
Route::get('uloga', [UlogaController::class, 'index']);
Route::get('uloga/{uloga}', [UlogaController::class, 'show']);

Route::get('korisnik', [KorisnikController::class, 'index']);
Route::get('korisnik/{korisnik}', [KorisnikController::class, 'show']);
