<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Minhas Turmas</title>

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
                @php
                    $isAdmin = auth()->user()->role === 'admin';
                    $dashboardRoute = $isAdmin ? route('dashboard-admin') : route('dashboard-docente');
                    $turmasRoute = $isAdmin ? route('admin.gerenciamento-turmas') : route('docente.minhas-turmas');
                    $turmasLabel = $isAdmin ? 'Gerenciar Turmas' : 'Minhas Turmas';
                @endphp

                <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ $dashboardRoute }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                        <div>
                            <span class="font-semibold text-lg">UniRoad</span>
                            <div class="text-xs text-white/60">Minhas Turmas</div>
                        </div>
                    </a>
                    <div class="inline-flex items-center gap-3">
                        <a href="{{ route('turmas.create') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">+ Criar Turma</a>
                        <a href="{{ $dashboardRoute }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Voltar</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-btn type="submit" style="secondary" class="rounded-full px-4 py-2">Sair</x-btn>
                        </form>
                    </div>
                </header>

                <nav class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $dashboardRoute }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Dashboard</a>
                    <a href="{{ $turmasRoute }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">{{ $turmasLabel }}</a>
                </nav>

                <x-email-verification-warning />
                <x-flash-messages />

                <main class="py-16">
                    <div class="hero-panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                        <h1 class="text-3xl font-extrabold">{{ $isAdmin ? 'Gerenciamento de turmas' : 'Turmas do professor' }}</h1>
                        <p class="mt-3 text-white/75">{{ $isAdmin ? 'Acompanhe as turmas cadastradas e acesse os detalhes com segurança.' : 'Lista exclusiva para professores, com acesso a todas as turmas sob sua responsabilidade.' }}</p>
                    </div>

                    <section class="mt-10 grid gap-6 lg:grid-cols-3">
                        @foreach($turmas as $turma)
                            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-xl transition hover:-translate-y-1 hover:bg-white/10">
                                <h2 class="text-xl font-semibold text-white">{{ $turma->nome }}</h2>
                                <p class="mt-3 text-sm leading-6 text-white/70">{{ $turma->descricao }}</p>
                                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 text-sm text-white/70">
                                    <span>Professor: {{ $turma->docente->name ?? auth()->user()->name }}</span>
                                    <a href="{{ route('turmas.show', $turma->id) }}" class="text-white/80 hover:text-white">Ver detalhes</a>
                                </div>
                                @if($isAdmin || (auth()->user()->role === 'docente' && $turma->docente_id === auth()->id()))
                                    <form method="POST" action="{{ route('turmas.destroy', $turma->id) }}" class="mt-5">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-full border border-red-400/40 bg-red-400/10 px-4 py-2 text-sm font-semibold text-red-100 transition hover:bg-red-400/20"
                                            onclick="return confirm('Excluir esta turma? Os roadmaps e vínculos de alunos também serão removidos.')"
                                        >
                                            Excluir turma
                                        </button>
                                    </form>
                                @endif
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
