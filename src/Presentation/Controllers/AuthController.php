<?php

namespace App\Presentation\Controllers;

use App\Business\Services\KorisnikService;
use App\DataAccess\Models\Korisnik;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseController
{
    public function __construct(
        private KorisnikService $korisnikService
    ) {}

    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:korisnik,email|max:255',
                'lozinka' => 'required|string|min:6',
                'idUloga' => 'required|exists:uloga,idUloga',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $korisnik = $this->korisnikService->createKorisnik($request->only(['email', 'lozinka', 'idUloga']));

            return response()->json([
                'success' => true,
                'message' => 'Korisnik uspešno registrovan',
                'data' => $korisnik
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'lozinka' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $korisnik = Korisnik::where('email', $request->email)->first();

            if (!$korisnik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Neispravni kredencijali'
                ], 401);
            }

            if (!Hash::check($request->lozinka, $korisnik->lozinka)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Neispravni kredencijali'
                ], 401);
            }

            // Kreiraj token - sa fallback ako Sanctum ne radi
            try {
                $token = $korisnik->createToken('auth-token')->plainTextToken;
            } catch (\Exception $tokenError) {
                Log::error('Token creation error: ' . $tokenError->getMessage());
                // Fallback: kreiraj jednostavan token
                $token = bin2hex(random_bytes(32));
                Log::warning('Using fallback token generation');
            }

            // Učitaj ulogu sa korisnikom
            $korisnik->load('uloga');

            // Pripremi podatke korisnika za odgovor (bez lozinke)
            $korisnikData = [
                'idKorisnik' => $korisnik->idKorisnik,
                'email' => $korisnik->email,
                'idUloga' => $korisnik->idUloga,
                'suspendovan' => $korisnik->suspendovan,
                'uloga' => $korisnik->uloga ? [
                    'idUloga' => $korisnik->uloga->idUloga,
                    'naziv' => $korisnik->uloga->naziv,
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Uspešna prijava',
                'data' => [
                    'korisnik' => $korisnikData,
                    'token' => $token
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Greška pri prijavi: ' . $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Uspešna odjava'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

