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
                    <a href="{{ route('dashboard-aluno') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                        <div>
                            <span class="font-semibold text-lg">UniRoad</span>
                            <div class="text-xs text-white/60">Minhas Turmas</div>
                        </div>
                    </a>
                    <div class="inline-flex items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Voltar ao dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-btn type="submit" style="secondary" class="rounded-full px-4 py-2">Sair</x-btn>
                        </form>
                    </div>
                </header>

                <nav class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('dashboard') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90 font-medium">Dashboard</a>
                    <a href="{{ route('roadmaps') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90 font-medium">Minhas Turmas</a>
                </nav>

                <x-email-verification-warning />

                <main class="py-16">
                    <div class="hero-panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                        <h1 class="text-3xl font-extrabold">Roadmaps</h1>
                        <p class="mt-3 text-white/75">Aqui estão as turmas e roadmaps em que você está inscrito no momento.</p>
                    </div>

                    <section class="mt-10 grid gap-6 lg:grid-cols-3">
                        @forelse($turmas as $turma)
                            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-xl transition hover:-translate-y-1 hover:bg-white/10">
                                <h2 class="text-xl font-semibold text-white">{{ $turma->nome }}</h2>
                                <p class="mt-3 text-sm leading-6 text-white/70">{{ $turma->descricao }}</p>
                                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 text-sm text-white/70">
                                    <span>Professor: {{ $turma->docente->name ?? 'Não disponível' }}</span>
                                    <a href="{{ route('roadmaps.index', $turma->id) }}" class="text-white/80 hover:text-white">Ver Roadmaps da Turma</a>
                                </div>
                            </div>
                        @empty
                            <div class="lg:col-span-3 rounded-[1.75rem] card-panel p-8 text-white/80">
                                <h2 class="text-xl font-semibold">Você ainda não está inscrito em nenhuma turma.</h2>
                                <p class="mt-3 text-white/70">Aguarde um professor ou administrador vincular você a uma turma.</p>
                            </div>
                        @endforelse
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
