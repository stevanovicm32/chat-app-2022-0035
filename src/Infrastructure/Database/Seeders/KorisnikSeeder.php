<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\DataAccess\Models\Korisnik;
use App\DataAccess\Models\Uloga;
use Illuminate\Support\Facades\Hash;

class KorisnikSeeder extends Seeder
{
    public function run(): void
    {
        // Pronađi uloge
        $adminUloga = Uloga::where('naziv', 'Admin')->first();
        $userUloga = Uloga::where('naziv', 'User')->first();
        $moderatorUloga = Uloga::where('naziv', 'Moderator')->first();

        // Admin korisnik
        if ($adminUloga) {
            Korisnik::firstOrCreate(
                ['email' => 'admin@test.com'],
                [
                    'email' => 'admin@test.com',
                    'lozinka' => Hash::make('admin123'),
                    'idUloga' => $adminUloga->idUloga,
                    'suspendovan' => null,
                ]
            );
        }

        // Regular korisnik
        if ($userUloga) {
            Korisnik::firstOrCreate(
                ['email' => 'user@test.com'],
                [
                    'email' => 'user@test.com',
                    'lozinka' => Hash::make('user123'),
                    'idUloga' => $userUloga->idUloga,
                    'suspendovan' => null,
                ]
            );
        }

        // Moderator korisnik
        if ($moderatorUloga) {
            Korisnik::firstOrCreate(
                ['email' => 'moderator@test.com'],
                [
                    'email' => 'moderator@test.com',
                    'lozinka' => Hash::make('moderator123'),
                    'idUloga' => $moderatorUloga->idUloga,
                    'suspendovan' => null,
                ]
            );
        }
    }
}

