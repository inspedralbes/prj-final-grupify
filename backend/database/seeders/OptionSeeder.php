<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Option;

class OptionSeeder extends Seeder
{
    public function run()
    {
        // Crear opcions bàsiques relacionades amb la primera pregunta amb ID 1
        Option::create([
            'question_id' => 1, // Assegura't que existeixi una Question amb ID 1
            'text' => 'Vermell',
            'value' => 1,
        ]);

        Option::create([
            'question_id' => 1,
            'text' => 'Blau',
            'value' => 2,
        ]);

        // Crear opcions per a una altra pregunta amb ID 2
        Option::create([
            'question_id' => 2, // Assegura't que existeixi una Question amb ID 2
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
