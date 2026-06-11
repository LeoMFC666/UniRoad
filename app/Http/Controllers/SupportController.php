<?php

namespace App\Http\Controllers;

use App\Mail\SupportRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function create()
    {
        return view('suporte');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'description.required' => 'Descreva brevemente como podemos ajudar.',
            'description.min' => 'A descrição precisa ter pelo menos 10 caracteres.',
            'description.max' => 'A descrição pode ter no máximo 3000 caracteres.',
        ]);

        $user = Auth::user();
        $recipient = config('mail.support_notification');

        if (! $recipient) {
            Log::warning('Solicitação de suporte não enviada porque o e-mail de destino não está configurado.', [
                'user_id' => $user->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Não foi possível enviar sua mensagem agora. Nossa equipe já foi avisada para revisar a configuração do suporte.');
        }

        $payload = [
            'name' => $user->name,
            'email' => $user->email,
            'description' => $validated['description'],
            'user_agent' => $request->userAgent() ?: 'Navegador não identificado',
            'sent_at' => now(),
        ];

        try {
            Mail::mailer(config('mail.support_mailer', 'resend'))
                ->to($recipient)
                ->send(new SupportRequestMail($payload));
        } catch (\Throwable $exception) {
            Log::error('Falha ao enviar solicitação de suporte.', [
                'user_id' => $user->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Recebemos sua tentativa, mas não conseguimos enviar a mensagem agora. Tente novamente em alguns instantes.');
        }

        return back()->with('success', 'Mensagem enviada ao suporte com sucesso. Nossa equipe vai acompanhar sua solicitação.');
    }
}
