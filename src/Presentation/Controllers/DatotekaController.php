<?php

namespace App\Presentation\Controllers;

use App\Business\Services\DatotekaService;
use App\Presentation\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DatotekaController extends BaseController
{
    public function __construct(
        private DatotekaService $datotekaService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];
            if ($request->has('idPoruka')) {
                $filters['idPoruka'] = $request->idPoruka;
            }
            if ($request->has('tip')) {
                $filters['tip'] = $request->tip;
            }

            $datoteke = $this->datotekaService->getAllDatoteke($filters);
            return response()->json([
                'success' => true,
                'data' => $datoteke
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'putanja' => 'required_without:file|string|max:500',
                'tip' => 'required_without:file|string|max:100',
                'idPoruka' => 'required|exists:poruka,idPoruka',
                'file' => 'sometimes|file|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['putanja', 'tip', 'idPoruka']);
            $file = $request->hasFile('file') ? $request->file('file') : null;

            $datoteka = $this->datotekaService->createDatoteka($data, $file);
            return response()->json([
                'success' => true,
                'message' => 'Datoteka uspešno kreirana',
                'data' => $datoteka
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
            $datoteka = $this->datotekaService->getDatotekaById($id);
            
            if (!$datoteka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datoteka nije pronađena'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $datoteka
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
            $validator = Validator::make($request->all(), [
                'putanja' => 'sometimes|required|string|max:500',
                'tip' => 'sometimes|required|string|max:100',
                'idPoruka' => 'sometimes|exists:poruka,idPoruka',
                'file' => 'sometimes|file|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['putanja', 'tip', 'idPoruka']);
            $file = $request->hasFile('file') ? $request->file('file') : null;

            $datoteka = $this->datotekaService->updateDatoteka($id, $data, $file);
            return response()->json([
                'success' => true,
                'message' => 'Datoteka uspešno ažurirana',
                'data' => $datoteka
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
            $this->datotekaService->deleteDatoteka($id);
            return response()->json([
                'success' => true,
                'message' => 'Datoteka uspešno obrisana'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}

