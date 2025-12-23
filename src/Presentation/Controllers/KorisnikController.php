<?php

namespace App\Presentation\Controllers;

use App\Presentation\Requests\StoreKorisnikRequest;
use App\Presentation\Requests\UpdateKorisnikRequest;
use App\Business\Services\KorisnikService;
use App\Presentation\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KorisnikController extends BaseController
{
    public function __construct(
        private KorisnikService $korisnikService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];
            
            if ($request->has('idUloga')) {
                $filters['idUloga'] = $request->idUloga;
            }

            if ($request->has('suspendovan')) {
                $filters['suspendovan'] = $request->suspendovan === 'true';
            }

            $korisnici = $this->korisnikService->getAllKorisnici($filters);
            
            return response()->json([
                'success' => true,
                'data' => $korisnici
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreKorisnikRequest $request): JsonResponse
    {
        try {
            $korisnik = $this->korisnikService->createKorisnik($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Korisnik uspešno kreiran',
                'data' => $korisnik
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
            $korisnik = $this->korisnikService->getKorisnikById($id);
            
            if (!$korisnik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Korisnik nije pronađen'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $korisnik
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function update(UpdateKorisnikRequest $request, int $id): JsonResponse
    {
        try {
            $korisnik = $this->korisnikService->updateKorisnik($id, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Korisnik uspešno ažuriran',
                'data' => $korisnik
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
            $this->korisnikService->deleteKorisnik($id);
            return response()->json([
                'success' => true,
                'message' => 'Korisnik uspešno obrisan'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}

