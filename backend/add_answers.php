<?php
// Script para añadir respuestas al formulario de autoavaluación (ID 4) para un estudiante específico
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Parámetros
$studentId = 272; // ID del estudiante para el que añadiremos las respuestas
$formId = 4;      // ID del formulario de autoavaluación
$defaultRating = 3; // Valor por defecto (1-5) para las preguntas si no hay respuestas

// Competencias y sus IDs de pregunta
$competencias = [
    ['id' => 22, 'name' => 'Responsabilitat', 'rating' => $defaultRating],
    ['id' => 23, 'name' => 'Treball en equip', 'rating' => $defaultRating],
    ['id' => 24, 'name' => 'Gestió del temps', 'rating' => $defaultRating],
    ['id' => 25, 'name' => 'Comunicació', 'rating' => $defaultRating],
    ['id' => 26, 'name' => 'Adaptabilitat', 'rating' => $defaultRating],
    ['id' => 27, 'name' => 'Lideratge', 'rating' => $defaultRating],
    ['id' => 28, 'name' => 'Creativitat', 'rating' => $defaultRating],
    ['id' => 29, 'name' => 'Proactivitat', 'rating' => $defaultRating]
];

// Verificar si el estudiante existe
$student = DB::table('users')->where('id', $studentId)->first();
if (!$student) {
    echo "ERROR: El estudiante con ID $studentId no existe.\n";
    exit(1);
}

echo "Procesando respuestas para el estudiante: {$student->name} {$student->last_name} (ID: $studentId)\n\n";

// Para cada competencia, verificar si ya existe una respuesta
foreach ($competencias as $competencia) {
    $questionId = $competencia['id'];
    
    // Verificar si ya existe una respuesta para esta pregunta
    $respuestaExistente = DB::table('answers')
        ->where('user_id', $studentId)
        ->where('form_id', $formId)
        ->where('question_id', $questionId)
        ->first();
    
    if ($respuestaExistente) {
        echo "Ya existe una respuesta para la competencia '{$competencia['name']}' (ID: $questionId): ";
        if (!empty($respuestaExistente->rating)) {
            echo "Rating: {$respuestaExistente->rating}\n";
        } else if (!empty($respuestaExistente->answer)) {
            echo "Answer: {$respuestaExistente->answer}\n";
        } else {
            echo "Sin valor\n";
        }
        
        // Actualizar la respuesta existente para asegurarse de que tiene un valor
        if (empty($respuestaExistente->rating) && empty($respuestaExistente->answer)) {
            DB::table('answers')
                ->where('id', $respuestaExistente->id)
                ->update([
                    'rating' => $competencia['rating'],
                    'answer_type' => 'rating',
                    'updated_at' => now()
                ]);
            echo " -> Actualizada con rating: {$competencia['rating']}\n";
        }
    } else {
        // No existe, crear una nueva respuesta
        $id = DB::table('answers')->insertGetId([
            'user_id' => $studentId,
            'form_id' => $formId,
            'question_id' => $questionId,
            'rating' => $competencia['rating'],
            'answer' => '',
            'answer_type' => 'rating',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "Creada nueva respuesta para '{$competencia['name']}' (ID: $questionId) con rating: {$competencia['rating']}\n";
    }
}

// Verificar si hay un registro en form_user y actualizarlo o crearlo
$formUser = DB::table('form_user')
    ->where('user_id', $studentId)
    ->where('form_id', $formId)
    ->first();

if ($formUser) {
    // Actualizar el registro existente
    DB::table('form_user')
        ->where('user_id', $studentId)
        ->where('form_id', $formId)
        ->update([
            'answered' => true,
            'updated_at' => now()
        ]);
    
    echo "\nActualizado registro en form_user: answered = true\n";
} else {
    // Crear un nuevo registro
    DB::table('form_user')->insert([
        'user_id' => $studentId,
        'form_id' => $formId,
        'answered' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "\nCreado nuevo registro en form_user con answered = true\n";
}

echo "\nProceso completado exitosamente.\n";
