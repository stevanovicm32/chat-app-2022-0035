<?php

namespace App\Presentation\Controllers;

use App\Presentation\Requests\StoreChatRequest;
use App\Business\Services\ChatService;
use App\Business\Services\KorisnikService;
use App\Presentation\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class ChatController extends BaseController
{
    private const ADMIN_ROLE_ID = 1;

    public function __construct(
        private ChatService $chatService,
        private KorisnikService $korisnikService
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
            $user = $request->user();
            if ($user?->suspendovan && Carbon::parse($user->suspendovan)->isFuture()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Niste u mogućnosti da kreirate chat dok ste suspendovani'
                ], 403);
            }

            $participantIds = array_unique($request->validated()['idKorisnici'] ?? []);
            $korisnici = $this->korisnikService->getKorisniciByIds($participantIds);
            if ($korisnici->contains(fn ($korisnik) => $korisnik->idUloga === self::ADMIN_ROLE_ID)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ne možete kreirati chat koji uključuje administratora'
                ], 422);
            }

            $chat = $this->chatService->createChat($participantIds);
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

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $chat = $this->chatService->getChatById($id);
            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat nije pronađen'
                ], 404);
            }

            $user = $request->user();
            $isParticipant = $chat->korisnici->contains('idKorisnik', $user->idKorisnik);
            if (!$isParticipant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nemate dozvolu za brisanje chata'
                ], 403);
            }

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

