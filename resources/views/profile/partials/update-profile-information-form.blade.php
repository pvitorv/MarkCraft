<header class="mb-5">
    <h2 class="text-lg font-semibold text-white">Nome e e-mail</h2>
    <p class="mt-1 text-sm text-zinc-400">Atualize os dados da sua conta.</p>
</header>

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf
    @method('patch')

    <div>
        <label class="mc-auth-label" for="name">Nome</label>
        <input
            id="name"
            name="name"
            type="text"
            class="mc-auth-input"
            value="{{ old('name', $user->name) }}"
            required
            autofocus
            autocomplete="name"
        >
        <x-input-error class="mt-2 text-sm text-rose-300" :messages="$errors->get('name')" />
    </div>

    <div>
        <label class="mc-auth-label" for="email">E-mail</label>
        <input
            id="email"
            name="email"
            type="email"
            class="mc-auth-input"
            value="{{ old('email', $user->email) }}"
            required
            autocomplete="username"
        >
        <x-input-error class="mt-2 text-sm text-rose-300" :messages="$errors->get('email')" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <p class="mt-2 text-sm text-amber-200/90">
                E-mail ainda não confirmado.
                <button form="send-verification" class="mc-auth-link font-semibold">
                    Reenviar confirmação
                </button>
            </p>

            @if (session('status') === 'verification-link-sent')
                <p class="mt-2 text-sm font-medium text-teal-300">
                    Novo link enviado para o seu e-mail.
                </p>
            @endif
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-3 pt-1">
        <button type="submit" class="mc-auth-btn">Salvar</button>

        @if (session('status') === 'profile-updated')
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
