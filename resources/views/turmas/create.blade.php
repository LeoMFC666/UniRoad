<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Criar Turma</title>

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
            .input-field {
                background: rgba(255,255,255,0.05);
                border: 1px solid rgba(255,255,255,0.12);
                color: white;
            }
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative overflow-hidden">
            
            <div class="relative z-10 max-w-4xl mx-auto px-6 py-8 lg:py-10">
                @php
                    $dashboardRoute = auth()->user()->role === 'admin'
                        ? route('dashboard-admin')
                        : route('dashboard-docente');
                @endphp

                <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ $dashboardRoute }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                        <div>
                            <span class="font-semibold text-lg">UniRoad</span>
                            <div class="text-xs text-white/60">Criar Turma</div>
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

                <main class="mt-10 panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                    <h1 class="text-3xl font-extrabold">Criar nova turma</h1>
                    <p class="mt-3 text-white/75">Preencha os dados para cadastrar uma turma na plataforma.</p>

                    <form method="POST" action="{{ route('turmas.store') }}" class="mt-8 space-y-6">
                        @csrf
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-white/80">Nome da Turma</label>
                            <input
                                type="text"
                                name="nome"
                                value="{{ old('nome') }}"
                                class="input-field w-full rounded-3xl px-4 py-3 placeholder:text-white/50 focus:outline-none focus:ring-2 focus:ring-[#0043cb]/50"
                                required
                            />
                            @error('nome')
                                <p class="text-sm text-red-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-white/80">Descrição</label>
                            <textarea
                                name="descricao"
                                rows="4"
                                class="input-field w-full rounded-3xl px-4 py-3 placeholder:text-white/50 focus:outline-none focus:ring-2 focus:ring-[#0043cb]/50"
                            >{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <p class="text-sm text-red-300">{{ $message }}</p>
                            @enderror
                        </div>
                        @if(auth()->user()->role === 'admin')
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-white/80">Professor responsável</label>
                                <select
                                    name="docente_id"
                                    class="input-field w-full rounded-3xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#0043cb]/50"
                                    required
                                >
                                    <option value="">Selecione um professor</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" @selected(old('docente_id') == $docente->id)>
                                            {{ $docente->name }} - {{ $docente->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('docente_id')
                                    <p class="text-sm text-red-300">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                            <a href="{{ route('turmas.index') }}" class="nav-pill rounded-full px-5 py-3 text-sm text-white/90">Cancelar</a>
                            <x-btn type="submit" style="primary" class="rounded-full px-6 py-3">Criar Turma</x-btn>
                        </div>
                    </form>
                </main>
            </div>
        </div>

        @ddfsnScripts
    </body>
</html>
