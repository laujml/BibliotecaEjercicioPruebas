<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    /**
     * Solo Docentes y Estudiantes pueden registrar un préstamo.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['docente', 'estudiante']);
    }

    /**
     * El dueño del préstamo O el bibliotecario pueden devolver.
     */
    public function return(User $user, Loan $loan): bool
    {
        return $user->id === $loan->user_id
            || $user->role === 'bibliotecario';
    }
}
