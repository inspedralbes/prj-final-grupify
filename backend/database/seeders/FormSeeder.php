<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Form;

class FormSeeder extends Seeder
{
    public function run()
    {
        // Verificar si el formulari ja existeix abans de crear-ne un de nou
        if (!Form::where('title', 'Formulari de prova')->exists()) {
            Form::create([
                'title' => 'Formulari de prova',
                'description' => 'Un formulari generat per a proves.',
                'teacher_id' => 1,
                'is_global' => false,
            ]);
        }

        if (!Form::where('title', 'Formulari CESC')->exists()) {
            Form::create([
                'title' => 'Formulari CESC',
                'description' => 'Conducta i Experiències Socials a Classe',
                'is_global' => true,
            ]);
        }

        if (!Form::where('title', 'Formulari Sociograma')->exists()) {
            Form::create([
                'title' => 'Formulari Sociograma',
                'description' => 'Relacions entre estudiants a l\'aula.',
                'is_global' => true,
            ]);
        }

        if (!Form::where('title', 'Formulari d’Autoavaluació')->exists()) {
            Form::create([
                'title' => 'Formulari d’Autoavaluació',
                'description' => 'Avaluació personal del rendiment acadèmic.',
                'is_global' => true,
            ]);
        }
    }
}
