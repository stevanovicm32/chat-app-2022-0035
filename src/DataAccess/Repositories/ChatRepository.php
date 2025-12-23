<?php

namespace App\DataAccess\Repositories;

use App\DataAccess\Models\Chat;
use App\DataAccess\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ChatRepository implements ChatRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = Chat::with(['korisnici', 'poruke']);

        if (isset($filters['idKorisnik'])) {
            $query->whereHas('korisnici', function ($q) use ($filters) {
                $q->where('korisnik.idKorisnik', $filters['idKorisnik']);
            });
        }

        return $query->get();
    }

    public function find(int $id): ?Chat
    {
        return Chat::with(['korisnici', 'poruke.datoteke', 'grupniChat', 'privatniChat'])->find($id);
    }

    public function create(): Chat
    {
        return Chat::create();
    }

    public function update(Chat $chat, array $data): Chat
    {
        $chat->update($data);
        return $chat->fresh(['korisnici', 'poruke']);
    }

    public function delete(Chat $chat): bool
    {
        return $chat->delete();
    }

    public function addKorisnik(Chat $chat, int $idKorisnik, ?string $datumKreiranja = null): void
    {
        if (!$this->hasKorisnik($chat, $idKorisnik)) {
            $chat->korisnici()->attach($idKorisnik, [
                'datumKreiranja' => $datumKreiranja ? Carbon::parse($datumKreiranja) : now()
            ]);
        }
    }

    public function removeKorisnik(Chat $chat, int $idKorisnik): void
    {
        $chat->korisnici()->detach($idKorisnik);
    }

    public function hasKorisnik(Chat $chat, int $idKorisnik): bool
    {
        return $chat->korisnici()->where('korisnik.idKorisnik', $idKorisnik)->exists();
    }
}

