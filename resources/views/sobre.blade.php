<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'UniRoad') }} - Sobre nós</title>
        <link rel="icon" type="image/png" sizes="any" href="{{ asset('img/logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('img/logo.png') }}">

        @fonts
        @ddfsnStyles

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            .landing-bg {
                background:
                    radial-gradient(circle at top left, rgba(34, 211, 197, 0.16), transparent 28rem),
                    radial-gradient(circle at top right, rgba(96, 165, 250, 0.14), transparent 30rem),
                    #080a10;
            }
            .nav-pill,
            .hero-panel,
            .card-panel {
                background: rgba(17, 21, 32, 0.88);
                border: 1px solid rgba(148, 163, 184, 0.16);
                border-radius: 8px;
            }
            .feature-chip {
                background: rgba(34, 211, 197, 0.12);
                border: 1px solid rgba(34, 211, 197, 0.28);
            }
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative z-10 max-w-7xl mx-auto px-6 py-8 lg:py-10">
            <header class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                    <div>
                        <span class="font-semibold text-lg">UniRoad</span>
                        <div class="text-xs text-white/60">Sobre nós</div>
                    </div>
                </a>

                <nav class="inline-flex flex-wrap justify-start gap-3 sm:justify-end w-full sm:w-auto">
                    <x-btn href="{{ route('login') }}" class="nav-pill rounded-full px-4 py-2 text-white !text-white">Entrar</x-btn>
                    <x-btn href="{{ route('register') }}" style="primary" class="rounded-full px-4 py-2">Cadastrar</x-btn>
                </nav>
            </header>

            <main class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] items-start py-16 lg:py-20">
                <section class="hero-panel p-8 lg:p-10 shadow-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 feature-chip text-sm text-white/80 font-medium">
                        <x-pulser style="info" class="inline-flex h-2 w-2.5" />
                        Gestão visual de aprendizado
                    </div>
                    <h1 class="mt-8 text-4xl font-extrabold tracking-tight sm:text-5xl">Um caminho mais claro para ensinar e aprender.</h1>
                    <p class="mt-5 text-lg leading-8 text-white/75">A UniRoad nasceu para organizar a rotina de turmas, alunos e professores em um único espaço visual. A plataforma transforma conteúdos em roadmaps estruturados, facilitando acompanhamento, planejamento e orientação.</p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <x-btn href="{{ route('contato') }}" style="primary" size="lg" class="w-full sm:w-auto">Entrar em Contato</x-btn>
                        <x-btn href="{{ url('/') }}" class="nav-pill w-full sm:w-auto text-white !text-white">Ver demo</x-btn>
                    </div>
                </section>

                <section class="grid gap-4">
                    <article class="card-panel p-6">
                        <h2 class="text-xl font-bold">Para escolas e equipes</h2>
                        <p class="mt-3 text-white/70">Centralize turmas, professores e alunos com regras de acesso simples e seguras.</p>
                    </article>
                    <article class="card-panel p-6">
                        <h2 class="text-xl font-bold">Para professores</h2>
                        <p class="mt-3 text-white/70">Crie roadmaps com etapas, anexos, imagens e notas para guiar o aluno durante o estudo.</p>
                    </article>
                    <article class="card-panel p-6">
                        <h2 class="text-xl font-bold">Para alunos</h2>
                        <p class="mt-3 text-white/70">Acesse apenas as turmas em que está matriculado e visualize os caminhos de aprendizado liberados.</p>
                    </article>
                </section>
            </main>

            <footer class="mt-10 border-t border-white/10 pt-6 text-sm text-white/60 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <span>© {{ date('Y') }} {{ config('app.name', 'UniRoad') }}.</span>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="hover:text-white">Início</a>
                    <a href="{{ route('contato') }}" class="hover:text-white">Contato</a>
                </div>
            </footer>
        </div>

        @ddfsnScripts
    </body>
</html>
