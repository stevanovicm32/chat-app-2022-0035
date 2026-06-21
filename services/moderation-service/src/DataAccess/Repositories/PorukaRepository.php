<?php

namespace App\DataAccess\Repositories;

use App\DataAccess\Models\Poruka;
use App\DataAccess\Repositories\Contracts\PorukaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PorukaRepository implements PorukaRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = Poruka::with(['chat', 'korisnik', 'datoteke']);

        if (isset($filters['idChat'])) {
            $query->where('idChat', $filters['idChat']);
        }

        if (isset($filters['idKorisnik'])) {
            $query->where('idKorisnik', $filters['idKorisnik']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function find(int $id): ?Poruka
    {
        return Poruka::with(['chat', 'korisnik', 'datoteke'])->find($id);
    }

    public function create(array $data): Poruka
    {
        return Poruka::create($data);
    }

    public function update(Poruka $poruka, array $data): Poruka
    {
        $poruka->update($data);
        return $poruka->fresh(['chat', 'korisnik', 'datoteke']);
    }

    public function delete(Poruka $poruka): bool
    {
        return $poruka->delete();
    }
}

