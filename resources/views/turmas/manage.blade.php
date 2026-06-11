<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Gerenciar {{ $turma->nome }}</title>

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
            .card-panel {
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.08);
                box-shadow: 0 24px 80px rgba(0, 67, 203, 0.12);
            }
            .nav-pill {
                background: rgba(255,255,255,0.08);
                border: 1px solid rgba(255,255,255,0.1);
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
            }
            .form-group input, .form-group select {
                width: 100%;
                padding: 10px;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 6px;
                color: white;
                box-sizing: border-box;
            }
            .form-group input::placeholder {
                color: rgba(255, 255, 255, 0.5);
            }
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative overflow-hidden">
            
            <div class="relative z-10 max-w-6xl mx-auto px-6 py-8 lg:py-10">
                @php
                    $dashboardRoute = auth()->user()->role === 'admin'
                        ? route('dashboard-admin')
                        : route('dashboard-docente');
                @endphp

                <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ $dashboardRoute }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                        <div>
                            <span class="font-semibold text-lg">{{ $turma->nome }}</span>
                            <div class="text-xs text-white/60">Gerenciar Turma</div>
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

                <main class="mt-10 grid gap-8 lg:grid-cols-2">
                    <!-- Adicionar Alunos -->
                    @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'docente' && $turma->docente_id === auth()->id()))
                        <div class="panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                            <h2 class="text-2xl font-semibold mb-6">Adicionar Alunos</h2>
                            
                            <form method="POST" action="{{ route('turmas.add-aluno', $turma->id) }}">
                                @csrf
                                <div class="form-group">
                                    <label for="aluno_email">E-mail do aluno</label>
                                    <input type="email" id="aluno_email" name="email" placeholder="exemplo@email.com" required>
                                    @error('email')
                                        <p class="text-sm text-red-400 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 rounded-full px-5 py-3 font-medium transition">
                                    ➕ Adicionar Aluno
                                </button>
                            </form>

                            @if(session('success'))
                                <div class="mt-4 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-200">
                                    {{ session('success') }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Informações da Turma -->
                    <div class="panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                        <h2 class="text-2xl font-semibold mb-6">Informações da Turma</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-white/60 text-sm">Nome</p>
                                <p class="text-lg font-medium">{{ $turma->nome }}</p>
                            </div>
                            
                            <div>
                                <p class="text-white/60 text-sm">Descrição</p>
                                <p class="text-lg">{{ $turma->descricao ?? 'Sem descrição' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-white/60 text-sm">Professor</p>
                                <p class="text-lg">{{ $turma->docente->name }}</p>
                            </div>
                            
                            <div>
                                <p class="text-white/60 text-sm">Total de Alunos</p>
                                <p class="text-lg font-semibold">{{ $turma->alunos->count() }}</p>
                            </div>
                        </div>
                    </div>
                </main>

                <!-- Lista de Alunos -->
                <div class="mt-8 panel rounded-[2rem] border-white/10 p-8 shadow-2xl">
                    <h2 class="text-2xl font-semibold mb-6">Alunos Inscritos ({{ $turma->alunos->count() }})</h2>
                    
                    @if($turma->alunos->count() > 0)
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($turma->alunos as $aluno)
                                <div class="card-panel rounded-2xl p-4 flex items-start justify-between">
                                    <div>
                                        <p class="font-medium text-white">{{ $aluno->name }}</p>
                                        <p class="text-sm text-white/60">{{ $aluno->email }}</p>
                                        <p class="text-xs text-white/50 mt-2">
                                            Inscrito em {{ optional($aluno->pivot->created_at)->format('d/m/Y H:i') ?? 'data não registrada' }}
                                        </p>
                                    </div>
                                    <form method="POST" action="{{ route('turmas.remove-aluno', [$turma->id, $aluno->id]) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Remover este aluno?')" class="text-red-400 hover:text-red-300 transition text-lg">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="card-panel rounded-2xl p-8 text-center text-white/70">
                            <p>Nenhum aluno inscrito ainda.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <script>
            async function handleAddAluno(event) {
                event.preventDefault();
                const email = document.getElementById('aluno_email').value;
                
                // Mantido apenas para compatibilidade visual; o envio real usa o formulário Blade acima.
                alert('Adicione o aluno pelo e-mail cadastrado no sistema.');
                document.getElementById('addAlunoForm').reset();
            }
        </script>

        @ddfsnScripts
    </body>
</html>
