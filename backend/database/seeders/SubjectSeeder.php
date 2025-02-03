<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        // Crear asignaturas
        Subject::create([
            'name' => 'Algebra',
            'description' => 'An introductory course to Algebra',
        ]);

        Subject::create([
            'name' => 'Calculus',
            'description' => 'An introductory course to Calculus',
        ]);

        Subject::create([
            'name' => 'Physics',
            'description' => 'Basics of Physics covering mechanics, optics, and more',
        ]);

        Subject::create([
            'name' => 'Chemistry',
            'description' => 'Introduction to chemical reactions and processes',
        ]);

        Subject::create([
            'name' => 'Biology',
            'description' => 'An overview of biological systems and organisms',
        ]);

        Subject::create([
            'name' => 'Literature',
            'description' => 'A deep dive into classic and modern literature',
        ]);

        Subject::create([
            'name' => 'History',
            'description' => 'World history from ancient civilizations to modern times',
        ]);

        Subject::create([
            'name' => 'Programming',
            'description' => 'Introduction to programming languages and concepts',
        ]);

        Subject::create([
            'name' => 'Economics',
            'description' => 'Understanding the basics of micro and macroeconomics',
        ]);

        Subject::create([
            'name' => 'Philosophy',
            'description' => 'Exploration of philosophical concepts and thinkers',
        ]);
        // Crear más asignaturas según lo necesites
    }
}
