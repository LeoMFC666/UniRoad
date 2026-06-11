<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Roadmaps de {{ $turma->nome }}</title>

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
            .panel {
                background: rgba(16, 24, 50, 0.92);
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 32px 120px rgba(0, 67, 203, 0.18);
            }
            .card-panel {
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.08);
                box-shadow: 0 24px 80px rgba(0, 67, 203, 0.12);
                transition: all 0.3s ease;
            }
            .card-panel:hover {
                background: rgba(255,255,255,0.1);
                border-color: rgba(255,255,255,0.15);
            }
            .nav-pill {
                background: rgba(255,255,255,0.08);
                border: 1px solid rgba(255,255,255,0.1);
            }
            .btn-primary {
                background: rgba(59, 130, 246, 0.9);
                border: 1px solid rgba(59, 130, 246, 0.5);
                padding: 12px 24px;
                border-radius: 9999px;
                color: white;
                text-decoration: none;
                display: inline-block;
                transition: all 0.3s ease;
            }
            .btn-primary:hover {
                background: rgba(59, 130, 246, 1);
                transform: scale(1.05);
            }
            .btn-danger {
                background: rgba(239, 68, 68, 0.9);
                border: 1px solid rgba(239, 68, 68, 0.5);
                padding: 8px 16px;
                border-radius: 6px;
                color: white;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            .btn-danger:hover {
                background: rgba(239, 68, 68, 1);
            }
            .roadmap-empty-state {
                background: rgba(255, 255, 255, 0.03) !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
                color: #f8fafc !important;
            }
            .roadmap-empty-state [data-slot="heading"] {
                color: #f8fafc !important;
            }
            .roadmap-empty-state p,
            .roadmap-empty-state [data-slot="paragraph"] {
                color: #cbd5e1 !important;
            }
            .roadmap-empty-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 0.5rem;
                background: rgba(255, 255, 255, 0.08);
                color: #67e8f9;
            }
            .roadmap-empty-icon svg {
                width: 1rem;
                height: 1rem;
                color: currentColor;
            }
            .roadmap-empty-actions {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
            }
            @media (min-width: 640px) {
                .roadmap-empty-actions {
                    flex-direction: row;
                }
            }
            .import-roadmap-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 2rem;
                padding: 0.5rem 0.75rem;
                border-radius: 0.5rem;
                border: 1px solid rgba(255, 255, 255, 0.1);
                background: rgba(255, 255, 255, 0.08);
                color: rgba(255, 255, 255, 0.92);
                font-size: 0.875rem;
                font-weight: 700;
                cursor: pointer;
                transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
            }
            .import-roadmap-btn:hover {
                background: rgba(255, 255, 255, 0.14);
                border-color: rgba(255, 255, 255, 0.2);
                transform: translateY(-1px);
            }
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative overflow-hidden">
            
            <div class="relative z-10 max-w-6xl mx-auto px-6 py-8 lg:py-10">
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
                            <div class="text-xs text-white/60">Roadmaps</div>
                        </div>
                    </a>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('turmas.show', $turma->id) }}" class="nav-pill rounded-full px-4 py-2 text-sm text-white/90">Voltar</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-btn type="submit" style="secondary" class="rounded-full px-4 py-2">Sair</x-btn>
                        </form>
                    </div>
                </header>

                <x-email-verification-warning />

                <main class="mt-10">
                    <div class="panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h1 class="text-3xl font-extrabold">Roadmaps</h1>
                                <p class="mt-2 text-white/75">Gerencie os roadmaps e diagramas de estudo da turma</p>
                            </div>
                            @php
                                $canEditRoadmaps = Auth::user()->role === 'docente' && $turma->docente_id === Auth::id();
                                $canDeleteRoadmaps = Auth::user()->role === 'admin' || $canEditRoadmaps;
                            @endphp
                            @if($canEditRoadmaps)
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="{{ route('roadmaps.create', ['turmaId' => $turma->id]) }}" class="btn-primary">+ Novo Roadmap</a>
                                    <form method="POST" action="{{ route('roadmaps.import', $turma->id) }}" enctype="multipart/form-data">
                                        @csrf
                                        <label class="import-roadmap-btn">
                                            Importar roadmap
                                            <input
                                                type="file"
                                                name="roadmap_file"
                                                accept="application/json,.json"
                                                class="sr-only"
                                                onchange="this.form.submit()">
                                        </label>
                                    </form>
                                </div>
                            @endif
                        </div>

                        @if(session('success'))
                            <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-200">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-6 p-4 bg-red-500/15 border border-red-500/40 rounded-lg text-red-100">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($errors->has('roadmap_file'))
                            <div class="mb-6 p-4 bg-red-500/15 border border-red-500/40 rounded-lg text-red-100">
                                {{ $errors->first('roadmap_file') }}
                            </div>
                        @endif

                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @forelse($roadmaps as $roadmap)
                                <div class="card-panel rounded-2xl p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <h3 class="text-xl font-semibold text-white">{{ $roadmap->titulo }}</h3>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <a href="{{ route('roadmaps.show', [$turma->id, $roadmap->id]) }}" class="text-cyan-300 hover:text-cyan-200 transition">
                                                🔍 Ver
                                            </a>
                                            @if($canEditRoadmaps)
                                                <a href="{{ route('roadmaps.edit', [$turma->id, $roadmap->id]) }}" class="text-blue-400 hover:text-blue-300 transition">
                                                    ✏️ Editar
                                                </a>
                                            @endif
                                            @if($canDeleteRoadmaps)
                                                <form method="POST" action="{{ route('roadmaps.destroy', [$turma->id, $roadmap->id]) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Tem certeza?')" class="text-red-400 hover:text-red-300 transition">
                                                        🗑️ Deletar
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if($roadmap->descricao)
                                        <p class="text-white/70 text-sm mb-4">{{ Str::limit($roadmap->descricao, 100) }}</p>
                                    @endif
                                    
                                    <div class="text-xs text-white/50">
                                        Criado em {{ $roadmap->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            @empty
                                <div class="lg:col-span-3">
                                    <x-empty
                                        title="Nenhum roadmap criado ainda"
                                        description="{{ $canEditRoadmaps ? 'Crie um novo roadmap para a turma ou importe um arquivo JSON exportado pela UniRoad.' : 'Quando houver roadmaps disponíveis, eles aparecerão aqui.' }}"
                                        class="roadmap-empty-state min-h-[18rem]">
                                        <x-slot:icon>
                                            <span class="roadmap-empty-icon" aria-hidden="true">
                                                <x-heroicon-o-map />
                                            </span>
                                        </x-slot:icon>

                                        @if($canEditRoadmaps)
                                            <div class="roadmap-empty-actions">
                                                <x-btn
                                                    href="{{ route('roadmaps.create', ['turmaId' => $turma->id]) }}"
                                                    size="sm">
                                                    Criar novo roadmap
                                                </x-btn>

                                                <form
                                                    method="POST"
                                                    action="{{ route('roadmaps.import', $turma->id) }}"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <label class="import-roadmap-btn">
                                                        Importar roadmap
                                                        <input
                                                            type="file"
                                                            name="roadmap_file"
                                                            accept="application/json,.json"
                                                            class="sr-only"
                                                            onchange="this.form.submit()">
                                                    </label>
                                                </form>
                                            </div>
                                        @endif
                                    </x-empty>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </main>
            </div>
        </div>

        @ddfsnScripts
    </body>
</html>
