<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-white">
            Excluir conta
        </h2>

        <p class="mt-1 text-sm text-slate-300">
            Ao excluir sua conta, seus dados vinculados serão removidos permanentemente. Revise suas informações antes de continuar.
        </p>

        @if(auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
            <p class="mt-3 rounded-lg border border-amber-300/30 bg-amber-300/10 px-4 py-3 text-sm text-amber-100">
                Verifique seu e-mail antes de solicitar a exclusão da conta.
            </p>
        @endif
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Excluir conta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Tem certeza de que deseja excluir sua conta?
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Esta ação não pode ser desfeita. O e-mail da conta precisa estar verificado, e sua senha atual será exigida para confirmar a exclusão permanente.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Senha') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Senha') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Excluir conta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
