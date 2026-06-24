<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'data' => [
                'user',
                'token',
            ],
        ]);
    }

    #[Test]
    public function user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'data' => [
                'user',
                'token',
            ],
        ]);
    }

    #[Test]
    public function login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
        ]);
    }

    #[Test]
    public function authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
    }

    #[Test]
    public function user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
