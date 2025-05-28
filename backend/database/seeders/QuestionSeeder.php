<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Form;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        // Obtén els formularis amb ID específics
        $form1 = Form::find(1); // Formulari amb ID 1
        $form2 = Form::find(2); // Formulari amb ID 2
        $form3 = Form::find(3); // Formulari amb ID 3
        $form4 = Form::find(4); // Formulari amb ID 4


        if (!$form1 || !$form2 || !$form3 || !$form4) {
            throw new \Exception('Un o més formularis no es trobaren en la base de dades.');
        }

        // Insereix preguntes associades al formulari 1
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
            'title' => 'M\'agrada',
        ]);

        // Insereix preguntes associades al formulari 2
        Question::create([
            'form_id' => $form2->id,
            'title' => 'No m\'agrada',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Difon rumors',
        ]);

        Question::create([
            'form_id' => $form2->id,
            'title' => 'Ajuda als altres',
        ]);

        // I així successivament per a les altres preguntes...
        Question::create([
            'form_id' => $form2->id,
            'title' => 'Dóna empentes',
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

        // I més preguntes per al formulari 2...
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

        // Insereix preguntes associades al formulari 3
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

    // Insereix preguntes associades al formulari d'autoavaluació (form_id = 4)
        Question::create([
            'form_id' => $form4->id,
            'title' => 'Compleixo amb els terminis i responsabilitats assignades sense necessitat de recordatoris.',
            'type' => 'rating',
        ]);

        Question::create([
            'form_id' => $form4->id,
            'title' => 'Em comunico de manera efectiva amb els companys de l’equip i contribueixo a l’assoliment dels objectius de grup.',
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
