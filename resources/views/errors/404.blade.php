<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Página não encontrada - {{ config('app.name', 'UniRoad') }}</title>
        <link rel="icon" type="image/png" sizes="any" href="{{ asset('img/logo.png') }}">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-950 text-white">
        <main class="mx-auto flex min-h-screen max-w-3xl flex-col items-center justify-center px-6 text-center">
            <img src="{{ asset('img/logo.png') }}" alt="UniRoad" class="mb-6 h-16 w-16 rounded-full">
            <p class="text-sm font-bold uppercase tracking-[0.3em] text-cyan-200">Página não encontrada</p>
            <h1 class="mt-4 text-4xl font-black">Não encontramos o caminho que você tentou acessar.</h1>
            <p class="mt-4 max-w-xl text-slate-300">O link pode ter mudado ou não estar mais disponível.</p>
            <a href="{{ url('/') }}" class="mt-8 rounded-full bg-cyan-400 px-6 py-3 text-sm font-bold text-slate-950">Ir para o início</a>
        </main>
    </body>
</html>
