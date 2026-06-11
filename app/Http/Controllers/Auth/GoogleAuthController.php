<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse|SymfonyRedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('login')
                ->withErrors(['google' => 'Login com Google ainda não está configurado.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $exception) {
            Log::error('Falha ao autenticar com Google.', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors(['google' => 'Não foi possível entrar com Google agora. Tente novamente.']);
        }

        $email = Str::lower((string) $googleUser->getEmail());

        if (! $email) {
            return redirect()
                ->route('login')
                ->withErrors(['google' => 'Sua conta Google não informou um e-mail válido.']);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::random(64)),
                'role' => 'aluno',
            ]);
        } elseif (! $user->name && $googleUser->getName()) {
            $user->forceFill(['name' => $googleUser->getName()])->save();
        }

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
