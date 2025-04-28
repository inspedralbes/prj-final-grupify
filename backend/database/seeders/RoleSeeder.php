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
            ['description' => 'Puede asignar formularios, ver todas las respuestas, acceder a todos los análisis y gestionar clases.']
        );
        
        Role::updateOrCreate(
            ['name' => 'alumno'],
            ['description' => 'Puede responder formularios asignados y ver su propia información.']
        );
        
        Role::updateOrCreate(
            ['name' => 'admin'],
            ['description' => 'Tiene acceso completo a todas las funcionalidades del sistema.']
        );
        
        Role::updateOrCreate(
            ['name' => 'tutor'],
            ['description' => 'Similar a un profesor pero solo puede asignar formularios y ver quiénes han respondido. No puede ver las respuestas ni los análisis detallados.']
        );
        
        Role::updateOrCreate(
            ['name' => 'orientador'],
            ['description' => 'Tiene acceso a todos los formularios y sus análisis pero no puede enviar formularios a los alumnos.']
        );
    }
}
