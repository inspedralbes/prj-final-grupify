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
     * Executar el seeder per generar respostes d'autoavaluació de competències
     */
    public function run()
    {
        // Obtenir tots els estudiants que tenen rol d'estudiant
        $studentRoleId = \DB::table('roles')->where('name', 'student')->value('id');
        $students = User::where('role_id', $studentRoleId)->get();

        // Obtenir totes les competències
        $competences = Competence::all();
        
        // Buscar o crear un formulari per a autoavaluació de competències
        $form = Form::firstOrCreate(
            ['title' => 'Autoavaluació de Competències'],
            [
                'description' => 'Formulari per a l\'autoavaluació de competències dels estudiants',
                'status' => 'active',
                'type' => 'competence'
            ]
        );
        
        // Crear preguntes per a cada competència si no existeixen
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
            
            // Associar la pregunta amb la competència si no ho està ja
            if (!$question->competences()->where('competence_id', $competence->id)->exists()) {
                $question->competences()->attach($competence->id);
            }
        }
        
        // Generar respostes per a cada estudiant
        foreach ($students as $student) {
            // Generar respostes per als últims 4 anys
            for ($yearOffset = 0; $yearOffset < 4; $yearOffset++) {
                $year = now()->subYears($yearOffset);
                
                // Per a cada competència, crear una resposta aleatòria
                foreach ($competences as $competence) {
                    // Obtenir les preguntes associades a aquesta competència
                    $questions = Question::whereHas('competences', function ($query) use ($competence) {
                        $query->where('competences.id', $competence->id);
                    })->where('form_id', $form->id)->get();
                    
                    foreach ($questions as $question) {
                        // Generar un valor base per mantenir coherència entre anys
                        $baseValue = mt_rand(5, 8); // Valor base entre 5 i 8
                        
                        // Afegir variació segons l'any (tendència a millorar amb el temps)
                        $yearFactor = $yearOffset * 0.5; // Factor incremental per any (més recent = millor valoració)
                        $randomVariation = (mt_rand(-10, 10) / 10); // Variació aleatòria entre -1 i 1
                        
                        // Calcular el valor final amb límits entre 0 i 10
                        $rating = max(0, min(10, $baseValue + $yearFactor + $randomVariation));
                        
                        // Crear o actualitzar la resposta
                        Answer::updateOrCreate(
                            [
                                'user_id' => $student->id,
                                'form_id' => $form->id,
                                'question_id' => $question->id,
                                // Assegurar-se que només hi hagi una resposta per any
                                'created_at' => Carbon::create($year->year, mt_rand(1, 12), mt_rand(1, 28))
                            ],
                            [
                                'answer' => json_encode(['competence_id' => $competence->id]),
                                'rating' => round($rating, 1), // Arrodonir a 1 decimal
                                'answer_type' => 'competence_rating',
                                'updated_at' => Carbon::create($year->year, mt_rand(1, 12), mt_rand(1, 28))
                            ]
                        );
                    }
                }
            }
        }
        
        $this->command->info('S\'han generat respostes d\'autoavaluació de competències per a tots els estudiants.');
    }
}
