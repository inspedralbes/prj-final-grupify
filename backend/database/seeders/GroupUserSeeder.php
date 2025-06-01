<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\Group;

class GroupUserSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el rol "alumno"
        $alumnoRole = Role::where('name', 'alumno')->first();
        
        // Verificar si el rol alumno existe
        if (!$alumnoRole) {
            $this->command->error('El rol de alumno no existe. Ejecuta primero RoleSeeder.');
            return;
        }
        
        // Obtener el ID del rol
        $alumnoRoleId = $alumnoRole->id;
        
        // Obtener solo los usuarios con el rol "alumno"
        $alumnos = User::where('role_id', $alumnoRoleId)->pluck('id')->toArray();
        
        // Verificar si hay alumnos disponibles
        if (empty($alumnos)) {
            $this->command->error('No hay usuarios con rol de alumno. Ejecuta primero UserSeeder.');
            return;
        }

        // Obtener todos los grupos con su número de estudiantes requerido
        $grupos = Group::all();

        // Índice para recorrer los alumnos
        $indiceAlumno = 0;
        $totalAlumnos = count($alumnos);

        // Lista para almacenar las asignaciones
        $groupUsers = [];

        foreach ($grupos as $grupo) {
            $numEstudiantes = $grupo->number_of_students;

            for ($i = 0; $i < $numEstudiantes; $i++) {
                // Si no hay más alumnos disponibles, salir del loop
                if ($indiceAlumno >= $totalAlumnos) {
                    break;
                }

                $groupUsers[] = [
                    'group_id' => $grupo->id,
                    'user_id' => $alumnos[$indiceAlumno],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $indiceAlumno++;
            }
        }

        // Insertar las asignaciones en la tabla group_user
        DB::table('group_user')->insert($groupUsers);
    }
}
