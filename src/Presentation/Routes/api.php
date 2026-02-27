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
use App\Presentation\Controllers\StatsController;

// Auth routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes - zahtevaju autentifikaciju
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/stats', [StatsController::class, 'index']);

    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ], 200);
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // CREATE, UPDATE, DELETE rute - zaštićene
    Route::post('uloga', [UlogaController::class, 'store']);
    Route::put('uloga/{uloga}', [UlogaController::class, 'update']);
    Route::patch('uloga/{uloga}', [UlogaController::class, 'update']);
    Route::delete('uloga/{uloga}', [UlogaController::class, 'destroy']);

    Route::post('korisnik', [KorisnikController::class, 'store']);
    Route::put('korisnik/{korisnik}', [KorisnikController::class, 'update']);
    Route::patch('korisnik/{korisnik}', [KorisnikController::class, 'update']);
    Route::patch('korisnik/{korisnik}/lozinka', [KorisnikController::class, 'changePassword']);
    Route::patch('korisnik/{korisnik}/suspend', [KorisnikController::class, 'suspend']);
    Route::delete('korisnik/{korisnik}', [KorisnikController::class, 'destroy']);

    Route::post('chat', [ChatController::class, 'store']);
    Route::put('chat/{chat}', [ChatController::class, 'update']);
    Route::patch('chat/{chat}', [ChatController::class, 'update']);
    Route::delete('chat/{chat}', [ChatController::class, 'destroy']);

    Route::post('poruka', [PorukaController::class, 'store']);
    Route::put('poruka/{poruka}', [PorukaController::class, 'update']);
    Route::patch('poruka/{poruka}', [PorukaController::class, 'update']);
    Route::delete('poruka/{poruka}', [PorukaController::class, 'destroy']);

    Route::post('datoteka', [DatotekaController::class, 'store']);
    Route::put('datoteka/{datoteka}', [DatotekaController::class, 'update']);
    Route::patch('datoteka/{datoteka}', [DatotekaController::class, 'update']);
    Route::delete('datoteka/{datoteka}', [DatotekaController::class, 'destroy']);

    // Nested CREATE, UPDATE, DELETE
    Route::prefix('chat/{chat}')->group(function () {
        Route::post('poruka', [PorukaController::class, 'store']);
        Route::put('poruka/{poruka}', [PorukaController::class, 'update']);
        Route::patch('poruka/{poruka}', [PorukaController::class, 'update']);
        Route::delete('poruka/{poruka}', [PorukaController::class, 'destroy']);
    });

    Route::prefix('poruka/{poruka}')->group(function () {
        Route::post('datoteka', [DatotekaController::class, 'store']);
        Route::put('datoteka/{datoteka}', [DatotekaController::class, 'update']);
        Route::patch('datoteka/{datoteka}', [DatotekaController::class, 'update']);
        Route::delete('datoteka/{datoteka}', [DatotekaController::class, 'destroy']);
    });

    // Pripada routes - CREATE, DELETE zaštićene
    Route::prefix('chat/{chat}')->group(function () {
        Route::post('korisnici', [PripadaController::class, 'store']);
        Route::delete('korisnici/{korisnik}', [PripadaController::class, 'destroy']);
    });
});

// Public routes - GET (index, show) - ne zahtevaju autentifikaciju
Route::get('uloga', [UlogaController::class, 'index']);
Route::get('uloga/{uloga}', [UlogaController::class, 'show']);

Route::get('korisnik', [KorisnikController::class, 'index']);
Route::get('korisnik/{korisnik}', [KorisnikController::class, 'show']);

Route::get('chat', [ChatController::class, 'index']);
Route::get('chat/{chat}', [ChatController::class, 'show']);

Route::get('poruka', [PorukaController::class, 'index']);
Route::get('poruka/{poruka}', [PorukaController::class, 'show']);

Route::get('datoteka', [DatotekaController::class, 'index']);
Route::get('datoteka/{datoteka}', [DatotekaController::class, 'show']);

// Nested GET routes
Route::prefix('chat/{chat}')->group(function () {
    Route::get('poruka', [PorukaController::class, 'index']);
    Route::get('poruka/{poruka}', [PorukaController::class, 'show']);
});

Route::prefix('poruka/{poruka}')->group(function () {
    Route::get('datoteka', [DatotekaController::class, 'index']);
    Route::get('datoteka/{datoteka}', [DatotekaController::class, 'show']);
});

// Pripada GET routes - javne
Route::prefix('chat/{chat}')->group(function () {
    Route::get('korisnici', [PripadaController::class, 'index']);
});

Route::prefix('korisnik/{korisnik}')->group(function () {
    Route::get('chatovi', [PripadaController::class, 'korisnikChatovi']);
});

