<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupplierFeedbackMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $supplier,
        public $type,
        public $feedback,
        public $sender,      // cuarto argumento
        public $document = null
    ) {}

    // app/Mail/SupplierFeedbackMail.php
    public function build()
    {
        $mail = $this->subject('Retroalimentación sobre tus documentos')
            ->view('emails/suppliers/feedback')
            ->with([
                'supplier' => $this->supplier,
                'type'     => $this->type,
                'feedback' => $this->feedback,
                'sender'   => $this->sender,
                'document' => $this->document,
            ]);

        // Reply-To al revisor si hay correo
        if ($this->sender && filled($this->sender->email)) {
            $mail->replyTo($this->sender->email, $this->sender->name ?? null);
        }

        return $mail;
    }
}
