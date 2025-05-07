<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class CodeConfirmationMail extends Mailable {
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function build(): CodeConfirmationMail
    {
        return $this->subject('Уведомление от приложения')
            ->view('emails.code_confirmation'); // Привязка шаблона письма
    }
}



