<?php

namespace App\Business\Services;

use App\DataAccess\Repositories\Contracts\UlogaRepositoryInterface;
use App\DataAccess\Models\Uloga;
use Illuminate\Database\Eloquent\Collection;

class UlogaService
{
    public function __construct(
        private UlogaRepositoryInterface $ulogaRepository
    ) {}

    public function getAllUloge(): Collection
    {
        return $this->ulogaRepository->all();
    }

    public function getUlogaById(int $id): ?Uloga
    {
        return $this->ulogaRepository->find($id);
    }

    public function createUloga(array $data): Uloga
    {
        return $this->ulogaRepository->create($data);
    }

    public function updateUloga(int $id, array $data): Uloga
    {
        $uloga = $this->ulogaRepository->find($id);
        
        if (!$uloga) {
            throw new \Exception('Uloga nije pronađena', 404);
        }

        return $this->ulogaRepository->update($uloga, $data);
    }

    public function deleteUloga(int $id): bool
    {
        $uloga = $this->ulogaRepository->find($id);
        
        if (!$uloga) {
            throw new \Exception('Uloga nije pronađena', 404);
        }

        if ($this->ulogaRepository->hasKorisnici($uloga)) {
            throw new \Exception('Ne možete obrisati ulogu koja je dodeljena korisnicima', 422);
        }

        return $this->ulogaRepository->delete($uloga);
    }
}

