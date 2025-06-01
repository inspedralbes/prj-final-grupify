<?php

namespace App\Services;

use App\Models\Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormService
{
    public function createForm(array $formData): Form
    {

        return DB::transaction(function () use ($formData) {
            $form = Form::create([
                'title' => $formData['title'],
                'teacher_id' => $formData['teacher_id'],
                'description' => $formData['description'] ?? null,
                'status' => true,
                'is_global' => false,
                'date_limit' => $formData['date_limit'],
                'time_limit' => $formData['time_limit'],
            ]);

            foreach ($formData['questions'] as $questionData) {
                $question = $form->questions()->create([
                    'title' => $questionData['title'],
                    'type' => $questionData['type'],
                    'placeholder' => $questionData['placeholder'] ?? null,
                    'context' => $questionData['context'] ?? null,
                ]);

                if (!empty($questionData['options'])) {
                    foreach ($questionData['options'] as $optionData) {
                        $question->options()->create([
                            'text' => $optionData['text'],
                            'value' => $optionData['value'] ?? 0,
                        ]);
                    }
                }
            }

            return $form;
        });
    }

    public function getAllForms()
    {
        return Form::all();
    }
}
