<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles si no existen
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $librarianRole = Role::firstOrCreate(['name' => 'librarian']);

        // Crear usuario estudiante
        $student = User::firstOrCreate(
            ['email' => 'estudiante@test.com'],
            [
                'name' => 'Estudiante Test',
                'password' => bcrypt('test123'),
            ]
        );
        $student->assignRole($studentRole);

        // Crear usuario docente
        $teacher = User::firstOrCreate(
            ['email' => 'docente@test.com'],
            [
                'name' => 'Docente Test',
                'password' => bcrypt('test123'),
            ]
        );
        $teacher->assignRole($teacherRole);

        // Crear usuario bibliotecario
        $librarian = User::firstOrCreate(
            ['email' => 'bibliotecario@test.com'],
            [
                'name' => 'Bibliotecario Test',
                'password' => bcrypt('test123'),
            ]
        );
        $librarian->assignRole($librarianRole);

    }
}