<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Verificar si la tabla roles existe
        if (!Schema::hasTable('roles')) {
            $this->command->error('La tabla roles no existe. Ejecuta las migraciones primero.');
            return;
        }

        // Asegúrate de que los roles básicos existan
        // Usamos updateOrCreate para evitar duplicados
        $roles = [
            [
                'name' => 'profesor',
                'description' => 'Pot assignar formularis, veure totes les respostes, accedir a totes les anàlisis i gestionar classes.'
            ],
            [
                'name' => 'alumne',
                'description' => 'Pot respondre formularis assignats i veure la seva pròpia informació.'
            ],
            [
                'name' => 'administrador',
                'description' => 'Té accés complet a totes les funcionalitats del sistema.'
            ],
            [
                'name' => 'tutor',
                'description' => 'Similar a un professor però només pot assignar formularis i veure qui ha respost. No pot veure les respostes ni les anàlisis detallades.'
            ],
            [
                'name' => 'orientador',
                'description' => 'Té accés a tots els formularis i les seves anàlisis però no pot enviar formularis als alumnes.'
            ],
            // Aquí se pueden agregar más roles si es necesario
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
        
        $this->command->info('Roles creados/actualizados correctamente.');
    }
}
