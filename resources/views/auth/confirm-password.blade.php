<x-guest-layout>
    <div class="mb-6">
        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-teal-400/90">Segurança</p>
        <h1 class="mc-brand mt-1 text-2xl font-bold text-white">Confirme sua senha</h1>
        <p class="mt-1.5 text-sm text-zinc-400">
            Esta área é protegida. Digite sua senha para continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label class="mc-auth-label" for="password">Senha</label>
            <input id="password" class="mc-auth-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-300" />
        </div>

        <button type="submit" class="mc-auth-btn">Confirmar</button>
    </form>
</x-guest-layout>
