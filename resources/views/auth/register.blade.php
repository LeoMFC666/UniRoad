<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Cadastro</title>
        <link rel="icon" type="image/png" sizes="any" href="{{ asset('img/logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('img/logo.png') }}">


        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @ddfsnStyles

        <style>
            .login_btn {
                color: white;
                background: rgba(0, 67, 203, 0.14);
                border: 1px solid rgba(0, 67, 203, 0.26);
            }
            .login_btn:hover {
                background: rgba(0, 67, 203, 0.24);
                border-color: rgba(0, 67, 203, 0.36);
            }
            .landing-bg { background: radial-gradient(circle at top, rgba(0,67,203,0.25), transparent 20%), linear-gradient(180deg,#07080f 0%,#081a33 45%,#001d84 100%);} 
            .hero-panel { background: rgba(16,24,50,0.92); border:1px solid rgba(255,255,255,0.08); box-shadow:0 32px 120px rgba(0,67,203,0.18);} 
            .mobile-visible {
                display: none;
            }
            @media (max-width: 1029px) {
                .desktop-visible {
                    display: none;
                }
            }

            @media (max-width: 1850px) {
                .desktop-visible {
                    display: none;
                }

                .mobile-visible {
                display: block;
                }
            }
            

                @media (max-width: 420px) {
                    .smartphone-visible{
                    display: block;
                    }
                    .desktop-btn{
                    display: none;
                    }
                }
                @media (min-width: 421px) {
                    .desktop-btn{
                    display: block;
                    }
                    .smartphone-visible{
                    display: none;
                    }
                }
            
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative z-10 max-w-4xl mx-auto px-6 py-12">
            <header class="flex items-center justify-between mb-8 mx-6">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                    <div>
                        <span class="font-semibold text-lg">{{ config('app.name', 'UniRoad') }}</span>
                        <div class="text-xs text-white/60">Aprenda, acompanhe e evolua</div>
                    </div>
                </a>
                <div>
                    <x-btn href="{{ route('login') }}" class="login_btn rounded-full px-4 py-2">Entrar</x-btn>
                </div>
            </header>

            <main class="grid gap-8 lg:grid-cols-2 items-start">

                <div class="mobile-visible space-y-6 mx-12">
                    <h1 class="text-3xl font-extrabold">Crie sua conta</h1>
                    <p class="text-white/75">Registre-se para criar roadmaps, turmas e enviar atividades.</p>
                </div>

                <div class="hero-panel rounded-2xl p-6 mx-6">
                    <x-input-error :messages="$errors->get('google')" class="mb-4" />

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nome completo:')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="email" :value="__('E-mail:')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password" :value="__('Senha:')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Senha:')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between">
                            <a class="underline text-sm text-white/80 hover:text-white" href="{{ route('login') }}">{{ __('Já tem uma conta?') }}</a>
                            <x-primary-button class="desktop-btn">{{ __('Cadastrar') }}</x-primary-button>
                        </div>

                        <div class="flex items-center justify-center mt-4">
                            <x-primary-button class="smartphone-visible">{{ __('Cadastrar') }}</x-primary-button>
                        </div>

                    </form>

                    <div class="my-5 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-white/40">
                        <span class="h-px flex-1 bg-white/10"></span>
                        <span>ou</span>
                        <span class="h-px flex-1 bg-white/10"></span>
                    </div>

                    <x-google-auth-button />
                </div>
                <div class="desktop-visible space-y-6">
                    <h1 class="text-3xl font-extrabold">Crie sua conta</h1>
                    <p class="text-white/75">Registre-se para criar roadmaps, turmas e enviar atividades.</p>
                </div>
            </main>

            <footer class="mt-10 text-sm text-white/60 mx-9">© {{ date('Y') }} {{ config('app.name', 'UniRoad') }}.</footer>
        </div>

        
    </body>
</html>
