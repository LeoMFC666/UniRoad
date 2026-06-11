<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    private const CODE_EXPIRES_IN_MINUTES = 10;

    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = Str::lower((string) $request->input('email'));
        $user = User::where('email', $email)->first();
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $user || ! $record) {
            throw ValidationException::withMessages([
                'code' => 'Código inválido ou expirado. Solicite um novo código e tente novamente.',
            ]);
        }

        $createdAt = $record->created_at ? Carbon::parse($record->created_at) : null;
        if (! $createdAt || $createdAt->lt(now()->subMinutes(self::CODE_EXPIRES_IN_MINUTES))) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            throw ValidationException::withMessages([
                'code' => 'Código expirado. Solicite um novo código e tente novamente.',
            ]);
        }

        if (! Hash::check((string) $request->input('code'), $record->token)) {
            throw ValidationException::withMessages([
                'code' => 'Código inválido. Confira os números enviados ao seu e-mail.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        event(new PasswordReset($user));

        return redirect()
            ->route('login')
            ->with('status', 'Senha redefinida com sucesso. Entre usando sua nova senha.');
    }
}
