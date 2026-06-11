<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.support_from.address', 'onboarding@resend.dev'),
                config('mail.support_from.name', 'UniRoad')
            ),
            replyTo: [
                new Address($this->payload['email'], $this->payload['name']),
            ],
            subject: 'Nova solicitação de suporte pela UniRoad'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support-request'
        );
    }
}
