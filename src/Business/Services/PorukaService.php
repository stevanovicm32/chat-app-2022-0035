<?php

namespace App\Business\Services;

use App\DataAccess\Repositories\Contracts\PorukaRepositoryInterface;
use App\DataAccess\Models\Poruka;
use Illuminate\Database\Eloquent\Collection;

class PorukaService
{
    public function __construct(
        private PorukaRepositoryInterface $porukaRepository
    ) {}

    public function getAllPoruke(array $filters = []): Collection
    {
        return $this->porukaRepository->all($filters);
    }

    public function getPorukaById(int $id): ?Poruka
    {
        return $this->porukaRepository->find($id);
    }

    public function createPoruka(array $data): Poruka
    {
        return $this->porukaRepository->create($data);
    }

    public function updatePoruka(int $id, array $data): Poruka
    {
        $poruka = $this->porukaRepository->find($id);
        
        if (!$poruka) {
            throw new \Exception('Poruka nije pronađena', 404);
        }

        return $this->porukaRepository->update($poruka, $data);
    }

    public function deletePoruka(int $id): bool
    {
        $poruka = $this->porukaRepository->find($id);
        
        if (!$poruka) {
            throw new \Exception('Poruka nije pronađena', 404);
        }

        return $this->porukaRepository->delete($poruka);
    }
}

