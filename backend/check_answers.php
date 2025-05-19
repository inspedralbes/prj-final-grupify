<?php
// Cargar el entorno de Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Estudiante a verificar
$studentId = 272;
$formId = 4;

// Consultar respuestas directamente
echo "Verificando respuestas para estudiante ID: $studentId, formulario ID: $formId\n\n";

// Verificar si existe en la tabla form_user
$formUser = DB::table('form_user')
    ->where('user_id', $studentId)
    ->where('form_id', $formId)
    ->first();

echo "Estado en form_user:\n";
echo $formUser ? "Encontrado - Answered: " . ($formUser->answered ? "Sí" : "No") . "\n" : "No encontrado\n";
echo "Detalles: " . print_r($formUser, true) . "\n\n";

// Consultar respuestas directamente en la tabla answers
$answers = DB::table('answers')
    ->where('user_id', $studentId)
    ->where('form_id', $formId)
    ->get();

echo "Respuestas en la tabla 'answers':\n";
echo "Total de respuestas: " . count($answers) . "\n";
foreach ($answers as $answer) {
    echo "Question ID: {$answer->question_id}, Value: ";
    
    if (!empty($answer->rating)) {
        echo "Rating: {$answer->rating}";
    } elseif (!empty($answer->answer)) {
        echo "Answer: {$answer->answer}";
    } else {
        echo "Sin valor";
    }
    
    echo "\n";
}

// Verificar si hay preguntas para el formulario 4
echo "\nPreguntas del formulario 4:\n";
$questions = DB::table('questions')
    ->where('form_id', $formId)
    ->get();

echo "Total de preguntas: " . count($questions) . "\n";
foreach ($questions as $q) {
    echo "ID: {$q->id}, Texto: {$q->text}\n";
}

echo "\nCompetencias en la tabla 'competences':\n";
$competences = DB::table('competences')->get();
echo "Total de competencias: " . count($competences) . "\n";
foreach ($competences as $c) {
    echo "ID: {$c->id}, Nombre: {$c->name}\n";
}
