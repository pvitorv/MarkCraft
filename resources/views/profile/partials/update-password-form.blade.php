<header class="mb-5">
    <h2 class="text-lg font-semibold text-white">Alterar senha</h2>
    <p class="mt-1 text-sm text-zinc-400">Use uma senha longa e segura.</p>
</header>

<form method="post" action="{{ route('password.update') }}" class="space-y-4">
    @csrf
    @method('put')

    <div>
        <label class="mc-auth-label" for="update_password_current_password">Senha atual</label>
        <input
            id="update_password_current_password"
            name="current_password"
            type="password"
            class="mc-auth-input"
            autocomplete="current-password"
        >
        <x-input-error class="mt-2 text-sm text-rose-300" :messages="$errors->updatePassword->get('current_password')" />
    </div>

    <div>
        <label class="mc-auth-label" for="update_password_password">Nova senha</label>
        <input
            id="update_password_password"
            name="password"
            type="password"
            class="mc-auth-input"
            autocomplete="new-password"
        >
        <x-input-error class="mt-2 text-sm text-rose-300" :messages="$errors->updatePassword->get('password')" />
    </div>

    <div>
        <label class="mc-auth-label" for="update_password_password_confirmation">Confirmar nova senha</label>
        <input
            id="update_password_password_confirmation"
            name="password_confirmation"
            type="password"
            class="mc-auth-input"
            autocomplete="new-password"
        >
        <x-input-error class="mt-2 text-sm text-rose-300" :messages="$errors->updatePassword->get('password_confirmation')" />
    </div>

    <div class="flex flex-wrap items-center gap-3 pt-1">
        <button type="submit" class="mc-auth-btn">Salvar senha</button>

        @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2200)"
                class="text-sm text-teal-300"
            >Salvo.</p>
        @endif
    </div>
</form>
