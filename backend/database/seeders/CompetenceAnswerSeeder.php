<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Competence;
use App\Models\Question;
use App\Models\Form;
use App\Models\Answer;
use Carbon\Carbon;

class CompetenceAnswerSeeder extends Seeder
{
    /**
     * Ejecutar el seeder para generar respuestas de autoavaluación de competencias
     */
    public function run()
    {
        // Obtener todos los estudiantes que tienen rol de estudiante
        $studentRoleId = \DB::table('roles')->where('name', 'student')->value('id');
        $students = User::where('role_id', $studentRoleId)->get();

        // Obtener todas las competencias
        $competences = Competence::all();
        
        // Buscar o crear un formulario para autoavaluación de competencias
        $form = Form::firstOrCreate(
            ['title' => 'Autoavaluació de Competències'],
            [
                'description' => 'Formulari per a l\'autoavaluació de competències dels estudiants',
                'status' => 'active',
                'type' => 'competence'
            ]
        );
        
        // Crear preguntas para cada competencia si no existen
        foreach ($competences as $competence) {
            $question = Question::firstOrCreate(
                [
                    'title' => 'Valora el teu nivell de ' . strtolower($competence->name),
                    'form_id' => $form->id,
                    'type' => 'rating'
                ],
                [
                    'placeholder' => 'Valora del 0 al 10',
                    'context' => 'Autoavaluació de la competència ' . $competence->name
                ]
            );
            
            // Asociar la pregunta con la competencia si no lo está ya
            if (!$question->competences()->where('competence_id', $competence->id)->exists()) {
                $question->competences()->attach($competence->id);
            }
        }
        
        // Generar respuestas para cada estudiante
        foreach ($students as $student) {
            // Generar respuestas para los últimos 4 años
            for ($yearOffset = 0; $yearOffset < 4; $yearOffset++) {
                $year = now()->subYears($yearOffset);
                
                // Para cada competencia, crear una respuesta aleatoria
                foreach ($competences as $competence) {
                    // Obtener las preguntas asociadas a esta competencia
                    $questions = Question::whereHas('competences', function ($query) use ($competence) {
                        $query->where('competences.id', $competence->id);
                    })->where('form_id', $form->id)->get();
                    
                    foreach ($questions as $question) {
                        // Generar un valor base para mantener coherencia entre años
                        $baseValue = mt_rand(5, 8); // Valor base entre 5 y 8
                        
                        // Añadir variación según el año (tendencia a mejorar con el tiempo)
                        $yearFactor = $yearOffset * 0.5; // Factor incremental por año (más reciente = mejor valoración)
                        $randomVariation = (mt_rand(-10, 10) / 10); // Variación aleatoria entre -1 y 1
                        
                        // Calcular el valor final con límites entre 0 y 10
                        $rating = max(0, min(10, $baseValue + $yearFactor + $randomVariation));
                        
                        // Crear o actualizar la respuesta
                        Answer::updateOrCreate(
                            [
                                'user_id' => $student->id,
                                'form_id' => $form->id,
                                'question_id' => $question->id,
                                // Asegurarse de que solo haya una respuesta por año
                                'created_at' => Carbon::create($year->year, mt_rand(1, 12), mt_rand(1, 28))
                            ],
                            [
                                'answer' => json_encode(['competence_id' => $competence->id]),
                                'rating' => round($rating, 1), // Redondear a 1 decimal
                                'answer_type' => 'competence_rating',
                                'updated_at' => Carbon::create($year->year, mt_rand(1, 12), mt_rand(1, 28))
                            ]
                        );
                    }
                }
            }
        }
        
        $this->command->info('Se han generado respuestas de autoavaluación de competencias para todos los estudiantes.');
    }
}
