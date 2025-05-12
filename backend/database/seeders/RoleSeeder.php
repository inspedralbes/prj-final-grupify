<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::updateOrCreate(
            ['name' => 'profesor'],
            ['description' => 'Pot assignar formularis, veure totes les respostes, accedir a totes les anàlisis i gestionar classes.']
        );
        
        Role::updateOrCreate(
            ['name' => 'alumno'],
            ['description' => 'Pot respondre formularis assignats i veure la seva pròpia informació.']
        );
        
        Role::updateOrCreate(
            ['name' => 'admin'],
            ['description' => 'Té accés complet a totes les funcionalitats del sistema.']
        );
        
        Role::updateOrCreate(
            ['name' => 'tutor'],
            ['description' => 'Similar a un professor però només pot assignar formularis i veure qui ha respost. No pot veure les respostes ni les anàlisis detallades.']
        );
        
        Role::updateOrCreate(
            ['name' => 'orientador'],
            ['description' => 'Té accés a tots els formularis i les seves anàlisis però no pot enviar formularis als alumnes.']
        );
    }
}
