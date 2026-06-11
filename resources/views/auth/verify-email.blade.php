<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'UniRoad') }} - Verificar e-mail</title>
        <link rel="icon" type="image/png" sizes="any" href="{{ asset('img/logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('img/logo.png') }}">


        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @ddfsnStyles

        <style>
            .landing-bg { background: radial-gradient(circle at top, rgba(0,67,203,0.25), transparent 20%), linear-gradient(180deg,#07080f 0%,#081a33 45%,#001d84 100%);} 
            .hero-panel { background: rgba(16,24,50,0.92); border:1px solid rgba(255,255,255,0.08); box-shadow:0 32px 120px rgba(0,67,203,0.18);} 
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative z-10 max-w-4xl mx-auto px-6 py-12">
            <header class="flex items-center justify-between mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                    <div>
                        <span class="font-semibold text-lg">{{ config('app.name', 'UniRoad') }}</span>
                        <div class="text-xs text-white/60">Aprenda, acompanhe e evolua</div>
                    </div>
                </a>
            </header>

            <main class="grid gap-8 lg:grid-cols-2 items-start">
                <div class="space-y-6">
                    <h1 class="text-3xl font-extrabold">Verifique seu e-mail</h1>
                    <p class="text-white/75">Enviamos uma mensagem para confirmar que este e-mail pertence a você.</p>
                </div>

                <div class="hero-panel rounded-2xl p-6">
                    @php
                        $verificationCooldown = auth()->check()
                            ? \Illuminate\Support\Facades\RateLimiter::availableIn('verification-email:'.auth()->id())
                            : (int) session('email_verification_available_in', 0);
                    @endphp

                    <div class="mb-4 text-sm text-white/75">
                        Antes de continuar, clique no botão enviado ao seu e-mail. Se a mensagem não chegou, você pode pedir outro envio.
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="mb-4 rounded-lg border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm font-medium text-emerald-200">
                            Enviamos um novo e-mail de verificação.
                        </div>
                    @endif

                    @if($errors->has('email_verification'))
                        <div class="mb-4 rounded-lg border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm font-medium text-rose-100">
                            {{ $errors->first('email_verification') }}
                        </div>
                    @endif

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <form method="POST" action="{{ route('verification.send') }}"
                            x-data="{ seconds: {{ $verificationCooldown }} }"
                            x-init="if (seconds > 0) { const interval = setInterval(() => { seconds > 0 ? seconds-- : clearInterval(interval) }, 1000) }">
                            @csrf
                            <button type="submit"
                                x-bind:disabled="seconds > 0"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-800 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50">
                                <span x-show="seconds <= 0">Reenviar e-mail</span>
                                <span x-show="seconds > 0">Aguarde <span x-text="seconds"></span>s</span>
                            </button>
                            <p x-show="seconds > 0" class="mt-2 text-xs text-cyan-100">
                                Você poderá pedir outro envio em <span x-text="seconds"></span> segundos.
                            </p>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="underline text-sm text-white/80 hover:text-white">{{ __('Sair') }}</button>
                        </form>
                    </div>
                </div>
            </main>

            <footer class="mt-10 text-sm text-white/60">© {{ date('Y') }} {{ config('app.name', 'UniRoad') }}.</footer>
        </div>

        @ddfsnScripts
    </body>
</html>
