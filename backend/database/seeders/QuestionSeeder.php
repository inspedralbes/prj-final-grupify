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
            throw new \Exception('Un o mes formularis no es troban en la base de dades.');
        }

        // Inserta preguntas asociadas al formulario 1
        Question::create([
            'form_id' => $form1->id,
            'title' => 'Quin és el teu color favorit?',
        ]);

        Question::create([
            'form_id' => $form1->id,
            'title' => 'Què opines sobre la intel·ligència artificial?',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Em cau bé',
        ]);

        // Inserta preguntas asociadas al formulario 2
        Question::create([
            'form_id' => $form2->id,
            'title' => 'No em cau bé',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Difon rumors',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Ajuda als altres',
        ]);

        // Y así sucesivamente para las demás preguntas...
        Question::create([
            'form_id' => $form2->id,
            'title' => 'Dona empentes',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'No deixa participar',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Anima als altres',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Insulta',
        ]);

        // Y más preguntas para el formulario 2...
        Question::create([
            'form_id' => $form2->id,
            'title' => 'A qui donen empentes?',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'A qui insulten o ridiculitzen?',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'A qui no deixen participar?',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Els meus amics / amigues',
        ]);

        // Inserta preguntas asociadas al formulario 1
        Question::create([
            'form_id' => $form3->id,
            'title' => 'Amb qui prefereixes treballar?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => 'Amb qui prefereixes no treballar?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => 'Amb qui has treballat anteriorment?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => 'Qui té habilitats de lideratge?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => 'Qui té habilitats de creativitat?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => 'Qui té habilitats d\'organització?',
        ]);
        Question::create([
            'form_id' => $form3->id,
            'title' => 'Amb qui no has treballat anteriorment?',
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
