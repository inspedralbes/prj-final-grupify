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

            $users = $group->users; // Relación entre usuarios y grupos

            foreach ($users as $user) {
            }
        }
    }
}
