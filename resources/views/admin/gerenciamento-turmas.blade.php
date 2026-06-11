<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Gerenciamento de Turmas</title>

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
            <div class="absolute inset-x-0 top-0 h-72 bg-[radial-gradient(circle_at_top,_rgba(0,67,203,0.4),_transparent_30%)] opacity-70"></div>
            <div class="absolute inset-y-0 right-0 w-96 bg-[radial-gradient(circle_at_bottom_right,_rgba(255,255,255,0.08),_transparent_38%)]"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-6 py-8 lg:py-10">
                <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('dashboard-admin') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                        <div>
                            <span class="font-semibold text-lg">UniRoad</span>
                            <div class="text-xs text-white/60">Gerenciamento de Turmas</div>
                        </div>
                    </a>
                    <div class="inline-flex items-center gap-3">
                        <a href="{{ route('turmas.create') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">+ Criar Turma</a>
                        <a href="{{ route('dashboard-admin') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Voltar</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-btn type="submit" style="secondary" class="rounded-full px-4 py-2">Sair</x-btn>
                        </form>
                    </div>
                </header>

                <nav class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('dashboard-admin') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Dashboard</a>
                    <a href="{{ route('admin.gerenciamento-turmas') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Gerenciar Turmas</a>
                    <a href="{{ route('admin.gerenciamento-docentes') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Gerenciar Professores</a>
                    <a href="{{ route('admin.gerenciamento-alunos') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Gerenciar Alunos</a>
                </nav>

                <x-email-verification-warning />

                <main class="py-16">
                    <div class="hero-panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                        <h1 class="text-3xl font-extrabold">Gerenciar Turmas</h1>
                        <p class="mt-3 text-white/75">Controle e revise todas as turmas ativas no sistema. Acesse os detalhes direto da lista.</p>
                    </div>

                    @if(session('success'))
                        <div class="mt-6 rounded-2xl border border-emerald-400/30 bg-emerald-400/10 px-5 py-4 text-sm font-semibold text-emerald-100">
                            {{ session('success') }}
                        </div>
                    @endif

                    <section class="mt-10 space-y-6">
                        @foreach($turmas as $turma)
                            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-xl transition hover:-translate-y-1 hover:bg-white/10">
                                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <h2 class="text-xl font-semibold text-white">{{ $turma->nome }}</h2>
                                        <p class="mt-2 text-white/70">{{ $turma->descricao }}</p>
                                        <p class="mt-2 text-sm text-white/60">Professor: {{ $turma->docente->name ?? '---' }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.2em] text-cyan-200/70">
                                            {{ $turma->alunos_count }} alunos · {{ $turma->roadmaps_count }} roadmaps
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <x-btn href="{{ route('turmas.show', $turma->id) }}" style="primary" size="sm" class="rounded-full px-4 py-2">Detalhes</x-btn>
                                        <form method="POST" action="{{ route('admin.turmas.destroy', $turma->id) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-full border border-red-400/40 bg-red-400/10 px-4 py-2 text-sm font-semibold text-red-100 transition hover:bg-red-400/20"
                                                onclick="return confirm('Excluir esta turma? Os roadmaps e vínculos de alunos também serão removidos.')"
                                            >
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </section>
                </main>

                <footer class="mt-10 border-t border-white/10 pt-6 text-sm text-white/60 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <span>© {{ date('Y') }} {{ config('app.name', 'UniRoad') }}.</span>
                    <a href="{{ route('support.create') }}" class="hover:text-white">Suporte</a>
                </footer>
            </div>
        </div>

        @ddfsnScripts
    </body>
</html>
