<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        abort_unless(hash_equals($hash, sha1($user->getEmailForVerification())), 403);

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route($this->dashboardRouteFor($user))
            ->with('success', 'Sua conta foi verificada com sucesso.');
    }

    private function dashboardRouteFor(User $user): string
    {
        return match ($user->role) {
            'admin' => 'dashboard-admin',
            'docente' => 'dashboard-docente',
            default => 'dashboard-aluno',
        };
    }
}
