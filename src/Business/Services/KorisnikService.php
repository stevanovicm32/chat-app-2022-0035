<?php

namespace App\Business\Services;

use App\DataAccess\Repositories\Contracts\KorisnikRepositoryInterface;
use App\DataAccess\Models\Korisnik;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class KorisnikService
{
    public function __construct(
        private KorisnikRepositoryInterface $korisnikRepository
    ) {}

    public function getAllKorisnici(array $filters = []): Collection
    {
        return $this->korisnikRepository->all($filters);
    }

    public function getKorisnikById(int $id): ?Korisnik
    {
        return $this->korisnikRepository->find($id);
    }

    public function createKorisnik(array $data): Korisnik
    {
        if (isset($data['lozinka'])) {
            $data['lozinka'] = Hash::make($data['lozinka']);
        }

        return $this->korisnikRepository->create($data);
    }

    public function updateKorisnik(int $id, array $data): Korisnik
    {
        $korisnik = $this->korisnikRepository->find($id);
        
        if (!$korisnik) {
            throw new \Exception('Korisnik nije pronađen', 404);
        }

        if (isset($data['lozinka'])) {
            $data['lozinka'] = Hash::make($data['lozinka']);
        }

        return $this->korisnikRepository->update($korisnik, $data);
    }

    public function deleteKorisnik(int $id): bool
    {
        $korisnik = $this->korisnikRepository->find($id);
        
        if (!$korisnik) {
            throw new \Exception('Korisnik nije pronađen', 404);
        }

        return $this->korisnikRepository->delete($korisnik);
    }

    public function getKorisniciByIds(array $ids): Collection
    {
        return $this->korisnikRepository->findByIds($ids);
    }

    public function suspendKorisnik(int $id, int $days = 3): Korisnik
    {
        $korisnik = $this->korisnikRepository->find($id);

        if (!$korisnik) {
            throw new \Exception('Korisnik nije pronađen', 404);
        }

        $korisnik->suspendovan = Carbon::now()->addDays($days);
        return $this->korisnikRepository->update($korisnik, [
            'suspendovan' => $korisnik->suspendovan
        ]);
    }

    public function changePassword(int $id, string $oldPassword, string $newPassword): Korisnik
    {
        $korisnik = $this->korisnikRepository->find($id);

        if (!$korisnik) {
            throw new \Exception('Korisnik nije pronađen', 404);
        }

        if (!Hash::check($oldPassword, $korisnik->lozinka)) {
            throw new \Exception('Stara lozinka nije tačna', 422);
        }

        $korisnik->lozinka = Hash::make($newPassword);

        return $this->korisnikRepository->update($korisnik, [
            'lozinka' => $korisnik->lozinka
        ]);
    }
}

