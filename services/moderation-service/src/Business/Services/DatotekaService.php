<?php

namespace App\Business\Services;

use App\DataAccess\Repositories\Contracts\DatotekaRepositoryInterface;
use App\DataAccess\Models\Datoteka;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class DatotekaService
{
    public function __construct(
        private DatotekaRepositoryInterface $datotekaRepository
    ) {}

    public function getAllDatoteke(array $filters = []): Collection
    {
        return $this->datotekaRepository->all($filters);
    }

    public function getDatotekaById(int $id): ?Datoteka
    {
        return $this->datotekaRepository->find($id);
    }

    public function createDatoteka(array $data, $file = null): Datoteka
    {
        if ($file) {
            $path = $file->store('datoteke', 'public');
            $data['putanja'] = $path;
            $data['tip'] = $file->getMimeType();
        }

        return $this->datotekaRepository->create($data);
    }

    public function updateDatoteka(int $id, array $data, $file = null): Datoteka
    {
        $datoteka = $this->datotekaRepository->find($id);
        
        if (!$datoteka) {
            throw new \Exception('Datoteka nije pronađena', 404);
        }

        if ($file) {
            if ($datoteka->putanja && Storage::disk('public')->exists($datoteka->putanja)) {
                Storage::disk('public')->delete($datoteka->putanja);
            }

            $path = $file->store('datoteke', 'public');
            $data['putanja'] = $path;
            $data['tip'] = $file->getMimeType();
        }

        return $this->datotekaRepository->update($datoteka, $data);
    }

    public function deleteDatoteka(int $id): bool
    {
        $datoteka = $this->datotekaRepository->find($id);
        
        if (!$datoteka) {
            throw new \Exception('Datoteka nije pronađena', 404);
        }

        if ($datoteka->putanja && Storage::disk('public')->exists($datoteka->putanja)) {
            Storage::disk('public')->delete($datoteka->putanja);
        }

        return $this->datotekaRepository->delete($datoteka);
    }
}

