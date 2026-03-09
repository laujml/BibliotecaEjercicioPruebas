<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * Determina si el usuario puede ver cualquier modelo.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver el modelo.
     */
    public function view(User $user, Book $book): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede crear modelos.
     */
    public function create(User $user): bool
    {
        // Solo los bibliotecarios pueden crear libros
        return $user->hasRole('librarian');
    }

    /**
     * Determina si el usuario puede actualizar el modelo.
     */
    public function update(User $user, Book $book): bool
    {
        // Solo los bibliotecarios pueden actualizar libros
        return $user->hasRole('librarian');
    }

    /**
     * Determina si el usuario puede eliminar el modelo.
     */
    public function delete(User $user, Book $book): bool
    {
        // solo los bibliotecarios pueden eliminar libros
        return $user->hasRole('librarian');
    }

    /**
     * Determina si el usuario puede restaurar el modelo.
     */
    public function restore(User $user, Book $book): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede eliminar permanentemente el modelo.
     */
    public function forceDelete(User $user, Book $book): bool
    {
        return true;
    }
}
