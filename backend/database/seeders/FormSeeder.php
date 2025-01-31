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
            ]);
        }

        if (!Form::where('title', 'Formulario CESC')->exists()) {
            Form::create([
                'title' => 'Formulario CESC',
                'description' => 'Conducta y Experiencias Sociales en Clase',
            ]);
        }
        
        if (!Form::where('title', 'Formulario Sociograma')->exists()) {
            Form::create([
                'title' => 'Formulario Sociograma',
                'description' => 'Relaciones entre estudiantes en el aula.',
            ]);
        }
    }
}
