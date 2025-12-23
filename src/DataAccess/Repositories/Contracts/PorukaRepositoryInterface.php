<?php

namespace App\DataAccess\Repositories\Contracts;

use App\DataAccess\Models\Poruka;
use Illuminate\Database\Eloquent\Collection;

interface PorukaRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function find(int $id): ?Poruka;
    public function create(array $data): Poruka;
    public function update(Poruka $poruka, array $data): Poruka;
    public function delete(Poruka $poruka): bool;
}

