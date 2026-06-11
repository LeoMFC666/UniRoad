@auth
    <x-dropdown align="right" width="48" contentClasses="py-2 bg-slate-950/95 border border-white/10 text-white">
        <x-slot name="trigger">
            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-white/90 transition hover:border-cyan-300/40 hover:bg-white/10">
                <span class="max-w-36 truncate">{{ auth()->user()->name }}</span>
                <svg class="h-4 w-4 text-cyan-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <a href="{{ route('account.settings') }}" class="block px-4 py-2 text-sm text-slate-100 transition hover:bg-white/10">
                Configurações da conta
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-rose-100 transition hover:bg-rose-400/10">
                    Sair
                </button>
            </form>
        </x-slot>
    </x-dropdown>
@endauth
