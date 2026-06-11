<x-app-layout>
    <x-slot name="header">
        Conta
    </x-slot>

    <div class="space-y-6">
        <section class="panel p-6">
            <p class="text-sm font-bold uppercase tracking-[0.28em] text-cyan-200">Conta</p>
            <h1 class="mt-3 text-2xl font-black text-white">Seus dados na UniRoad</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                Revise seu nome, e-mail de acesso e status de verificação da conta.
            </p>
        </section>

        <section class="panel p-6">
            @include('profile.partials.update-profile-information-form')
        </section>

        <section class="panel p-6">
            @include('profile.partials.update-password-form')
        </section>
    </div>
</x-app-layout>
