<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_it_can_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('test123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => $user->email,
            'password' => 'test123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user',
            ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email'    => 'user@test.com',
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'    => 'user@test.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Invalid credentials']);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Logged out successfully']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'name'  => 'John Doe',
            'email' => 'john@test.com',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name', 'email']])
            ->assertJsonPath('user.email', 'john@test.com');
    }
}