<?php

namespace App\DataAccess\Repositories;

use App\DataAccess\Models\Korisnik;
use App\DataAccess\Repositories\Contracts\KorisnikRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class KorisnikRepository implements KorisnikRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = Korisnik::with('uloga');

        if (isset($filters['idUloga'])) {
            $query->where('idUloga', $filters['idUloga']);
        }

        if (isset($filters['suspendovan'])) {
            if ($filters['suspendovan'] === true) {
                $query->whereNotNull('suspendovan');
            } else {
                $query->whereNull('suspendovan');
            }
        }

        return $query->get();
    }

    public function find(int $id): ?Korisnik
    {
        return Korisnik::with(['uloga', 'chatovi', 'poruke'])->find($id);
    }

    public function findByEmail(string $email): ?Korisnik
    {
        return Korisnik::where('email', $email)->first();
    }

    public function create(array $data): Korisnik
    {
        return Korisnik::create($data);
    }

    public function update(Korisnik $korisnik, array $data): Korisnik
    {
        $korisnik->update($data);
        return $korisnik->fresh(['uloga']);
    }

    public function delete(Korisnik $korisnik): bool
    {
        return $korisnik->delete();
    }
}

