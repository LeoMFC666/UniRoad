<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if (! config('mail.email_verification_enabled')) {
            Log::info('Reenvio de verificação pulado porque o envio externo está desativado.', [
                'user_id' => $request->user()->id,
            ]);

            return back()->withErrors([
                'email_verification' => 'O envio de verificação por e-mail está temporariamente indisponível. Você ainda pode usar sua conta normalmente.',
            ]);
        }

        $key = $this->rateLimitKey($request);

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->with('email_verification_available_in', $seconds)
                ->withErrors([
                    'email_verification' => "Aguarde {$seconds} segundos antes de pedir outro e-mail de verificação.",
                ]);
        }

        RateLimiter::hit($key, 30);

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $exception) {
            Log::error('Falha ao reenviar e-mail de verificação da UniRoad.', [
                'user_id' => $request->user()->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'email_verification' => 'Não conseguimos enviar o e-mail agora. Tente novamente em alguns instantes.',
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }

    private function rateLimitKey(Request $request): string
    {
        return 'verification-email:'.$request->user()->id;
    }
}
