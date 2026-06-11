<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeNewUserMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

           
            
            'role' => 'aluno',
        ]);

        if (config('mail.registration_emails_enabled') && config('mail.email_verification_enabled')) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $exception) {
                Log::error('Falha ao enviar e-mail de verificação da UniRoad.', [
                    'user_id' => $user->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        } else {
            Log::info('E-mail de verificação pulado durante cadastro.', [
                'user_id' => $user->id,
            ]);
        }

        if (config('mail.registration_emails_enabled') && config('mail.external_sending_enabled')) {
            try {
                Mail::mailer(config('mail.registration_mailer'))
                    ->to($user->email)
                    ->send(new WelcomeNewUserMail($user));
            } catch (\Throwable $exception) {
                Log::error('Falha ao enviar e-mail de boas-vindas da UniRoad.', [
                    'user_id' => $user->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        } else {
            Log::info('E-mail de boas-vindas pulado durante cadastro.', [
                'user_id' => $user->id,
            ]);
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
