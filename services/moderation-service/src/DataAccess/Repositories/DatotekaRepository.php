<?php

namespace App\DataAccess\Repositories;

use App\DataAccess\Models\Datoteka;
use App\DataAccess\Repositories\Contracts\DatotekaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DatotekaRepository implements DatotekaRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = Datoteka::with('poruka');

        if (isset($filters['idPoruka'])) {
            $query->where('idPoruka', $filters['idPoruka']);
        }

        if (isset($filters['tip'])) {
            $query->where('tip', $filters['tip']);
        }

        return $query->get();
    }

    public function find(int $id): ?Datoteka
    {
        return Datoteka::with('poruka')->find($id);
    }

    public function create(array $data): Datoteka
    {
        return Datoteka::create($data);
    }

    public function update(Datoteka $datoteka, array $data): Datoteka
    {
        $datoteka->update($data);
        return $datoteka->fresh('poruka');
    }

    public function delete(Datoteka $datoteka): bool
    {
        return $datoteka->delete();
    }
}

