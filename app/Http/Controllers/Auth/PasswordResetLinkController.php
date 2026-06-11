<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    private const CODE_EXPIRES_IN_MINUTES = 10;

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset code request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = Str::lower((string) $request->input('email'));
        $key = $this->rateLimitKey($email);

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withInput(['email' => $email])
                ->with('password_reset_available_in', $seconds)
                ->withErrors([
                    'email' => "Aguarde {$seconds} segundos antes de pedir outro código.",
                ]);
        }

        RateLimiter::hit($key, 30);

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()
                ->route('password.reset.code')
                ->withInput(['email' => $email])
                ->with('status', 'Se este e-mail estiver cadastrado, enviaremos um código de verificação.');
        }

        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        try {
            Mail::mailer(config('mail.password_reset_mailer'))
                ->to($user->email)
                ->send(new PasswordResetCodeMail($user, $code, self::CODE_EXPIRES_IN_MINUTES));
        } catch (\Throwable $exception) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            Log::error('Falha ao enviar código de redefinição de senha da UniRoad.', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'Não conseguimos enviar o código agora. Tente novamente em alguns instantes.']);
        }

        return redirect()
            ->route('password.reset.code')
            ->withInput(['email' => $email])
            ->with('status', 'Enviamos um código de verificação para seu e-mail.');
    }

    private function rateLimitKey(string $email): string
    {
        return 'password-reset-email:'.sha1($email);
    }
}
