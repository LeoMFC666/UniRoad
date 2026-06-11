@props(['label' => 'Continue com o Google'])

<a
    href="{{ route('auth.google.redirect') }}"
    {{ $attributes->merge([
        'class' => 'mt-4 flex w-full items-center justify-center gap-3 rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-extrabold text-white shadow-lg transition hover:-translate-y-0.5 hover:border-cyan-300/40 hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-cyan-300/60',
    ]) }}
>
    <svg aria-hidden="true" viewBox="0 0 24 24" class="h-6 w-6">
        <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.5-.2-2.2H12v4.2h6.5c-.3 1.4-1.1 2.7-2.4 3.5v2.9h3.8c2.2-2.1 3.5-5.1 3.5-8.4z"/>
        <path fill="#34A853" d="M12 24c3.2 0 5.9-1.1 7.9-2.9l-3.8-2.9c-1.1.7-2.4 1.1-4.1 1.1-3.1 0-5.7-2.1-6.6-4.9H1.5v3c2 4 6.1 6.6 10.5 6.6z"/>
        <path fill="#FBBC05" d="M5.4 14.3c-.2-.7-.4-1.5-.4-2.3s.1-1.6.4-2.3v-3H1.5C.5 8.6 0 10.3 0 12s.5 3.4 1.5 5.3l3.9-3z"/>
        <path fill="#EA4335" d="M12 4.8c1.7 0 3.3.6 4.5 1.8l3.4-3.4C17.9 1.2 15.2 0 12 0 7.6 0 3.5 2.6 1.5 6.7l3.9 3C6.3 6.9 8.9 4.8 12 4.8z"/>
    </svg>
    <span>{{ $label }}</span>
</a>
