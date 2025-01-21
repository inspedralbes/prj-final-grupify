<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Option;

class OptionSeeder extends Seeder
{
    public function run()
    {
        // Crear opciones básicas relacionadas a la primera pregunta con ID 1
        Option::create([
            'question_id' => 1, // Asegúrate de que exista una Question con ID 1
            'text' => 'Rojo',
            'value' => 1,
        ]);

        Option::create([
            'question_id' => 1,
            'text' => 'Azul',
            'value' => 2,
        ]);

        // Crear opciones para otra pregunta con ID 2
        Option::create([
            'question_id' => 2, // Asegúrate de que exista una Question con ID 2
            'text' => 'Pizza',
            'value' => 1,
        ]);

        Option::create([
            'question_id' => 2,
            'text' => 'Pasta',
            'value' => 2,
        ]);
    }
}
