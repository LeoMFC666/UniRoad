<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'UniRoad') }}</title>
    <link rel="icon" type="image/png" sizes="any" href="{{ asset('img/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/logo.png') }}">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @ddfsnStyles

    <style>
        .account-bg {
            background: radial-gradient(circle at top, rgba(34, 211, 197, 0.14), transparent 20rem),
                linear-gradient(180deg, #080a10 0%, #0f172a 54%, #06182f 100%);
        }

        .nav-pill {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body class="account-bg min-h-screen text-white antialiased">
    <div class="relative z-10 mx-auto max-w-7xl px-6 py-8 lg:py-10">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('img/logo.png') }}" alt="UniRoad logo" class="h-12 w-12 rounded-full border-white/10 shadow-2xl" />
                <div>
                    <span class="font-semibold text-lg">UniRoad</span>
                    <div class="text-xs text-white/60">
                        @isset($header)
                            {{ $header }}
                        @else
                            Conta
                        @endisset
                    </div>
                </div>
            </a>

            <x-user-menu />
        </header>

        <nav class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('dashboard') }}" class="nav-pill rounded-full px-4 py-2 text-sm font-medium text-white/90 transition hover:bg-white/10">
                Dashboard
            </a>
        </nav>

        <x-email-verification-warning />
        <x-flash-messages />

        <main class="py-10 lg:py-14">
            {{ $slot }}
        </main>
    </div>

    @ddfsnScripts
</body>
</html>
