<?php

namespace App\DataAccess\Repositories\Contracts;

use App\DataAccess\Models\Datoteka;
use Illuminate\Database\Eloquent\Collection;

interface DatotekaRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function find(int $id): ?Datoteka;
    public function create(array $data): Datoteka;
    public function update(Datoteka $datoteka, array $data): Datoteka;
    public function delete(Datoteka $datoteka): bool;
}

