<?php

namespace App\Presentation\Controllers;

use App\Presentation\Requests\StorePorukaRequest;
use App\Business\Services\PorukaService;
use App\Presentation\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

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
            if (!$request->filled('idChat') && $request->route('chat')) {
                $chat = $request->route('chat');
                $request->merge(['idChat' => $chat instanceof \App\DataAccess\Models\Chat ? $chat->idChat : $chat]);
            }
            if (!$request->filled('idKorisnik') && $request->user()) {
                $request->merge(['idKorisnik' => $request->user()->idKorisnik]);
            }
            $user = $request->user();
            if ($user?->suspendovan && Carbon::parse($user->suspendovan)->isFuture()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Niste u mogućnosti da šaljete poruke dok ste suspendovani'
                ], 403);
            }
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
            ], is_int($c = $e->getCode()) && $c >= 100 && $c < 600 ? $c : 500);
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
            ], is_int($c = $e->getCode()) && $c >= 100 && $c < 600 ? $c : 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $poruka = $this->porukaService->getPorukaById($id);
            if (!$poruka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poruka nije pronađena'
                ], 404);
            }

            $user = $request->user();
            $isModerator = $user->idUloga === 3;
            if (!$isModerator && $poruka->idKorisnik !== $user->idKorisnik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nemate dozvolu da obrišete ovu poruku'
                ], 403);
            }

            $this->porukaService->deletePoruka($id);
            return response()->json([
                'success' => true,
                'message' => 'Poruka uspešno obrisana'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], is_int($c = $e->getCode()) && $c >= 100 && $c < 600 ? $c : 500);
        }
    }
}

