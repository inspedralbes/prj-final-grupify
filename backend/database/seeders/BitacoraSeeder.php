<?php

namespace Database\Seeders;

use App\Models\Bitacora;
use App\Models\Group;
use Illuminate\Database\Seeder;

class BitacoraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los grupos (o un grupo específico si lo deseas)
        $groups = Group::all();

        foreach ($groups as $group) {
            // Crear la bitácora asociada al grupo
            $bitacora = Bitacora::create([
                'group_id' => $group->id, // Asociar la bitácora con el grupo
                'title' => 'Bitácora del Grupo ' . $group->name,
                'description' => 'Descripción general de las actividades del grupo '  . $group->name,
            ]);

            // Obtener los usuarios que pertenecen al grupo
            $users = $group->users; // Relación entre usuarios y grupos

            // Aquí ya no necesitamos la tabla bitacora_user, solo asociamos los usuarios directamente
            // Puedes agregar la relación entre los usuarios y la bitácora aquí si es necesario
            foreach ($users as $user) {
                // Relacionar usuarios con la bitácora (si fuera necesario crear alguna tabla intermedia)
                // $bitacora->users()->attach($user->id); 
                // Si no es necesario, simplemente se guarda la bitácora sin esta relación directa.
            }
        }
    }
}
