<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Division;

class CourseSeeder extends Seeder
{
    public function run()
    {
        // Crear cursos
        Course::create([
            'name' => '1 ESO',
        ]);

        Course::create([
            'name' => '2 ESO'
        ]);

        Course::create([
            'name' => '3 ESO'
        ]);

        Course::create([
            'name' => '4 ESO'
        ]);

        Course::create([
            'name' => '1 BACHILLERATO',
        ]);

        Course::create([
            'name' => '2 BACHILLERATO'
        ]);
    }
}
