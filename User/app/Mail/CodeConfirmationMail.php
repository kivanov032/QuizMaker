<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodeConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build(): CodeConfirmationMail
    {
        return $this->subject('Уведомление от приложения')
            ->view('emails.code_confirmation');
    }
}


