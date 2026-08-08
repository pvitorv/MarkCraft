<x-guest-layout>
    <div class="mb-6">
        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-teal-400/90">Recuperação</p>
        <h1 class="mc-brand mt-1 text-2xl font-bold text-white">Esqueceu a senha?</h1>
        <p class="mt-1.5 text-sm text-zinc-400">
            Informe o e-mail da conta. Se existir um cadastro, enviaremos um link para criar uma nova senha.
        </p>
    </div>

    <x-auth-session-status class="mb-4 rounded-lg border border-teal-500/30 bg-teal-500/10 px-3 py-2 text-sm text-teal-100" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label class="mc-auth-label" for="email">E-mail</label>
            <input id="email" class="mc-auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="voce@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-300" />
        </div>

        <button type="submit" class="mc-auth-btn">Enviar link de recuperação</button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-400">
        <a href="{{ route('login') }}" class="mc-auth-link">← Voltar ao login</a>
    </p>
</x-guest-layout>
