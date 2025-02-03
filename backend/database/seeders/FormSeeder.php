<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Form;

class FormSeeder extends Seeder
{
    public function run()
    {
        // Verificar si el formulario ya existe antes de crear uno nuevo
        if (!Form::where('title', 'Formulario de prueba')->exists()) {
            Form::create([
                'title' => 'Formulario de prueba',
                'description' => 'Un formulario generado para pruebas.',
                'teacher_id' => 1,
                'is_global' => false,
            ]);
        }

        if (!Form::where('title', 'Formulario CESC')->exists()) {
            Form::create([
                'title' => 'Formulario CESC',
                'description' => 'Conducta y Experiencias Sociales en Clase',
                'is_global' => true,
            ]);
        }

        if (!Form::where('title', 'Formulario Sociograma')->exists()) {
            Form::create([
                'title' => 'Formulario Sociograma',
                'description' => 'Relaciones entre estudiantes en el aula.',
                'is_global' => true,
            ]);
        }
    }
}
