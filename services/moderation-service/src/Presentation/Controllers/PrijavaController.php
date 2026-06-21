<?php

namespace App\Presentation\Controllers;

use App\Business\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrijavaController extends BaseController
{
    public function __construct(private ModerationService $moderationService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];
            if ($request->has('status')) {
                $filters['status'] = $request->status;
            }

            return response()->json([
                'success' => true,
                'data' => $this->moderationService->getAllPrijave($filters),
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
                'poruka_id_ref' => 'nullable|integer',
                'optuzeni_id_ref' => 'nullable|integer',
            ]);

            $prijava = $this->moderationService->createPrijava([
                'podnosilac_id_ref' => $user->idKorisnik,
                'poruka_id_ref' => $validated['poruka_id_ref'] ?? null,
                'optuzeni_id_ref' => $validated['optuzeni_id_ref'] ?? null,
                'status' => 'na_cekanju',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prijava uspešno kreirana',
                'data' => $prijava,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $prijava = $this->moderationService->getPrijavaById($id);
            if (!$prijava) {
                return response()->json(['success' => false, 'message' => 'Prijava nije pronađena'], 404);
            }

            return response()->json(['success' => true, 'data' => $prijava]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['status' => 'required|in:na_cekanju,odobreno,odbijeno']);
            $prijava = $this->moderationService->updatePrijavaStatus($id, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Status prijave ažuriran',
                'data' => $prijava,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_int($c = $e->getCode()) && $c >= 100 && $c < 600 ? $c : 500);
        }
    }
}
