<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson(
            '/api/v1/auth/register',
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJsonPath(
                'success',
                true
            );

        $this->assertDatabaseHas(
            'users',
            [
                'email' => 'test@example.com',
            ]
        );
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson(
            '/api/v1/auth/login',
            [
                'email' => 'test@example.com',
                'password' => 'Password123!',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'success',
                true
            );
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson(
            '/api/v1/auth/login',
            [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]
        );

        $response
            ->assertStatus(401)
            ->assertJsonPath(
                'success',
                false
            );
    }
}