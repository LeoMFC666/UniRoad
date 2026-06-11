<x-app-layout>
    <x-slot name="header">
        Configurações da conta
    </x-slot>

    <div class="space-y-6">
        <section class="mt-6 panel p-6">
            <p class="text-sm font-bold uppercase tracking-[0.28em] text-cyan-200">Conta</p>
            <h1 class="mt-3 text-2xl font-black text-white">Ajustes pessoais e segurança</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                Atualize seu nome, revise os dados principais da conta e use confirmações seguras para senha e exclusão.
            </p>

            <dl class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                    <dt class="text-xs uppercase tracking-[0.22em] text-slate-400">Nome</dt>
                    <dd class="mt-2 text-sm font-semibold text-white">{{ $user->name }}</dd>
                </div>

                <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                    <dt class="text-xs uppercase tracking-[0.22em] text-slate-400">E-mail de acesso</dt>
                    <dd class="mt-2 break-all text-sm font-semibold text-white">{{ $user->email }}</dd>
                </div>

                <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                    <dt class="text-xs uppercase tracking-[0.22em] text-slate-400">Tipo de conta</dt>
                    <dd class="mt-2 text-sm font-semibold text-white">
                        {{ $user->role === 'docente' ? 'professor' : $user->role }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="panel p-6">
            @include('profile.partials.update-profile-information-form')
        </section>

        <section class="panel p-6">
            @include('profile.partials.update-password-form')
        </section>

        <section class="panel p-6">
            @include('profile.partials.delete-user-form')
        </section>
    </div>
</x-app-layout>
