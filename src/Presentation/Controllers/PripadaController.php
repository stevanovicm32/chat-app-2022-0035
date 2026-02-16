<?php

namespace App\Presentation\Controllers;

use App\Business\Services\ChatService;
use App\Business\Services\KorisnikService;
use App\Presentation\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PripadaController extends BaseController
{
    public function __construct(
        private ChatService $chatService,
        private KorisnikService $korisnikService
    ) {}

    public function index(int $chat): JsonResponse
    {
        try {
            $chatModel = $this->chatService->getChatById($chat);
            
            if (!$chatModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat nije pronađen'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $chatModel->korisnici
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request, int $chat): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'idKorisnik' => 'required|exists:korisnik,idKorisnik',
                'datumKreiranja' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->chatService->addKorisnikToChat($chat, $request->idKorisnik, $request->datumKreiranja);
            $chatModel = $this->chatService->getChatById($chat);

            return response()->json([
                'success' => true,
                'message' => 'Korisnik uspešno dodat u chat',
                'data' => $chatModel
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function destroy(int $chat, int $korisnik): JsonResponse
    {
        try {
            $this->chatService->removeKorisnikFromChat($chat, $korisnik);
            return response()->json([
                'success' => true,
                'message' => 'Korisnik uspešno uklonjen iz chata'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    public function korisnikChatovi(int $korisnik): JsonResponse
    {
        try {
            $korisnikModel = $this->korisnikService->getKorisnikById($korisnik);
            
            if (!$korisnikModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Korisnik nije pronađen'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $korisnikModel->chatovi
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}

