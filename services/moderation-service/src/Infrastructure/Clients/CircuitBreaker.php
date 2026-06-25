<?php

namespace App\Infrastructure\Clients;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Circuit Breaker zaštita od kaskadnih otkazivanja pri pozivu Identity servisa.
 * @see README — distribuirani patern Circuit Breaker
 */
class CircuitBreaker
{
    private int $failures = 0;
    private ?int $openedAt = null;

    public function __construct(
        private int $threshold = 3,
        private int $cooldownSeconds = 30
    ) {}

    public function isOpen(): bool
    {
        if ($this->openedAt === null) {
            return false;
        }
        if (time() - $this->openedAt >= $this->cooldownSeconds) {
            $this->reset();
            return false;
        }
        return true;
    }

    public function recordSuccess(): void
    {
        $this->reset();
    }

    public function recordFailure(): void
    {
        $this->failures++;
        if ($this->failures >= $this->threshold) {
            $this->openedAt = time();
            Log::warning('Circuit breaker OPEN — Identity servis nedostupan');
        }
    }

    private function reset(): void
    {
        $this->failures = 0;
        $this->openedAt = null;
    }
}
