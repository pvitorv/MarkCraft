<x-guest-layout>
    <div class="mb-6">
        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-teal-400/90">Cadastro</p>
        <h1 class="mc-brand mt-1 text-2xl font-bold text-white">Criar sua conta</h1>
        <p class="mt-1.5 text-sm text-zinc-400">Leva menos de um minuto. Depois você já entra no Studio.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        @if(!empty($startPreset))
            <input type="hidden" name="preset" value="{{ $startPreset }}">
        @endif

        <div>
            <label class="mc-auth-label" for="name">Nome</label>
            <input id="name" class="mc-auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Seu nome">
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div>
            <label class="mc-auth-label" for="email">E-mail</label>
            <input id="email" class="mc-auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="voce@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div>
            <label class="mc-auth-label" for="password">Senha</label>
            <input id="password" class="mc-auth-input" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div>
            <label class="mc-auth-label" for="password_confirmation">Confirmar senha</label>
            <input id="password_confirmation" class="mc-auth-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repita a senha">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-rose-300" />
        </div>

        <button type="submit" class="mc-auth-btn mt-2">Criar conta e entrar</button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-400">
        Já tem conta?
        <a href="{{ route('login') }}" class="mc-auth-link font-semibold">Entrar</a>
    </p>
</x-guest-layout>
