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


test('books get detail returns book by id', function () {
    $user = User::factory()->student()->create();
    $book = Book::factory()->create([
        'title' => 'Clean Code',
        'description' => 'A handbook of agile software craftsmanship',
        'ISBN' => '9780132350884',  
        'total_copies' => 5,
        'available_copies' => 3,
        'is_available' => true
    ]);
    
    $response = $this->actingAs($user)
        ->getJson("/api/v1/books/{$book->id}");
    
    $response->assertStatus(200)
        ->assertJson([
            'id' => $book->id,
            'title' => 'Clean Code',
            'ISBN' => '9780132350884',  
            'total_copies' => 5
        ]);
});

test('books get detail returns 404 for non-existent book', function () {
    $user = User::factory()->student()->create();
    
    $response = $this->actingAs($user)
        ->getJson('/api/v1/books/999');
    
    $response->assertStatus(404);
});

test('books get detail requires authentication', function () {
    $book = Book::factory()->create();
    
    $response = $this->getJson("/api/v1/books/{$book->id}");
    
    $response->assertStatus(401);
});

test('teacher can get book detail', function () {
    $user = User::factory()->teacher()->create();
    $book = Book::factory()->create();
    
    $response = $this->actingAs($user)
        ->getJson("/api/v1/books/{$book->id}");
    
    $response->assertStatus(200);
});

test('librarian can get book detail', function () {
    $user = User::factory()->librarian()->create();
    $book = Book::factory()->create();
    
    $response = $this->actingAs($user)
        ->getJson("/api/v1/books/{$book->id}");
    
    $response->assertStatus(200);
});