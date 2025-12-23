<?php

namespace App\DataAccess\Repositories\Contracts;

use App\DataAccess\Models\Korisnik;
use Illuminate\Database\Eloquent\Collection;

interface KorisnikRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function find(int $id): ?Korisnik;
    public function findByEmail(string $email): ?Korisnik;
    public function create(array $data): Korisnik;
    public function update(Korisnik $korisnik, array $data): Korisnik;
    public function delete(Korisnik $korisnik): bool;
}

