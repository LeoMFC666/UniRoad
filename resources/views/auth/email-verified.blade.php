<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'UniRoad') }} - Conta verificada</title>
        <link rel="icon" type="image/png" sizes="any" href="{{ asset('img/logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('img/logo.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .verify-bg { background: radial-gradient(circle at top, rgba(34,211,238,0.18), transparent 24%), #030712; }
            .verify-title { text-shadow: 0 4px 0 rgba(15,23,42,0.95); }
        </style>
    </head>
    <body class="verify-bg min-h-screen text-white antialiased">
        <main class="min-h-screen flex items-center justify-center px-6 py-16">
            <section class="max-w-2xl text-center">
                <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="mx-auto mb-8 h-20 w-20 object-contain">

                <p class="mb-6 text-sm font-bold uppercase tracking-[0.55em] text-cyan-200">
                    Conta confirmada
                </p>

                <h1 class="verify-title text-4xl font-extrabold leading-tight sm:text-5xl">
                    Sua conta foi verificada
                </h1>

                <p class="mx-auto mt-6 max-w-xl text-base leading-7 text-white/80">
                    Tudo certo. Agora você já pode acessar suas turmas, roadmaps e recursos da UniRoad com mais segurança.
                </p>

                <a href="{{ route('dashboard') }}" class="mt-9 inline-flex items-center justify-center rounded-full bg-cyan-400 px-8 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-400/20 transition hover:bg-cyan-300">
                    Ir para o início
                </a>
            </section>
        </main>
    </body>
</html>
