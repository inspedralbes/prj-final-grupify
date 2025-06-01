<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Form;
use App\Models\User;

class FormAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $form;
    public $user;

    public function __construct(Form $form, User $user)
    {
        $this->form = $form;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Nuevo Formulario Asignado')
                    ->view('emails.form_assigned')
                    ->with([
                        'formTitle' => $this->form->title,
                        'userName' => $this->user->name,
                    ]);
    }
}
