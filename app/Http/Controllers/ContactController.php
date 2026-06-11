<?php

namespace App\Http\Controllers;

use App\Mail\ContactInternalNotificationMail;
use App\Mail\ContactThankYouMail;
use App\Models\ContactLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function create()
    {
        return view('contato');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
        ]);

        $lead = ContactLead::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'integration_status' => 'pending',
        ]);

        $contactConfirmationEnabled = config('mail.contact_confirmation_enabled');
        $contactMailer = config('mail.contact_mailer');
        $userMailDelivered = null;
        $internalMailDelivered = null;

        if ($contactConfirmationEnabled) {
            try {
                Mail::mailer($contactMailer)->to($lead->email)->send(new ContactThankYouMail($lead));
                $userMailDelivered = true;
            } catch (\Throwable $exception) {
                $userMailDelivered = false;

                Log::error('Falha ao enviar e-mail de agradecimento da UniRoad.', [
                    'lead_id' => $lead->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        } else {
            Log::info('E-mail de agradecimento pulado porque CONTACT_CONFIRMATION_EMAIL_ENABLED está desativado.', [
                'lead_id' => $lead->id,
            ]);
        }

        $notificationEmail = config('mail.contact_notification');
        if ($notificationEmail) {
            try {
                Mail::mailer($contactMailer)->to($notificationEmail)->send(new ContactInternalNotificationMail($lead));
                $internalMailDelivered = true;
            } catch (\Throwable $exception) {
                $internalMailDelivered = false;

                Log::error('Falha ao enviar notificação interna de contato da UniRoad.', [
                    'lead_id' => $lead->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        } else {
            Log::info('Notificação interna de contato pulada porque CONTACT_NOTIFICATION_EMAIL não está configurado.', [
                'lead_id' => $lead->id,
            ]);
        }

        $integrationStatus = match (true) {
            $userMailDelivered === false || $internalMailDelivered === false => 'email_failed',
            $userMailDelivered === null || $internalMailDelivered === null => 'email_skipped',
            default => 'email_sent',
        };

        $lead->update([
            'integration_status' => $integrationStatus,
        ]);

        return back()->with('success', 'Recebemos seu contato. Nossa equipe vai acompanhar a solicitação.');
    }
}
