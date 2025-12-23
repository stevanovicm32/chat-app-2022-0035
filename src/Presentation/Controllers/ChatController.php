<?php

namespace App\Presentation\Controllers;

use App\Presentation\Requests\StoreChatRequest;
use App\Business\Services\ChatService;
use App\Presentation\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends BaseController
{
    public function __construct(
        private ChatService $chatService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];
            if ($request->has('idKorisnik')) {
                $filters['idKorisnik'] = $request->idKorisnik;
            }

            $chatovi = $this->chatService->getAllChatovi($filters);
            return response()->json([
                'success' => true,
                'data' => $chatovi
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreChatRequest $request): JsonResponse
    {
        try {
            $chat = $this->chatService->createChat($request->validated()['idKorisnici'] ?? []);
            return response()->json([
                'success' => true,
                'message' => 'Chat uspešno kreiran',
                'data' => $chat
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
            $chat = $this->chatService->getChatById($id);
            
            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat nije pronađen'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $chat
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
            $chat = $this->chatService->updateChat($id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Chat uspešno ažuriran',
                'data' => $chat
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
            $this->chatService->deleteChat($id);
            return response()->json([
                'success' => true,
                'message' => 'Chat uspešno obrisan'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}

