<?php

namespace App\Presentation\Controllers;

use App\Presentation\Requests\StoreUlogaRequest;
use App\Presentation\Requests\UpdateUlogaRequest;
use App\Business\Services\UlogaService;
use App\Presentation\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class UlogaController extends BaseController
{
    public function __construct(
        private UlogaService $ulogaService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $uloge = $this->ulogaService->getAllUloge();
            return response()->json([
                'success' => true,
                'data' => $uloge
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreUlogaRequest $request): JsonResponse
    {
        try {
            $uloga = $this->ulogaService->createUloga($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Uloga uspešno kreirana',
                'data' => $uloga
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $uloga = $this->ulogaService->getUlogaById($id);
            
            if (!$uloga) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uloga nije pronađena'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $uloga
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function update(UpdateUlogaRequest $request, int $id): JsonResponse
    {
        try {
            $uloga = $this->ulogaService->updateUloga($id, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Uloga uspešno ažurirana',
                'data' => $uloga
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->ulogaService->deleteUloga($id);
            return response()->json([
                'success' => true,
                'message' => 'Uloga uspešno obrisana'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}

