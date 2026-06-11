<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'UniRoad') }} - Gerenciamento de Alunos</title>
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
            .form-field {
                min-width: 0;
                width: 100%;
            }
            .form-field select {
                min-width: 0;
                width: 100%;
            }
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative min-h-screen overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-72 bg-[radial-gradient(circle_at_top,_rgba(0,67,203,0.4),_transparent_30%)] opacity-70"></div>
            <div class="absolute inset-y-0 right-0 w-96 bg-[radial-gradient(circle_at_bottom_right,_rgba(255,255,255,0.08),_transparent_38%)]"></div>

        <div class="relative z-10 mx-auto max-w-7xl px-6 py-8 lg:py-10">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('dashboard-admin') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="h-12 w-12 rounded-full border-white/10 shadow-2xl" />
                    <div>
                        <span class="font-semibold text-lg">UniRoad</span>
                        <div class="text-xs text-white/60">Gerenciamento de Alunos</div>
                    </div>
                </a>
                <div class="inline-flex items-center gap-3">
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
            <x-flash-messages />

            @if(session('success'))
                <div class="mt-6 rounded-lg border border-emerald-400/40 bg-emerald-400/10 p-4 text-emerald-100">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mt-6 rounded-lg border border-red-400/40 bg-red-400/10 p-4 text-red-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <main class="py-16">
                <div class="hero-panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                    <h1 class="text-3xl font-extrabold">Gerenciar Alunos</h1>
                    <p class="mt-3 text-white/75">Vincule alunos a turmas, remaneje matrículas e remova contas quando necessário.</p>
                </div>

                <section class="mt-10 grid min-w-0 gap-6 lg:grid-cols-2">
                    <div class="hero-panel min-w-0 overflow-hidden rounded-[1.5rem] border-white/10 p-6 shadow-2xl">
                        <h2 class="text-xl font-semibold">Adicionar aluno a uma turma</h2>
                        <form method="POST" action="{{ route('admin.turmas.aluno') }}" class="mt-5 grid min-w-0 gap-4">
                            @csrf
                            <label class="form-field grid gap-2 text-sm text-white/75">
                                Aluno
                                <select name="aluno_id" class="rounded-lg border border-white/10 bg-slate-950/70 px-3 py-3 text-white" required>
                                    <option value="">Selecione</option>
                                    @foreach($alunos as $aluno)
                                        <option value="{{ $aluno->id }}">{{ $aluno->name }} — {{ $aluno->email }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="form-field grid gap-2 text-sm text-white/75">
                                Turma
                                <select name="turma_id" class="rounded-lg border border-white/10 bg-slate-950/70 px-3 py-3 text-white" required>
                                    <option value="">Selecione</option>
                                    @foreach($turmas as $turma)
                                        <option value="{{ $turma->id }}">{{ $turma->nome }} — {{ $turma->alunos_count }} alunos</option>
                                    @endforeach
                                </select>
                            </label>
                            <x-btn type="submit" style="primary" class="rounded-full px-5 py-3">Adicionar aluno</x-btn>
                        </form>
                    </div>

                    <div class="hero-panel min-w-0 overflow-hidden rounded-[1.5rem] border-white/10 p-6 shadow-2xl">
                        <h2 class="text-xl font-semibold">Remanejar aluno</h2>
                        <form method="POST" action="{{ route('admin.turmas.aluno.remanejar') }}" class="mt-5 grid min-w-0 gap-4">
                            @csrf
                            <label class="form-field grid gap-2 text-sm text-white/75">
                                Aluno
                                <select name="aluno_id" class="rounded-lg border border-white/10 bg-slate-950/70 px-3 py-3 text-white" required>
                                    <option value="">Selecione</option>
                                    @foreach($alunos as $aluno)
                                        <option value="{{ $aluno->id }}">{{ $aluno->name }} — {{ $aluno->email }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <div class="grid min-w-0 gap-4 sm:grid-cols-2">
                                <label class="form-field grid gap-2 text-sm text-white/75">
                                    Turma de origem
                                    <select name="from_turma_id" class="rounded-lg border border-white/10 bg-slate-950/70 px-3 py-3 text-white" required>
                                        <option value="">Selecione</option>
                                        @foreach($turmas as $turma)
                                            <option value="{{ $turma->id }}">{{ $turma->nome }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="form-field grid gap-2 text-sm text-white/75">
                                    Turma de destino
                                    <select name="to_turma_id" class="rounded-lg border border-white/10 bg-slate-950/70 px-3 py-3 text-white" required>
                                        <option value="">Selecione</option>
                                        @foreach($turmas as $turma)
                                            <option value="{{ $turma->id }}">{{ $turma->nome }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <x-btn type="submit" style="primary" class="rounded-full px-5 py-3">Remanejar aluno</x-btn>
                        </form>
                    </div>
                </section>

                <section class="mt-10 grid gap-6 lg:grid-cols-3">
                    @forelse($alunos as $aluno)
                        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-xl transition hover:-translate-y-1 hover:bg-white/10">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-xl font-semibold text-white">{{ $aluno->name }}</h2>
                                    <p class="mt-2 text-sm text-white/70">{{ $aluno->email }}</p>
                                </div>
                                <span class="rounded-full bg-[#0043cb]/15 px-3 py-1 text-xs uppercase tracking-[0.24em] text-[#cfe3ff]">Aluno</span>
                            </div>
                            <p class="mt-4 text-sm text-white/70">Turmas: {{ $aluno->turmas_count }}</p>
                            @if($aluno->turmas->isNotEmpty())
                                <p class="mt-2 text-xs leading-5 text-white/55">{{ $aluno->turmas->pluck('nome')->join(', ') }}</p>
                            @endif
                            <form method="POST" action="{{ route('admin.alunos.destroy', $aluno->id) }}" class="mt-5">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="w-full rounded-lg border border-red-400/40 bg-red-400/10 px-3 py-2 text-sm font-semibold text-red-100 transition hover:bg-red-400/20"
                                    onclick="return confirm('Excluir este aluno? Ele será removido das turmas e do sistema.')"
                                >
                                    Excluir aluno
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="lg:col-span-3 rounded-[1.75rem] border border-white/10 bg-white/5 p-8 text-white/75">
                            Nenhum aluno cadastrado no sistema.
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
