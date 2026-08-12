<div
    x-data="{ confirmDelete: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }"
    class="space-y-4"
>
    <header>
        <h2 class="text-lg font-semibold text-rose-200">Excluir conta</h2>
        <p class="mt-1 text-sm text-zinc-400">
            Remove permanentemente sua conta. Baixe suas artes antes — o MarkCraft não guarda arquivos no servidor.
        </p>
    </header>

    <button
        type="button"
        class="mc-auth-btn mc-auth-btn--danger"
        @click="confirmDelete = true"
    >
        Excluir conta
    </button>

    <div
        x-show="confirmDelete"
        x-cloak
        class="fixed inset-0 z-[600] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="mc-delete-account-title"
    >
        <div class="absolute inset-0 bg-black/70" @click="confirmDelete = false"></div>
        <div class="mc-account-card relative z-10 w-full max-w-md rounded-2xl px-5 py-6 sm:px-6">
            <h3 id="mc-delete-account-title" class="text-lg font-semibold text-white">Confirmar exclusão</h3>
            <p class="mt-2 text-sm text-zinc-400">
                Digite sua senha para excluir a conta de forma permanente.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-5 space-y-4">
                @csrf
                @method('delete')

                <div>
                    <label class="mc-auth-label" for="password">Senha</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="mc-auth-input"
                        placeholder="••••••••"
                        required
                    >
                    <x-input-error class="mt-2 text-sm text-rose-300" :messages="$errors->userDeletion->get('password')" />
                </div>

                <div class="flex flex-wrap justify-end gap-2 pt-1">
                    <button type="button" class="mc-auth-btn mc-auth-btn--ghost" @click="confirmDelete = false">
                        Cancelar
                    </button>
                    <button type="submit" class="mc-auth-btn mc-auth-btn--danger">
                        Excluir definitivamente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
