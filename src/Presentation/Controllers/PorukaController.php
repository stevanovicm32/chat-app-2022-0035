<?php

namespace App\Presentation\Controllers;

use App\Presentation\Requests\StorePorukaRequest;
use App\Business\Services\PorukaService;
use App\Presentation\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PorukaController extends BaseController
{
    public function __construct(
        private PorukaService $porukaService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];
            if ($request->has('idChat')) {
                $filters['idChat'] = $request->idChat;
            }
            if ($request->has('idKorisnik')) {
                $filters['idKorisnik'] = $request->idKorisnik;
            }

            $poruke = $this->porukaService->getAllPoruke($filters);
            return response()->json([
                'success' => true,
                'data' => $poruke
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StorePorukaRequest $request): JsonResponse
    {
        try {
            $poruka = $this->porukaService->createPoruka($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Poruka uspešno kreirana',
                'data' => $poruka
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
            $poruka = $this->porukaService->getPorukaById($id);
            
            if (!$poruka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poruka nije pronađena'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $poruka
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $poruka = $this->porukaService->updatePoruka($id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Poruka uspešno ažurirana',
                'data' => $poruka
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
            $this->porukaService->deletePoruka($id);
            return response()->json([
                'success' => true,
                'message' => 'Poruka uspešno obrisana'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}

