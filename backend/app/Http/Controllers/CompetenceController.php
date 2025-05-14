<?php

namespace App\Http\Controllers;

use App\Models\Competence;
use App\Models\Question;
use App\Models\Answer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompetenceController extends Controller
{
    /**
     * Obtener todas las competencias
     */
    public function index()
    {
        $competences = Competence::all();
        return response()->json($competences);
    }

    /**
     * Obtener una competencia específica
     */
    public function show($id)
    {
        $competence = Competence::with('questions')->findOrFail($id);
        return response()->json($competence);
    }

    /**
     * Obtener el historial de competencias para un estudiante
     */
    public function getStudentCompetences($studentId)
    {
        // Verificar si el estudiante existe
        $student = User::findOrFail($studentId);

        // Obtener todas las competencias
        $competences = Competence::all();

        // Obtener las respuestas del estudiante agrupadas por año académico
        $answers = Answer::where('user_id', $studentId)
            ->where('rating', '!=', null) // Solo incluir respuestas con valoración
            ->join('questions', 'answers.question_id', '=', 'questions.id')
            ->join('competence_question', 'questions.id', '=', 'competence_question.question_id')
            ->select(
                'answers.id',
                'answers.user_id',
                'answers.question_id',
                'answers.rating as value',
                'competence_question.competence_id',
                DB::raw('YEAR(answers.created_at) as year')
            )
            ->get();

        // Si no hay respuestas, devolver una respuesta vacía estructurada
        if ($answers->isEmpty()) {
            return response()->json([
                'years' => [],
                'competenciasData' => []
            ]);
        }

        // Obtener los años únicos de las respuestas en orden descendente
        $years = $answers->pluck('year')->unique()->sortDesc()->values()->toArray();

        // Formato esperado por el frontend
        $formattedData = [];

        foreach ($answers as $answer) {
            $formattedData[] = [
                'year' => (string)$answer->year,
                'competenciaId' => $answer->competence_id,
                'value' => (float)$answer->value
            ];
        }

        return response()->json([
            'years' => $years,
            'competenciasData' => $formattedData
        ]);
    }

    /**
     * Guardar las valoraciones de competencias de un usuario
     */
    public function storeUserCompetence(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'form_id' => 'required|exists:forms,id',
            'competences' => 'required|array',
            'competences.*.competence_id' => 'required|exists:competences,id',
            'competences.*.rating' => 'required|numeric|min:0|max:10',
        ]);

        $userId = $request->input('user_id');
        $formId = $request->input('form_id');
        $competencesData = $request->input('competences');

        $result = [];

        foreach ($competencesData as $competenceData) {
            $competenceId = $competenceData['competence_id'];
            $rating = $competenceData['rating'];

            // Buscar preguntas relacionadas con esta competencia
            $questions = Question::whereHas('competences', function ($query) use ($competenceId) {
                $query->where('competences.id', $competenceId);
            })->where('form_id', $formId)->get();

            foreach ($questions as $question) {
                // Guardar respuesta para cada pregunta asociada a esta competencia
                $answer = Answer::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'form_id' => $formId,
                        'question_id' => $question->id,
                    ],
                    [
                        'answer' => json_encode(['competence_id' => $competenceId]),
                        'rating' => $rating,
                        'answer_type' => 'competence_rating'
                    ]
                );

                $result[] = $answer;
            }
        }

        return response()->json([
            'message' => 'Competencias guardadas correctamente',
            'data' => $result
        ]);
    }
}
