<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Painel do administrador</title>

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
            .card-panel {
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.08);
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
                    <a href="{{ route('dashboard-admin') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                        <div>
                            <span class="font-semibold text-lg">UniRoad</span>
                            <div class="text-xs text-white/60">Painel Administrativo</div>
                        </div>
                    </a>

                    <x-user-menu />
                </header>

                <nav class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('dashboard-admin') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90 font-medium">Dashboard</a>
                    <a href="{{ route('admin.gerenciamento-turmas') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90 font-medium">Gerenciar Turmas</a>
                    <a href="{{ route('admin.gerenciamento-docentes') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90 font-medium">Gerenciar Professores</a>
                    <a href="{{ route('admin.gerenciamento-alunos') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90 font-medium">Gerenciar Alunos</a>
                    <a href="{{ route('turmas.create') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90 font-medium">Adicionar Turma</a>
                </nav>

                <x-email-verification-warning />
                <x-flash-messages />

                <main class="grid gap-10 lg:grid-cols-[1.3fr_0.85fr] items-start py-16 lg:py-24">
                    <section class="space-y-8 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 feature-chip text-sm text-white/80 font-medium">
                            <x-pulser style="info" class="inline-flex h-2 w-2.5" />
                            Administração
                        </div>

                        <div class="space-y-6">
                            <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">Painel do administrador</h1>
                            <p class="max-w-xl text-lg leading-8 text-white/75">Tenha controle sobre turmas, professores e estudantes com acesso dedicado a cada área.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-3xl card-panel p-6">
                                <p class="text-sm uppercase tracking-[0.28em] text-white/50 mb-3">Turmas</p>
                                <p class="text-3xl font-semibold">{{ $turmas->count() }}</p>
                            </div>
                            <div class="rounded-3xl card-panel p-6">
                                <p class="text-sm uppercase tracking-[0.28em] text-white/50 mb-3">Professores</p>
                                <p class="text-3xl font-semibold">{{ $docentes->count() }}</p>
                            </div>
                            <div class="rounded-3xl card-panel p-6">
                                <p class="text-sm uppercase tracking-[0.28em] text-white/50 mb-3">Alunos</p>
                                <p class="text-3xl font-semibold">{{ $alunos->count() }}</p>
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
                                <p class="text-lg font-semibold">Visão rápida da plataforma</p>
                            </div>
                        </div>
                        <div class="space-y-4 text-sm text-white/75">
                            <p>As métricas abaixo ajudam a identificar atividade e oportunidades de gerenciamento imediato.</p>
                            <p>Clique nas abas acima para migrar entre os painéis de turmas, professores e alunos.</p>
                        </div>
                    </section>
                </main>

                <section class="grid gap-6 lg:grid-cols-3">
                    @foreach($turmas->take(3) as $turma)
                        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-xl transition hover:-translate-y-1 hover:bg-white/10">
                            <h2 class="text-xl font-semibold text-white">{{ $turma->nome }}</h2>
                            <p class="mt-3 text-sm leading-6 text-white/70">{{ $turma->descricao }}</p>
                            <p class="mt-4 text-xs uppercase tracking-[0.24em] text-white/50">Professor: {{ $turma->docente->name ?? 'Sem professor' }}</p>
                        </div>
                    @endforeach
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
