<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function createBook(int $copias = 3): Book
    {
        return Book::factory()->create([
            'available_copies' => $copias,
            'is_available'     => true,
        ]);
    }

    private function createLoan(User $user, Book $book, bool $devuelto = false): Loan
    {
        return Loan::create([
            'book_id'        => $book->id,
            'user_id'        => $user->id,
            'requester_name' => $user->name,
            'return_at'      => $devuelto ? now() : null,
        ]);
    }

    // =============================================
    //  PRESTAR
    // =============================================

    public function test_estudiante_puede_prestar_un_libro(): void
    {
        $estudiante = $this->createUser('estudiante');
        $book = $this->createBook();

        $response = $this->actingAs($estudiante, 'sanctum')
            ->postJson('/api/v1/loans', [
                'book_id'        => $book->id,
                'requester_name' => $estudiante->name,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Prestamo registrado exitosamente.']);

        $this->assertDatabaseHas('loans', [
            'book_id' => $book->id,
            'user_id' => $estudiante->id,
        ]);

        $this->assertEquals(
            $book->available_copies - 1,
            $book->fresh()->available_copies
        );
    }

    public function test_docente_puede_prestar_un_libro(): void
    {
        $docente = $this->createUser('docente');
        $book = $this->createBook();

        $response = $this->actingAs($docente, 'sanctum')
            ->postJson('/api/v1/loans', [
                'book_id'        => $book->id,
                'requester_name' => $docente->name,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Prestamo registrado exitosamente.']);
    }

    public function test_bibliotecario_no_puede_prestar_libro(): void
    {
        $biblio = $this->createUser('bibliotecario');
        $book = $this->createBook();

        $response = $this->actingAs($biblio, 'sanctum')
            ->postJson('/api/v1/loans', [
                'book_id'        => $book->id,
                'requester_name' => $biblio->name,
            ]);

        $response->assertStatus(403);
    }

    public function test_no_se_puede_prestar_sin_copias_disponibles(): void
    {
        $estudiante = $this->createUser('estudiante');
        $book = $this->createBook(0);
        $book->update(['available_copies' => 0, 'is_available' => false]);

        $response = $this->actingAs($estudiante, 'sanctum')
            ->postJson('/api/v1/loans', [
                'book_id'        => $book->id,
                'requester_name' => $estudiante->name,
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'No hay copias disponibles para este libro.']);
    }

    public function test_no_se_puede_prestar_sin_autenticacion(): void
    {
        $book = $this->createBook();

        $response = $this->postJson('/api/v1/loans', [
            'book_id'        => $book->id,
            'requester_name' => 'Anonimo',
        ]);

        $response->assertStatus(401);
    }

    // =============================================
    //  DEVOLVER
    // =============================================

    public function test_estudiante_puede_devolver_su_prestamo(): void
    {
        $estudiante        = $this->createUser('estudiante');
        $book              = $this->createBook();
        $loan              = $this->createLoan($estudiante, $book);
        $copias_originales = $book->available_copies;
        $book->update(['available_copies' => $book->available_copies - 1]);

        $response = $this->actingAs($estudiante, 'sanctum')
            ->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Devolucion registrada exitosamente.']);

        $this->assertNotNull($loan->fresh()->return_at);
        $this->assertEquals(
            $copias_originales,
            $book->fresh()->available_copies
        );
    }

    public function test_bibliotecario_puede_devolver_cualquier_prestamo(): void
    {
        $estudiante = $this->createUser('estudiante');
        $biblio     = $this->createUser('bibliotecario');
        $book       = $this->createBook();
        $loan       = $this->createLoan($estudiante, $book);
        $book->update(['available_copies' => $book->available_copies - 1]);

        $response = $this->actingAs($biblio, 'sanctum')
            ->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(200);
    }

    public function test_no_se_puede_devolver_prestamo_ya_devuelto(): void
    {
        $estudiante = $this->createUser('estudiante');
        $book       = $this->createBook();
        $loan       = $this->createLoan($estudiante, $book, true);

        $response = $this->actingAs($estudiante, 'sanctum')
            ->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Este prestamo ya fue devuelto.']);
    }

    public function test_no_se_puede_devolver_prestamo_inexistente(): void
    {
        $estudiante = $this->createUser('estudiante');

        $response = $this->actingAs($estudiante, 'sanctum')
            ->postJson('/api/v1/loans/999/return');

        $response->assertStatus(404);
    }

    // =============================================
    //  HISTORIAL
    // =============================================

    public function test_bibliotecario_ve_historial_completo(): void
    {
        $biblio     = $this->createUser('bibliotecario');
        $estudiante = $this->createUser('estudiante');
        $docente    = $this->createUser('docente');
        $book       = $this->createBook(5);

        $this->createLoan($estudiante, $book);
        $this->createLoan($docente, $book);

        $response = $this->actingAs($biblio, 'sanctum')
            ->getJson('/api/v1/loans/history');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_estudiante_solo_ve_sus_prestamos(): void
    {
        $estudiante = $this->createUser('estudiante');
        $docente    = $this->createUser('docente');
        $book       = $this->createBook(5);

        $this->createLoan($estudiante, $book);
        $this->createLoan($docente, $book);

        $response = $this->actingAs($estudiante, 'sanctum')
            ->getJson('/api/v1/loans/history');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_docente_solo_ve_sus_prestamos(): void
    {
        $estudiante = $this->createUser('estudiante');
        $docente    = $this->createUser('docente');
        $book       = $this->createBook(5);

        $this->createLoan($estudiante, $book);
        $this->createLoan($docente, $book);

        $response = $this->actingAs($docente, 'sanctum')
            ->getJson('/api/v1/loans/history');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_historial_sin_autenticacion_retorna_401(): void
    {
        $response = $this->getJson('/api/v1/loans/history');

        $response->assertStatus(401);
    }
}