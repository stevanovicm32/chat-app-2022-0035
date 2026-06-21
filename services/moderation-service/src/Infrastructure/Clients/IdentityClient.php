<?php

namespace App\Infrastructure\Clients;

use Illuminate\Support\Facades\Http;

class IdentityClient
{
    public function __construct(
        private string $baseUrl = '',
        private string $internalKey = ''
    ) {
        $this->baseUrl = rtrim($baseUrl ?: config('app.identity_service_url', env('IDENTITY_SERVICE_URL', '')), '/');
        $this->internalKey = $internalKey ?: config('app.internal_api_key', env('INTERNAL_API_KEY', ''));
    }

    public function getUserFromToken(string $authorization): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => $authorization,
            'Accept' => 'application/json',
        ])->timeout(5)->get("{$this->baseUrl}/api/user");

        if (!$response->successful()) {
            return null;
        }

        $body = $response->json();
        return $body['success'] ? ($body['data'] ?? null) : null;
    }

    public function suspendKorisnik(int $korisnikId, int $days = 3): ?array
    {
        $response = Http::withHeaders([
            'X-Internal-Api-Key' => $this->internalKey,
            'Accept' => 'application/json',
        ])->timeout(5)->patch("{$this->baseUrl}/api/internal/korisnik/{$korisnikId}/suspend", [
            'days' => $days,
        ]);

        if (!$response->successful()) {
            return null;
        }

        $body = $response->json();
        return $body['success'] ? ($body['data'] ?? null) : null;
    }

    public function getKorisnik(int $korisnikId): ?array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->timeout(5)->get("{$this->baseUrl}/api/korisnik/{$korisnikId}");

        if (!$response->successful()) {
            return null;
        }

        $body = $response->json();
        return $body['success'] ? ($body['data'] ?? null) : null;
    }
}
