<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'UniRoad') }} - Contato</title>
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
            .nav-pill,
            .hero-panel,
            .card-panel {
                background: rgba(17, 21, 32, 0.88);
                border: 1px solid rgba(148, 163, 184, 0.16);
                border-radius: 8px;
            }
            .contact-field {
                background: rgba(15, 23, 42, 0.84);
                border: 1px solid rgba(148, 163, 184, 0.22);
                border-radius: 8px;
                color: #f8fafc;
                padding: 12px 14px;
                width: 100%;
            }
            .contact-field:focus {
                border-color: rgba(34, 211, 197, 0.68);
                box-shadow: 0 0 0 3px rgba(34, 211, 197, 0.14);
                outline: none;
            }
        </style>
    </head>
    <body class="landing-bg min-h-screen text-white antialiased">
        <div class="relative z-10 max-w-7xl mx-auto px-6 py-8 lg:py-10">
            <header class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="w-12 h-12 rounded-full border-white/10 shadow-2xl" />
                    <div>
                        <span class="font-semibold text-lg">UniRoad</span>
                        <div class="text-xs text-white/60">Contato comercial</div>
                    </div>
                </a>

                <nav class="inline-flex flex-wrap justify-start gap-3 sm:justify-end w-full sm:w-auto">
                    <x-btn href="{{ route('login') }}" class="nav-pill rounded-full px-4 py-2 text-white !text-white">Entrar</x-btn>
                    <x-btn href="{{ route('register') }}" style="primary" class="rounded-full px-4 py-2">Cadastrar</x-btn>
                </nav>
            </header>

            <main class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] items-start py-16 lg:py-20">
                <section class="hero-panel p-8 lg:p-10 shadow-2xl">
                    <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl">Vamos conversar sobre a UniRoad?</h1>
                    <p class="mt-5 text-lg leading-8 text-white/75">Se sua escola, curso ou equipe precisa organizar trilhas de aprendizado, a UniRoad ajuda professores a criar roadmaps claros e alunos a seguir cada etapa com mais segurança.</p>
                    <p class="mt-6 text-sm text-white/60">Seus dados são armazenados com segurança e protegidos por criptografia.</p>
                    @if(session('success'))
                        <div class="mt-6 rounded-lg border border-emerald-400/40 bg-emerald-400/10 p-4 text-emerald-100">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="mt-6 rounded-lg border border-amber-400/40 bg-amber-400/10 p-4 text-amber-100">
                            {{ session('warning') }}
                        </div>
                    @endif
                </section>

                <section class="card-panel p-6 lg:p-8">
                    <form method="POST" action="{{ route('contato.store') }}">
                        @csrf
                        <h2 class="text-xl font-bold">Fale com a equipe UniRoad</h2>
                        <p class="mt-2 text-sm text-white/65">Informe seu nome e e-mail para receber uma resposta comercial.</p>
                        <div class="mt-5 grid gap-4">
                            <label class="grid gap-2 text-sm font-semibold text-white/80">
                                Nome
                                <input class="contact-field" name="name" value="{{ old('name') }}" required>
                            </label>
                            <label class="grid gap-2 text-sm font-semibold text-white/80">
                                E-mail
                                <input class="contact-field" type="email" name="email" value="{{ old('email') }}" required>
                            </label>
                            @if($errors->any())
                                <p class="text-sm text-red-300">{{ $errors->first() }}</p>
                            @endif
                            <x-btn type="submit" style="primary" class="w-full rounded-full">Quero receber contato</x-btn>
                        </div>
                    </form>
                </section>
            </main>

            <footer class="mt-10 border-t border-white/10 pt-6 text-sm text-white/60 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <span>© {{ date('Y') }} {{ config('app.name', 'UniRoad') }}.</span>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="hover:text-white">Início</a>
                    <a href="{{ route('sobre') }}" class="hover:text-white">Sobre</a>
                </div>
            </footer>
        </div>

        @ddfsnScripts
    </body>
</html>
