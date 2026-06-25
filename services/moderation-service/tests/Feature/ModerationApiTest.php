<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModerationApiTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertOk()
            ->assertJsonStructure(['status', 'service']);
    }

    public function test_protected_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/prijava');

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }
}
