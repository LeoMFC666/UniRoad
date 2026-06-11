<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - {{ $turma->nome }}</title>

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
            .panel {
                background: rgba(16, 24, 50, 0.92);
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 32px 120px rgba(0, 67, 203, 0.18);
            }
            .nav-pill {
                background: rgba(255,255,255,0.08);
                border: 1px solid rgba(255,255,255,0.1);
            }
            .card-panel {
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.08);
                box-shadow: 0 24px 80px rgba(0, 67, 203, 0.12);
            }
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative overflow-hidden">
            
            <div class="relative z-10 max-w-4xl mx-auto px-6 py-8 lg:py-10">
                @php
                    $dashboardRoute = match(auth()->user()->role) {
                        'admin' => route('dashboard-admin'),
                        'docente' => route('dashboard-docente'),
                        default => route('dashboard-aluno'),
                    };
                @endphp

                <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ $dashboardRoute }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                        <div>
                            <span class="font-semibold text-lg">{{ $turma->nome }}</span>
                            <div class="text-xs text-white/60">Detalhes da turma</div>
                        </div>
                    </a>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('turmas.index') }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Voltar</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-btn type="submit" style="secondary" class="rounded-full px-4 py-2">Sair</x-btn>
                        </form>
                    </div>
                </header>

                <x-email-verification-warning />

                @php
                    $user = auth()->user();
                    $isAluno = $user->role === 'aluno';
                    $isAdmin = $user->role === 'admin';
                    $isOwnerProfessor = $user->role === 'docente' && $turma->docente_id === $user->id;
                @endphp

                <main class="mt-10 panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                    <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                        <div>
                            <h1 class="text-3xl font-extrabold">{{ $turma->nome }}</h1>
                            <p class="mt-4 text-white/75">{{ $turma->descricao }}</p>
                            <p class="mt-4 text-sm text-white/60">Professor: {{ $turma->docente->name }}</p>
                        </div>
                        <div class="rounded-[1.5rem] card-panel p-6">
                            <p class="text-sm uppercase tracking-[0.28em] text-white/50 mb-4">Ações</p>
                            <div class="flex flex-col gap-3">
                                @if($isAluno)
                                    <a href="{{ route('roadmaps.index', $turma->id) }}" class="w-full rounded-full px-5 py-3 text-center inline-block bg-blue-600/20 hover:bg-blue-600/30 border border-blue-500/50 transition">
                                        📚 Ver Roadmaps Disponíveis
                                    </a>
                                @endif

                                @if($isAdmin || $isOwnerProfessor)
                                    <a href="{{ route('roadmaps.index', $turma->id) }}" class="w-full rounded-full px-5 py-3 text-center inline-block bg-blue-600/20 hover:bg-blue-600/30 border border-blue-500/50 transition">
                                        📚 Gerenciar Roadmaps
                                    </a>
                                @endif

                                @if($isOwnerProfessor)
                                    <a href="{{ route('turmas.manage', $turma->id) }}" class="w-full rounded-full px-5 py-3 text-center inline-block bg-purple-600/20 hover:bg-purple-600/30 border border-purple-500/50 transition">
                                        ⚙️ Gerenciar Turma
                                    </a>
                                    <form method="POST" action="{{ route('turmas.destroy', $turma->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="w-full rounded-full px-5 py-3 text-center bg-red-600/15 hover:bg-red-600/25 border border-red-400/40 text-red-100 transition"
                                            onclick="return confirm('Excluir esta turma? Os roadmaps e vínculos de alunos também serão removidos.')"
                                        >
                                            Excluir Turma
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <section class="mt-10">
                        <h2 class="text-2xl font-semibold">Alunos</h2>
                        <div class="mt-4 grid gap-4">
                            @forelse($turma->alunos as $aluno)
                                <div class="rounded-3xl card-panel p-4">
                                    <p class="text-white">{{ $aluno->name }}</p>
                                    <p class="text-sm text-white/60">{{ $aluno->email }}</p>
                                </div>
                            @empty
                                <div class="rounded-3xl card-panel p-6 text-white/80">Nenhum aluno inscrito ainda.</div>
                            @endforelse
                        </div>
                    </section>
                </main>
            </div>
        </div>

        @ddfsnScripts
    </body>
</html>
