<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    public function test_login_with_empty_body_returns_422(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    public function test_login_with_invalid_email_returns_422(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nije-email',
            'lozinka' => 'nekaLozinka123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_without_password_returns_422(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'valid@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lozinka']);
    }
}
