<?php

namespace App\Services;

use App\Models\Question;

class QuestionService
{
    /**
     * Obtener todas las preguntas.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllQuestions()
    {
        return Question::all();
    }

    /**
     * Obtener una pregunta por su ID.
     *
     * @param int $id
     * @return Question|null
     */
    public function getQuestionById($id)
    {
        return Question::find($id);
    }

    /**
     * Crear una nueva pregunta.
     *
     * @param array $data
     * @return Question
     */
    public function createQuestion(array $data)
    {
        return Question::create($data);
    }

    /**
     * Actualizar una pregunta existente.
     *
     * @param int $id
     * @param array $data
     * @return Question|null
     */
    public function updateQuestion($id, array $data)
    {
        $question = $this->getQuestionById($id);

        if ($question) {
            $question->update($data);
        }

        return $question;
    }

    /**
     * Eliminar una pregunta por su ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteQuestion($id)
    {
        $question = $this->getQuestionById($id);

        if ($question) {
            return $question->delete();
        }

        return false;
    }
}
