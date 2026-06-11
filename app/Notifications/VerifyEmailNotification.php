<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->mailer(config('mail.email_verification_mailer'))
            ->subject('Verifique sua conta na UniRoad')
            ->view('emails.verify-email', [
                'user' => $notifiable,
                'verificationUrl' => $verificationUrl,
            ]);
    }
}
