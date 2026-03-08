<?php

use App\Models\User;
use App\Models\Book;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
});


test('books list all returns successful response for authenticated users', function () {
    $user = User::factory()->student()->create();
    Book::factory()->count(5)->create();
    
    $response = $this->actingAs($user)
        ->getJson('/api/v1/books');
    
    $response->assertStatus(200)
        ->assertJsonCount(5);
});

test('books list requires authentication', function () {
    $response = $this->getJson('/api/v1/books');
    
    $response->assertStatus(401);
});

test('student can list books', function () {
    $user = User::factory()->student()->create();
    
    $response = $this->actingAs($user)
        ->getJson('/api/v1/books');
    
    $response->assertStatus(200);
});

test('teacher can list books', function () {
    $user = User::factory()->teacher()->create();
    
    $response = $this->actingAs($user)
        ->getJson('/api/v1/books');
    
    $response->assertStatus(200);
});

test('librarian can list books', function () {
    $user = User::factory()->librarian()->create();
    
    $response = $this->actingAs($user)
        ->getJson('/api/v1/books');
    
    $response->assertStatus(200);
});