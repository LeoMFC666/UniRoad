<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'UniRoad') }} - Suporte</title>
        <link rel="icon" type="image/png" sizes="any" href="{{ asset('img/logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('img/logo.png') }}">

        @fonts
        @ddfsnStyles

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            .landing-bg {
                background: radial-gradient(circle at top, rgba(0, 67, 203, 0.25), transparent 20%),
                    linear-gradient(180deg, #07080f 0%, #081a33 45%, #001d84 100%);
            }
            .hero-panel {
                background: rgba(16, 24, 50, 0.92);
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 32px 120px rgba(0, 67, 203, 0.18);
            }
            .nav-pill {
                background: rgba(255,255,255,0.08);
                border: 1px solid rgba(255,255,255,0.1);
            }
            .field {
                width: 100%;
                border-radius: 1rem;
                border: 1px solid rgba(255, 255, 255, 0.1);
                background: rgba(2, 6, 23, 0.62);
                padding: 0.9rem 1rem;
                color: white;
                outline: none;
            }
            .field:focus {
                border-color: rgba(103, 232, 249, 0.7);
                box-shadow: 0 0 0 3px rgba(103, 232, 249, 0.12);
            }
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        @php
            $dashboardRoute = match(auth()->user()->role) {
                'admin' => route('dashboard-admin'),
                'docente' => route('dashboard-docente'),
                default => route('dashboard-aluno'),
            };
        @endphp

        <div class="relative min-h-screen overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-72 bg-[radial-gradient(circle_at_top,_rgba(0,67,203,0.4),_transparent_30%)] opacity-70"></div>
            <div class="absolute inset-y-0 right-0 w-96 bg-[radial-gradient(circle_at_bottom_right,_rgba(255,255,255,0.08),_transparent_38%)]"></div>

        <div class="relative z-10 mx-auto max-w-5xl px-6 py-8 lg:py-10">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ $dashboardRoute }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="h-12 w-12 rounded-full border-white/10 shadow-2xl" />
                    <div>
                        <span class="font-semibold text-lg">UniRoad</span>
                        <div class="text-xs text-white/60">Suporte</div>
                    </div>
                </a>
                <a href="{{ $dashboardRoute }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Voltar ao dashboard</a>
            </header>

            <x-email-verification-warning />

            @if(session('success'))
                <div class="mt-8 rounded-2xl border border-emerald-400/30 bg-emerald-400/10 px-5 py-4 text-sm font-semibold text-emerald-100">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mt-8 rounded-2xl border border-amber-300/40 bg-amber-300/10 px-5 py-4 text-sm font-semibold text-amber-100">
                    {{ session('error') }}
                </div>
            @endif

            <main class="grid gap-8 py-12 lg:grid-cols-[0.82fr_1.18fr]">
                <section class="hero-panel rounded-[2rem] p-8">
                    <p class="text-xs uppercase tracking-[0.35em] text-cyan-200">Atendimento</p>
                    <h1 class="mt-4 text-4xl font-extrabold tracking-tight">Fale com o suporte</h1>
                    <p class="mt-5 text-white/75 leading-7">
                        Envie uma descrição objetiva do problema. A mensagem incluirá automaticamente seu e-mail,
                        nome da conta, navegador e horário do envio para facilitar o atendimento.
                    </p>
                </section>

                <section class="hero-panel rounded-[2rem] p-8">
                    <form method="POST" action="{{ route('support.store') }}" class="grid gap-5">
                        @csrf
                        <label class="grid gap-2 text-sm text-white/75">
                            Nome
                            <input class="field" value="{{ auth()->user()->name }}" readonly>
                        </label>
                        <label class="grid gap-2 text-sm text-white/75">
                            E-mail da conta
                            <input class="field" value="{{ auth()->user()->email }}" readonly>
                        </label>
                        <label class="grid gap-2 text-sm text-white/75">
                            Descrição
                            <textarea
                                name="description"
                                rows="7"
                                class="field resize-y"
                                placeholder="Conte o que aconteceu e onde você estava no sistema."
                                required>{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-sm text-red-300">{{ $message }}</span>
                            @enderror
                        </label>
                        <div class="flex justify-end">
                            <x-btn type="submit" style="primary" class="rounded-full px-6 py-3">Enviar mensagem ao suporte</x-btn>
                        </div>
                    </form>
                </section>
            </main>
        </div>
        </div>

        @ddfsnScripts
    </body>
</html>
