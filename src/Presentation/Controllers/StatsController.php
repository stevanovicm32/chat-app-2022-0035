<?php

namespace App\Presentation\Controllers;

use App\DataAccess\Models\Chat;
use App\DataAccess\Models\Korisnik;
use App\DataAccess\Models\Poruka;
use App\DataAccess\Models\Uloga;
use Illuminate\Http\JsonResponse;

class StatsController extends BaseController
{
    public function index(): JsonResponse
    {
        $korisniciPoUlozi = Uloga::query()
            ->withCount('korisnici')
            ->get()
            ->map(fn ($uloga) => [
                'uloga' => $uloga->naziv,
                'broj' => $uloga->korisnici_count,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'korisniciPoUlozi' => $korisniciPoUlozi,
                'ukupnoKorisnika' => Korisnik::count(),
                'ukupnoChatova' => Chat::count(),
                'ukupnoPoruka' => Poruka::count(),
            ],
        ]);
    }
}
