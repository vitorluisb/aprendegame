<?php

namespace App\Mail;

use App\Domain\Accounts\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public string $token, public School $school)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Convite para escola');
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.invite',
            with: [
                'token' => $this->token,
                'school' => $this->school,
            ],
        );
    }
}
