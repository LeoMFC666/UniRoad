@auth
    @if(auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
        @php
            $verificationCooldown = \Illuminate\Support\Facades\RateLimiter::availableIn('verification-email:'.auth()->id());
        @endphp

        <div class="mx-4 mt-3 rounded-xl border border-cyan-300/25 bg-cyan-400/10 px-4 py-3 text-sm text-cyan-50 shadow-lg shadow-cyan-950/20 sm:mx-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p>
                    Seu e-mail ainda não foi verificado. Confirme sua conta para manter seu acesso protegido.
                </p>

                <form method="POST" action="{{ route('verification.send') }}" class="shrink-0"
                    x-data="{ seconds: {{ $verificationCooldown }} }"
                    x-init="if (seconds > 0) { const interval = setInterval(() => { seconds > 0 ? seconds-- : clearInterval(interval) }, 1000) }">
                    @csrf
                    <button type="submit"
                        x-bind:disabled="seconds > 0"
                        class="rounded-full border border-cyan-300/40 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-cyan-100 transition hover:bg-cyan-300 hover:text-slate-950 disabled:cursor-not-allowed disabled:opacity-60">
                        <span x-show="seconds <= 0">Reenviar e-mail</span>
                        <span x-show="seconds > 0">Aguarde <span x-text="seconds"></span>s</span>
                    </button>
                </form>
            </div>

            @if($errors->has('email_verification'))
                <p class="mt-2 text-xs text-rose-200">{{ $errors->first('email_verification') }}</p>
            @elseif(session('status') === 'verification-link-sent')
                <p class="mt-2 text-xs text-emerald-200">Enviamos um novo e-mail de verificação.</p>
            @endif
        </div>
    @endif
@endauth
