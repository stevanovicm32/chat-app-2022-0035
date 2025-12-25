<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Presentation\Controllers\UlogaController;
use App\Presentation\Controllers\KorisnikController;
use App\Presentation\Controllers\ChatController;
use App\Presentation\Controllers\PorukaController;
use App\Presentation\Controllers\DatotekaController;
use App\Presentation\Controllers\PripadaController;
use App\Presentation\Controllers\AuthController;

// Auth routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ], 200);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Uloga routes
Route::apiResource('uloga', UlogaController::class);

// Korisnik routes
Route::apiResource('korisnik', KorisnikController::class);

// Chat routes
Route::apiResource('chat', ChatController::class);

// Nested routes: Poruke u chatu
Route::prefix('chat/{chat}')->group(function () {
    Route::apiResource('poruka', PorukaController::class);
});

// Standalone Poruka routes
Route::apiResource('poruka', PorukaController::class);

// Nested routes: Datoteke u poruci
Route::prefix('poruka/{poruka}')->group(function () {
    Route::apiResource('datoteka', DatotekaController::class);
});

// Standalone Datoteka routes
Route::apiResource('datoteka', DatotekaController::class);


// Pripada routes (korisnik-chat veza)
Route::prefix('chat/{chat}')->group(function () {
    Route::get('korisnici', [PripadaController::class, 'index']);
    Route::post('korisnici', [PripadaController::class, 'store']);
    Route::delete('korisnici/{korisnik}', [PripadaController::class, 'destroy']);
});

Route::prefix('korisnik/{korisnik}')->group(function () {
    Route::get('chatovi', [PripadaController::class, 'korisnikChatovi']);
});

