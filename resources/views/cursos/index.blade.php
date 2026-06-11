<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Cursos Disponíveis</title>

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
            .search-panel {
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.08);
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
                            <div class="text-xs text-white/60">Cursos Disponíveis</div>
                        </div>
                    </a>
                    <div class="inline-flex items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Voltar</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-btn type="submit" style="secondary" class="rounded-full px-4 py-2">Sair</x-btn>
                        </form>
                    </div>
                </header>

                <nav class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('dashboard-admin') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Dashboard</a>
                    <a href="{{ route('admin.gerenciamento-turmas') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Gerenciar Turmas</a>
                </nav>

                <x-email-verification-warning />

                <main class="py-16">
                    <div class="hero-panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                        <h1 class="text-3xl font-extrabold">Cursos Disponíveis</h1>
                        <p class="mt-3 text-white/75">Pesquise e descubra oportunidades de formação para o seu perfil.</p>

                        <form method="GET" action="{{ route('cursos') }}" class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <input
                                type="search"
                                name="search"
                                value="{{ $query ?? '' }}"
                                placeholder="Pesquisar por nome de curso..."
                                class="w-full rounded-3xl border border-white/10 bg-white/5 px-4 py-3 text-white placeholder:text-white/50 focus:outline-none focus:ring-2 focus:ring-[#0043cb]/50"
                            />
                            <x-btn type="submit" style="primary" class="rounded-full px-6 py-3">Buscar</x-btn>
                        </form>
                    </div>

                    <section class="mt-10 grid gap-6 lg:grid-cols-3">
                        @forelse($available as $curso)
                            <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-xl transition hover:-translate-y-1 hover:bg-white/10">
                                <h2 class="text-xl font-semibold text-white">{{ $curso->nome }}</h2>
                                <p class="mt-3 text-sm leading-6 text-white/70">{{ $curso->descricao }}</p>
                                <div class="mt-6 flex items-center justify-between gap-3">
                                    <span class="text-sm text-white/70">Professor: {{ $curso->docente->name ?? '---' }}</span>
                                    <a href="{{ route('turmas.show', $curso->id) }}" class="text-white/80 hover:text-white">Ver detalhes</a>
                                </div>
                            </div>
                        @empty
                            <div class="lg:col-span-3 rounded-[1.75rem] card-panel p-8 text-white/80">
                                <h2 class="text-xl font-semibold">Nenhum curso encontrado.</h2>
                                <p class="mt-3 text-white/70">Tente outra palavra-chave ou volte mais tarde.</p>
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
