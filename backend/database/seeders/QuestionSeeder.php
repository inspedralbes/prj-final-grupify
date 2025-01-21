<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Form;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        // Obtén los formularios con ID específicos
        $form1 = Form::find(1); // Formulario con ID 1
        $form2 = Form::find(2); // Formulario con ID 2
        $form3 = Form::find(3); // Formulario con ID 3

        if (!$form1 || !$form2 || !$form3) {
            throw new \Exception('Uno o más formularios no se encontraron en la base de datos.');
        }

        // Inserta preguntas asociadas al formulario 1
        Question::create([
            'form_id' => $form1->id,
            'title' => '¿Cuál es tu color favorito?',
        ]);

        Question::create([
            'form_id' => $form1->id,
            'title' => '¿Qué opinas sobre la inteligencia artificial?',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Me cae bien',
        ]);

        // Inserta preguntas asociadas al formulario 2
        Question::create([
            'form_id' => $form2->id,
            'title' => 'No me cae bien',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Difunde rumores',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Ayuda a los demás',
        ]);

        // Y así sucesivamente para las demás preguntas...
        Question::create([
            'form_id' => $form2->id,
            'title' => 'Da empujones',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'No deja participar',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Anima a los demás',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Insulta',
        ]);

        // Y más preguntas para el formulario 2...
        Question::create([
            'form_id' => $form2->id,
            'title' => '¿A quién dan empujones?',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => '¿A quién insultan o ridiculizan?',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => '¿A quién no dejan participar?',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Mis amigos / amigas',
        ]);

        // Inserta preguntas asociadas al formulario 1
        Question::create([
            'form_id' => $form3->id,
            'title' => '¿Con quién prefieres trabajar?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => '¿Con quién prefieres no trabajar?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => '¿Con quién has trabajado anteriormente?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => '¿Quién tiene habilidades de liderazgo?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => '¿Quién tiene habilidades de creatividad?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => '¿Quién tiene habilidades de organización?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => '¿Con quién no has trabajado anteriormente?',
        ]);
    }
}
