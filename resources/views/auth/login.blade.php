<x-guest-layout>
    @php
        $allowsRegister = \App\Support\MarkCraftShell::allowsRegister();
    @endphp

    <div class="mb-6">
        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-teal-400/90">Acesso</p>
        <h1 class="mc-brand mt-1 text-2xl font-bold text-white">Entrar no MarkCraft</h1>
        <p class="mt-1.5 text-sm text-zinc-400">Use seu e-mail e senha para abrir o Studio.</p>
    </div>

    <x-auth-session-status class="mb-4 rounded-lg border border-teal-500/30 bg-teal-500/10 px-3 py-2 text-sm text-teal-100" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="mc-auth-label" for="email">E-mail</label>
            <input id="email" class="mc-auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="voce@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div>
            <div class="mb-0.5 flex items-center justify-between gap-2">
                <label class="mc-auth-label !mb-0" for="password">Senha</label>
                @if (Route::has('password.request'))
                    <a class="mc-auth-link text-xs" href="{{ route('password.request') }}">Esqueci a senha</a>
                @endif
            </div>
            <input id="password" class="mc-auth-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-300" />
        </div>

        <label for="remember_me" class="flex items-center gap-2 text-sm text-zinc-400">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-zinc-600 bg-zinc-900 text-teal-500 focus:ring-teal-500/40">
            Manter conectado
        </label>

        <button type="submit" class="mc-auth-btn mt-2">Entrar</button>
    </form>

    @if ($allowsRegister)
        <p class="mt-6 text-center text-sm text-zinc-400">
            Ainda não tem conta?
            <a
                href="{{ !empty($startPreset) ? route('register', ['preset' => $startPreset]) : route('register') }}"
                class="mc-auth-link font-semibold"
            >Criar conta grátis</a>
        </p>
    @endif
</x-guest-layout>
