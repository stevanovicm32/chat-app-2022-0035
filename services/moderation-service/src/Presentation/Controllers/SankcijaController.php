<?php

namespace App\Presentation\Controllers;

use App\Business\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SankcijaController extends BaseController
{
    public function __construct(private ModerationService $moderationService) {}

    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->moderationService->getAllSankcije(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'idPrijava' => 'required|integer|exists:prijava,idPrijava',
                'days' => 'nullable|integer|min:1|max:30',
            ]);

            $result = $this->moderationService->createSankcija(
                $validated['idPrijava'],
                $user->idKorisnik,
                $validated['days'] ?? 3
            );

            return response()->json([
                'success' => true,
                'message' => 'Sankcija uspešno kreirana',
                'data' => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_int($c = $e->getCode()) && $c >= 100 && $c < 600 ? $c : 500);
        }
    }
}
