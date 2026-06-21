<?php

namespace App\Business\Services;

use App\DataAccess\Repositories\Contracts\ChatRepositoryInterface;
use App\DataAccess\Models\Chat;
use Illuminate\Database\Eloquent\Collection;

class ChatService
{
    public function __construct(
        private ChatRepositoryInterface $chatRepository
    ) {}

    public function getAllChatovi(array $filters = []): Collection
    {
        return $this->chatRepository->all($filters);
    }

    public function getChatById(int $id): ?Chat
    {
        return $this->chatRepository->find($id);
    }

    public function createChat(array $korisniciIds = []): Chat
    {
        $chat = $this->chatRepository->create();

        foreach ($korisniciIds as $idKorisnik) {
            $this->chatRepository->addKorisnik($chat, $idKorisnik);
        }

        return $chat->fresh(['korisnici', 'poruke']);
    }

    public function updateChat(int $id, array $data): Chat
    {
        $chat = $this->chatRepository->find($id);
        
        if (!$chat) {
            throw new \Exception('Chat nije pronađen', 404);
        }

        if (isset($data['idKorisnici']) && is_array($data['idKorisnici'])) {
            foreach ($data['idKorisnici'] as $idKorisnik) {
                $this->chatRepository->addKorisnik($chat, $idKorisnik);
            }
            unset($data['idKorisnici']);
        }

        if (!empty($data)) {
            $this->chatRepository->update($chat, $data);
        }

        return $chat->fresh(['korisnici', 'poruke']);
    }

    public function deleteChat(int $id): bool
    {
        $chat = $this->chatRepository->find($id);
        
        if (!$chat) {
            throw new \Exception('Chat nije pronađen', 404);
        }

        return $this->chatRepository->delete($chat);
    }

    public function addKorisnikToChat(int $chatId, int $korisnikId, ?string $datumKreiranja = null): void
    {
        $chat = $this->chatRepository->find($chatId);
        
        if (!$chat) {
            throw new \Exception('Chat nije pronađen', 404);
        }

        if ($this->chatRepository->hasKorisnik($chat, $korisnikId)) {
            throw new \Exception('Korisnik već pripada ovom chatu', 422);
        }

        $this->chatRepository->addKorisnik($chat, $korisnikId, $datumKreiranja);
    }

    public function removeKorisnikFromChat(int $chatId, int $korisnikId): void
    {
        $chat = $this->chatRepository->find($chatId);
        
        if (!$chat) {
            throw new \Exception('Chat nije pronađen', 404);
        }

        if (!$this->chatRepository->hasKorisnik($chat, $korisnikId)) {
            throw new \Exception('Korisnik ne pripada ovom chatu', 404);
        }

        $this->chatRepository->removeKorisnik($chat, $korisnikId);
    }
}

