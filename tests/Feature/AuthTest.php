<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('Auth_LoginSuccess: inicia sesion con credenciales validas', function () {
    User::factory()->create([
        'email'    => 'usuario@test.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email'    => 'usuario@test.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'access_token',
                 'token_type',
                 'user',
             ]);
});

it('Auth_LoginFail: rechaza login con contrasena incorrecta', function () {
    User::factory()->create([
        'email'    => 'usuario@test.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email'    => 'usuario@test.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
             ->assertJsonFragment([
                 'message' => 'Invalid credentials',
             ]);
});

it('Auth_Logout: cierra sesion e invalida el token', function () {
    User::factory()->create([
        'email'    => 'usuario@test.com',
        'password' => 'password123',
    ]);

    $login = $this->postJson('/api/v1/login', [
        'email'    => 'usuario@test.com',
        'password' => 'password123',
    ]);

    $token = $login->json('access_token');

    $response = $this->withHeader('Authorization', "Bearer $token")
                     ->postJson('/api/v1/logout');

    $response->assertStatus(200)
             ->assertJsonFragment([
                 'message' => 'Logged out successfully',
             ]);
});

it('Auth_GetProfile: retorna datos del usuario autenticado', function () {
    User::factory()->create([
        'name'     => 'Juan Perez',
        'email'    => 'juan@test.com',
        'password' => 'password123',
    ]);

    $login = $this->postJson('/api/v1/login', [
        'email'    => 'juan@test.com',
        'password' => 'password123',
    ]);

    $token = $login->json('access_token');

    $response = $this->withHeader('Authorization', "Bearer $token")
                     ->getJson('/api/v1/profile');

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'user' => ['id', 'name', 'email'],
             ]);
});
