<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $deleteUrl;

    public function __construct($deleteUrl)
    {
        $this->deleteUrl = $deleteUrl;
    }

    public function build()
    {
        return $this->subject('Account Deletion Confirmation')
            ->view('mail.delete_user')
            ->with([
                'deleteUrl' => $this->deleteUrl,
            ]);
    }
}