<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Redefinir senha</title>
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
                <div>
                    <a href="{{ route('login') }}" class="underline text-sm text-white/80 hover:text-white">Voltar ao login</a>
                </div>
            </header>

            <main class="grid gap-8 lg:grid-cols-2 items-start">
                <div class="space-y-6">
                    <h1 class="text-3xl font-extrabold">Redefinir senha</h1>
                    <p class="text-white/75">Digite o código recebido por e-mail e escolha uma nova senha.</p>
                </div>

                <div class="hero-panel rounded-2xl p-6">
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="email" :value="__('E-mail')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="code" :value="__('Código de verificação')" />
                            <x-text-input id="code" class="block mt-1 w-full tracking-[0.35em] text-center" type="text" name="code" :value="old('code')" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" />
                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password" :value="__('Nova senha')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar senha')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('password.request') }}" class="text-sm text-white/70 underline hover:text-white">Enviar um novo código</a>
                            <x-primary-button>{{ __('Redefinir senha') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </main>

            <footer class="mt-10 text-sm text-white/60">© {{ date('Y') }} {{ config('app.name', 'UniRoad') }}.</footer>
        </div>

        @ddfsnScripts
    </body>
</html>
