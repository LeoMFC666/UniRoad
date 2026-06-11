<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'UniRoad') }} - Painel</title>
        <link rel="icon" type="image/png" sizes="any" href="{{ asset('img/logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('img/logo.png') }}">


        @fonts
        @ddfsnStyles

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            
            .landing-bg { background: radial-gradient(circle at top, rgba(0,67,203,0.25), transparent 20%), linear-gradient(180deg,#07080f 0%,#081a33 45%,#001d84 100%);}
            
            .hero-panel {
                background: rgba(16, 24, 50, 0.92);
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 32px 120px rgba(0, 67, 203, 0.18);
            }
            .feature-chip {
                background: rgba(0, 67, 203, 0.14);
                border: 1px solid rgba(0, 67, 203, 0.26);
            }
            .card-panel {
                background: rgba(255, 255, 255, 0.06);
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 24px 80px rgba(0, 67, 203, 0.12);
            }
            .nav-pill {
                background: rgba(255,255,255,0.08);
                border: 1px solid rgba(255,255,255,0.1);
            }
            @media (max-width: 420px) {
                .desktop-visible { display: none; }
                .mobile-visible { display: block; }
            }
            @media (min-width: 421px) {
                .desktop-visible { display: block; }
                .mobile-visible { display: none; }
            }
        </style>
    </head>

    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative overflow-hidden">
            
            <div class="relative z-10 max-w-7xl mx-auto px-6 py-8 lg:py-10">
                <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('dashboard-aluno') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                        <div>
                            <span class="font-semibold text-lg">UniRoad</span>
                            <div class="text-xs text-white/60">Painel do aluno</div>
                        </div>
                    </a>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('turmas.index') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Minhas Turmas</a>
                        <x-user-menu />
                    </div>
                </header>

                <nav class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('dashboard') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90 font-medium">Dashboard</a>
                    <a href="{{ route('turmas.index') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90 font-medium">Minhas Turmas</a>
                </nav>

                <x-email-verification-warning />
                <x-flash-messages />

                <main class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr] items-start py-16 lg:py-24">
                    <section class="space-y-8 max-w-2xl mx-auto lg:mx-0 text-center lg:text-left">
                        <div class="desktop-visible inline-flex items-center justify-center lg:justify-start gap-2 rounded-full px-4 py-2 feature-chip text-sm text-white/80 font-medium">
                            <x-pulser style="info" class="inline-flex h-2 w-2.5" />
                            Painel de controle do estudante
                        </div>
                        <div class="space-y-6">
                            <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">Bem-vindo de volta, {{ auth()->user()->name }}</h1>
                            <p class="max-w-xl text-lg leading-8 text-white/75">Acompanhe suas turmas, acesse conteúdos e encontre novas oportunidades de estudo.</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-3xl card-panel p-6">
                                <p class="text-sm uppercase tracking-[0.28em] text-white/50 mb-3">Turmas ativas</p>
                                <p class="text-3xl font-semibold">{{ $turmas->count() }}</p>
                            </div>
                            <div class="rounded-3xl card-panel p-6">
                                <p class="text-sm uppercase tracking-[0.28em] text-white/50 mb-3">Próxima ação</p>
                                <p class="text-3xl font-semibold">Minhas turmas</p>
                            </div>
                        </div>
                    </section>

                    <section class="hero-panel rounded-[2rem] border-white/10 p-8 lg:p-10 shadow-2xl">
                        <div class="mb-8 flex items-center gap-3">
                            <div class="rounded-full bg-white/10 p-3">
                                <span class="block h-3 w-3 rounded-full bg-[#0043cb]"></span>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.35em] text-white/50">Resumo</p>
                                <p class="text-lg font-semibold">Seu espaço de estudos</p>
                            </div>
                        </div>
                        <div class="space-y-4 text-sm text-white/75">
                            <p>Use o botão abaixo para ver suas turmas e acompanhar os roadmaps liberados.</p>
                            <p>Seu progresso está disponível em tempo real e organizado para facilitar o seu aprendizado.</p>
                        </div>
                    </section>
                </main>

                <section class="grid gap-6 lg:grid-cols-3">
                    @forelse($turmas as $turma)
                        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-xl transition hover:-translate-y-1 hover:bg-white/10">
                            <h2 class="text-xl font-semibold text-white">{{ $turma->nome }}</h2>
                            <p class="mt-3 text-sm leading-6 text-white/70">{{ $turma->descricao }}</p>
                            <div class="mt-6 flex items-center justify-between gap-3">
                                <span class="rounded-full bg-[#0043cb]/15 px-3 py-1 text-xs uppercase tracking-[0.24em] text-[#cfe3ff]">Turma ativa</span>
                                <a href="{{ route('turmas.show', $turma->id) }}" class="text-white/80 hover:text-white">Abrir</a>
                            </div>
                        </div>
                    @empty
                        <div class="lg:col-span-3 rounded-[1.75rem] card-panel p-8 text-white/80">
                            <h2 class="text-xl font-semibold">Você ainda não está em nenhuma turma.</h2>
                            <p class="mt-3 text-white/70">Aguarde um professor ou administrador vincular você a uma turma.</p>
                        </div>
                    @endforelse
                </section>

                <footer class="mt-10 border-t border-white/10 pt-6 text-sm text-white/60 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <span>© {{ date('Y') }} {{ config('app.name', 'UniRoad') }}.</span>
                    <a href="{{ route('support.create') }}" class="hover:text-white">Suporte</a>
                </footer>
            </div>
        </div>

        @ddfsnScripts
    </body>
</html>
