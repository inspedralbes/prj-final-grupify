<?php

namespace App\Observers;

use App\Models\FormUser;
use App\Models\FormAssignment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FormUserObserver
{
    /**
     * Handle the FormUser "created" event.
     */
    public function created(FormUser $formUser): void
    {
        $this->updateFormAssignmentCount($formUser);
    }

    /**
     * Handle the FormUser "updated" event.
     * Se dispara cuando un usuario marca un formulario como completado (answered cambia a true)
     */
    public function updated(FormUser $formUser): void
    {
        // Verificar si el campo 'answered' ha cambiado
        if ($formUser->wasChanged('answered')) {
            $this->updateFormAssignmentCount($formUser);
        }
    }

    /**
     * Handle the FormUser "deleted" event.
     */
    public function deleted(FormUser $formUser): void
    {
        $this->updateFormAssignmentCount($formUser);
    }

    /**
     * Actualiza el contador de respuestas en FormAssignment
     */
    private function updateFormAssignmentCount(FormUser $formUser): void
    {
        try {
            // Verificar si la tabla form_assignments existe
            if (Schema::hasTable('form_assignments')) {
                // Buscar la asignación correspondiente
                $formAssignment = FormAssignment::where('form_id', $formUser->form_id)
                    ->where('course_id', $formUser->course_id)
                    ->where('division_id', $formUser->division_id)
                    ->first();
                
                // Si existe la asignación, actualizar el contador
                if ($formAssignment) {
                    $formAssignment->updateResponseCount();
                    Log::info('Contador de respuestas actualizado para form_id: ' . $formUser->form_id . 
                              ', course_id: ' . $formUser->course_id . 
                              ', division_id: ' . $formUser->division_id);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar el contador de respuestas: ' . $e->getMessage());
        }
    }
}
