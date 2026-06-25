<?php

namespace Tests\Unit;

use App\Infrastructure\Clients\CircuitBreaker;
use Tests\TestCase;

class CircuitBreakerTest extends TestCase
{
    public function test_opens_after_threshold_failures(): void
    {
        $cb = new CircuitBreaker(2, 60);
        $cb->recordFailure();
        $this->assertFalse($cb->isOpen());
        $cb->recordFailure();
        $this->assertTrue($cb->isOpen());
    }

    public function test_resets_on_success(): void
    {
        $cb = new CircuitBreaker(2, 60);
        $cb->recordFailure();
        $cb->recordSuccess();
        $cb->recordFailure();
        $this->assertFalse($cb->isOpen());
    }
}
