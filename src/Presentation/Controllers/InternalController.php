<?php

namespace App\Presentation\Controllers;

use App\Business\Services\KorisnikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalController extends BaseController
{
    public function __construct(
        private KorisnikService $korisnikService
    ) {}

    public function suspend(Request $request, int $id): JsonResponse
    {
        try {
            $days = (int) ($request->input('days', 3));
            $korisnik = $this->korisnikService->suspendKorisnik($id, max(1, $days));

            return response()->json([
                'success' => true,
                'message' => 'Korisnik suspendovan',
                'data' => $korisnik->load('uloga'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_int($c = $e->getCode()) && $c >= 100 && $c < 600 ? $c : 500);
        }
    }

    public function showKorisnik(int $id): JsonResponse
    {
        try {
            $korisnik = $this->korisnikService->getKorisnikById($id);

            if (!$korisnik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Korisnik nije pronađen',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $korisnik,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
