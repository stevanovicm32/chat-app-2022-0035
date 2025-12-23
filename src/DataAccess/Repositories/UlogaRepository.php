<?php

namespace App\DataAccess\Repositories;

use App\DataAccess\Models\Uloga;
use App\DataAccess\Repositories\Contracts\UlogaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UlogaRepository implements UlogaRepositoryInterface
{
    public function all(): Collection
    {
        return Uloga::all();
    }

    public function find(int $id): ?Uloga
    {
        return Uloga::find($id);
    }

    public function create(array $data): Uloga
    {
        return Uloga::create($data);
    }

    public function update(Uloga $uloga, array $data): Uloga
    {
        $uloga->update($data);
        return $uloga->fresh();
    }

    public function delete(Uloga $uloga): bool
    {
        return $uloga->delete();
    }

    public function hasKorisnici(Uloga $uloga): bool
    {
        return $uloga->korisnici()->count() > 0;
    }
}

