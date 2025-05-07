<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Form;
use App\Models\User;
use App\Models\Role;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\DB;

class TutorFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar el rol de tutor
        $tutorRole = Role::where('name', 'tutor')->first();
        
        if (!$tutorRole) {
            $this->command->error('No se encontró el rol de tutor. Ejecute primero RoleSeeder.');
            return;
        }
        
        // Buscar un usuario tutor para asignar como creador de los formularios
        $tutor = User::where('role_id', $tutorRole->id)->first();
        
        if (!$tutor) {
            $this->command->error('No se encontró ningún usuario con rol de tutor. Ejecute primero TutorSeeder.');
            return;
        }
        
        // Crear formulario de sociograma
        $sociogramaForm = Form::create([
            'title' => 'Sociograma - Relaciones interpersonales',
            'description' => 'Este formulario ayuda a identificar las relaciones sociales dentro del aula',
            'status' => 1, // Activo
            'teacher_id' => $tutor->id,
            'is_global' => 0, // No es global
            'date_limit' => now()->addDays(30),
            'time_limit' => '23:59',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Crear preguntas para el sociograma
        $sociogramaPreguntas = [
            [
                'title' => '¿Con qué compañeros/as de clase te gustaría trabajar en equipo?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona a tus compañeros/as',
                'context' => 'Elección positiva para trabajo'
            ],
            [
                'title' => '¿Con qué compañeros/as de clase NO te gustaría trabajar en equipo?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona a tus compañeros/as',
                'context' => 'Elección negativa para trabajo'
            ],
            [
                'title' => '¿Con qué compañeros/as de clase te gustaría pasar tu tiempo libre?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona a tus compañeros/as',
                'context' => 'Elección positiva para ocio'
            ],
            [
                'title' => '¿Con qué compañeros/as de clase NO te gustaría pasar tu tiempo libre?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona a tus compañeros/as',
                'context' => 'Elección negativa para ocio'
            ]
        ];
        
        foreach ($sociogramaPreguntas as $pregunta) {
            Question::create([
                'title' => $pregunta['title'],
                'type' => $pregunta['type'],
                'placeholder' => $pregunta['placeholder'],
                'context' => $pregunta['context'],
                'form_id' => $sociogramaForm->id
            ]);
        }
        
        // Crear formulario de CESC (Cuestionario de Evaluación Socioemocional y de Conducta)
        $cescForm = Form::create([
            'title' => 'CESC - Evaluación Socioemocional',
            'description' => 'Cuestionario para identificar situaciones de conflicto en el aula',
            'status' => 1, // Activo
            'teacher_id' => $tutor->id,
            'is_global' => 0, // No es global
            'date_limit' => now()->addDays(30),
            'time_limit' => '23:59',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Crear preguntas para el CESC
        $cescPreguntas = [
            [
                'title' => '¿Quién/es de tus compañeros/as suele/n intimidar a otros?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona a tus compañeros/as',
                'context' => 'Identificación de intimidación'
            ],
            [
                'title' => '¿Quién/es de tus compañeros/as suele/n ser víctima/s de intimidación?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona a tus compañeros/as',
                'context' => 'Identificación de víctimas'
            ],
            [
                'title' => '¿Quién/es de tus compañeros/as suele/n ayudar a quien es intimidado?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona a tus compañeros/as',
                'context' => 'Identificación de defensores'
            ],
            [
                'title' => '¿Quién/es de tus compañeros/as suele/n reírse o animar situaciones de intimidación?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona a tus compañeros/as',
                'context' => 'Identificación de reforzadores'
            ]
        ];
        
        foreach ($cescPreguntas as $pregunta) {
            Question::create([
                'title' => $pregunta['title'],
                'type' => $pregunta['type'],
                'placeholder' => $pregunta['placeholder'],
                'context' => $pregunta['context'],
                'form_id' => $cescForm->id
            ]);
        }
        
        // Crear formulario de autoevaluación (global)
        $autoevaluacionForm = Form::create([
            'title' => 'Autoevaluación de Desempeño Académico',
            'description' => 'Evalúa tu propio rendimiento y compromiso con tu aprendizaje',
            'status' => 1, // Activo
            'teacher_id' => $tutor->id,
            'is_global' => 1, // Es global
            'date_limit' => now()->addDays(60),
            'time_limit' => '23:59',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Crear preguntas para la autoevaluación
        $autoevaluacionPreguntas = [
            [
                'title' => '¿Cómo calificarías tu participación en clase?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona una opción'
            ],
            [
                'title' => '¿Con qué frecuencia realizas las tareas asignadas?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona una opción'
            ],
            [
                'title' => '¿Cómo evaluarías tu colaboración en trabajos grupales?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona una opción'
            ],
            [
                'title' => '¿Qué aspectos crees que deberías mejorar?',
                'type' => 'multiple',
                'placeholder' => 'Selecciona una o varias opciones'
            ]
        ];
        
        // Opciones para las preguntas de autoevaluación
        $opcionesEvaluacion = [
            'Excelente', 'Buena', 'Regular', 'Insuficiente'
        ];
        
        $opcionesFrecuencia = [
            'Siempre', 'Casi siempre', 'A veces', 'Raramente', 'Nunca'
        ];
        
        $opcionesMejora = [
            'Atención en clase', 'Participación', 'Puntualidad', 'Organización', 
            'Estudio en casa', 'Colaboración con compañeros'
        ];
        
        // Crear las preguntas y opciones para la autoevaluación
        foreach ($autoevaluacionPreguntas as $index => $pregunta) {
            $questionObj = Question::create([
                'title' => $pregunta['title'],
                'type' => $pregunta['type'],
                'placeholder' => $pregunta['placeholder'],
                'form_id' => $autoevaluacionForm->id
            ]);
            
            // Asignar opciones diferentes según la pregunta
            if ($index == 0) { // Primera pregunta: calificación
                foreach ($opcionesEvaluacion as $value => $text) {
                    Option::create([
                        'text' => $text,
                        'value' => $value + 1,
                        'question_id' => $questionObj->id
                    ]);
                }
            } elseif ($index == 1) { // Segunda pregunta: frecuencia
                foreach ($opcionesFrecuencia as $value => $text) {
                    Option::create([
                        'text' => $text,
                        'value' => 5 - $value, // Invertir valores para que "Siempre" valga más
                        'question_id' => $questionObj->id
                    ]);
                }
            } elseif ($index == 2) { // Tercera pregunta: evaluación de colaboración
                foreach ($opcionesEvaluacion as $value => $text) {
                    Option::create([
                        'text' => $text,
                        'value' => $value + 1,
                        'question_id' => $questionObj->id
                    ]);
                }
            } elseif ($index == 3) { // Cuarta pregunta: aspectos a mejorar
                foreach ($opcionesMejora as $value => $text) {
                    Option::create([
                        'text' => $text,
                        'value' => 0, // Sin valor numérico, es una selección múltiple
                        'question_id' => $questionObj->id
                    ]);
                }
            }
        }
        
        $this->command->info('Se han creado los formularios para tutores:');
        $this->command->info('- Sociograma');
        $this->command->info('- CESC');
        $this->command->info('- Autoevaluación (global)');
    }
}
