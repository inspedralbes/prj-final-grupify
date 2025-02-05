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
        $form4 = Form::find(4); // Formulario con ID 4


        if (!$form1 || !$form2 || !$form3 || !$form4) {
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

    // Inserta preguntas asociadas al formulario de autoevaluación (form_id = 4)
        Question::create([
            'form_id' => $form4->id,
            'title' => 'Compleixo amb els terminis i responsabilitats assignades sense necessitat de recordatoris.',
            'type' => 'rating',
        ]);

        Question::create([
            'form_id' => $form4->id,
            'title' => 'Em comunico de manera efectiva amb els companys de l’equip i contribueixo a l’assoliment dels objectius grupals.',
            'type' => 'rating',
        ]);

        Question::create([
            'form_id' => $form4->id,
            'title' => 'Organitzo i prioritzo bé les meves tasques per evitar l’estrès de l’última hora.',
            'type' => 'rating',
        ]);

        Question::create([
            'form_id' => $form4->id,
            'title' => 'Escolto activament als altres i m’asseguro d’expressar les meves idees de manera clara i respectuosa.',
            'type' => 'rating',
        ]);

        Question::create([
            'form_id' => $form4->id,
            'title' => 'M’adapto fàcilment a canvis inesperats en la feina o en els projectes.',
            'type' => 'rating',
        ]);

        Question::create([
            'form_id' => $form4->id,
            'title' => 'Assumeixo la responsabilitat dels projectes i guio els altres quan és necessari.',
            'type' => 'rating',
        ]);

        Question::create([
            'form_id' => $form4->id,
            'title' => 'Proporciono solucions creatives o idees innovadores quan m’enfronto a un problema.',
            'type' => 'rating',
        ]);

        Question::create([
            'form_id' => $form4->id,
            'title' => 'Tinc la iniciativa i començo tasques o projectes sense esperar que em donin instruccions.',
            'type' => 'rating',
        ]); 
        
    }
}
