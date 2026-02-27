<?php

namespace App\DataAccess\Repositories\Contracts;

use App\DataAccess\Models\Uloga;
use Illuminate\Database\Eloquent\Collection;

interface UlogaRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Uloga;
    public function create(array $data): Uloga;
    public function update(Uloga $uloga, array $data): Uloga;
    public function delete(Uloga $uloga): bool;
    public function hasKorisnici(Uloga $uloga): bool;
}

