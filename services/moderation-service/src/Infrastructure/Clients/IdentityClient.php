<?php

namespace App\Infrastructure\Clients;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IdentityClient
{
    private CircuitBreaker $circuitBreaker;

    public function __construct(
        private string $baseUrl = '',
        private string $internalKey = ''
    ) {
        $this->baseUrl = rtrim($baseUrl ?: config('app.identity_service_url', env('IDENTITY_SERVICE_URL', '')), '/');
        $this->internalKey = $internalKey ?: config('app.internal_api_key', env('INTERNAL_API_KEY', ''));
        $this->circuitBreaker = new CircuitBreaker();
    }

    public function getUserFromToken(string $authorization): ?array
    {
        if ($this->circuitBreaker->isOpen()) {
            Log::warning('Circuit breaker fallback: getUserFromToken');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $authorization,
                'Accept' => 'application/json',
            ])->timeout(5)->get("{$this->baseUrl}/api/user");

            if (!$response->successful()) {
                $this->circuitBreaker->recordFailure();
                return null;
            }

            $this->circuitBreaker->recordSuccess();
            $body = $response->json();
            return $body['success'] ? ($body['data'] ?? null) : null;
        } catch (\Throwable $e) {
            $this->circuitBreaker->recordFailure();
            return null;
        }
    }

    public function suspendKorisnik(int $korisnikId, int $days = 3): ?array
    {
        if ($this->circuitBreaker->isOpen()) {
            Log::warning('Circuit breaker fallback: suspendKorisnik', ['korisnikId' => $korisnikId]);
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-Internal-Api-Key' => $this->internalKey,
                'Accept' => 'application/json',
            ])->timeout(5)->patch("{$this->baseUrl}/api/internal/korisnik/{$korisnikId}/suspend", [
                'days' => $days,
            ]);

            if (!$response->successful()) {
                $this->circuitBreaker->recordFailure();
                return null;
            }

            $this->circuitBreaker->recordSuccess();
            $body = $response->json();
            return $body['success'] ? ($body['data'] ?? null) : null;
        } catch (\Throwable $e) {
            $this->circuitBreaker->recordFailure();
            return null;
        }
    }

    public function getKorisnik(int $korisnikId): ?array
    {
        if ($this->circuitBreaker->isOpen()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->timeout(5)->get("{$this->baseUrl}/api/korisnik/{$korisnikId}");

            if (!$response->successful()) {
                $this->circuitBreaker->recordFailure();
                return null;
            }

            $this->circuitBreaker->recordSuccess();
            $body = $response->json();
            return $body['success'] ? ($body['data'] ?? null) : null;
        } catch (\Throwable $e) {
            $this->circuitBreaker->recordFailure();
            return null;
        }
    }
}
