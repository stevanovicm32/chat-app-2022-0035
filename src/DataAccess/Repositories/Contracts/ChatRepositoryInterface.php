<?php

namespace App\DataAccess\Repositories\Contracts;

use App\DataAccess\Models\Chat;
use Illuminate\Database\Eloquent\Collection;

interface ChatRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function find(int $id): ?Chat;
    public function create(): Chat;
    public function update(Chat $chat, array $data): Chat;
    public function delete(Chat $chat): bool;
    public function addKorisnik(Chat $chat, int $idKorisnik, ?string $datumKreiranja = null): void;
    public function removeKorisnik(Chat $chat, int $idKorisnik): void;
    public function hasKorisnik(Chat $chat, int $idKorisnik): bool;
}

