<x-guest-layout>
    <div class="mb-6">
        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-teal-400/90">Recuperação</p>
        <h1 class="mc-brand mt-1 text-2xl font-bold text-white">Definir nova senha</h1>
        <p class="mt-1.5 text-sm text-zinc-400">Escolha uma senha forte e confirme abaixo.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label class="mc-auth-label" for="email">E-mail</label>
            <input id="email" class="mc-auth-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div>
            <label class="mc-auth-label" for="password">Nova senha</label>
            <input id="password" class="mc-auth-input" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div>
            <label class="mc-auth-label" for="password_confirmation">Confirmar nova senha</label>
            <input id="password_confirmation" class="mc-auth-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repita a senha">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-rose-300" />
        </div>

        <button type="submit" class="mc-auth-btn">Salvar nova senha</button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-400">
        <a href="{{ route('login') }}" class="mc-auth-link">← Voltar ao login</a>
    </p>
</x-guest-layout>
