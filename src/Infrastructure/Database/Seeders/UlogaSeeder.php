<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\DataAccess\Models\Uloga;

class UlogaSeeder extends Seeder
{
    public function run(): void
    {
        $uloge = [
            ['naziv' => 'Admin'],
            ['naziv' => 'User'],
            ['naziv' => 'Moderator'],
        ];

        foreach ($uloge as $uloga) {
            Uloga::firstOrCreate(['naziv' => $uloga['naziv']], $uloga);
        }
    }
}

