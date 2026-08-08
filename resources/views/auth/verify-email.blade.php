<x-guest-layout>
    <div class="mb-6">
        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-teal-400/90">Verificação</p>
        <h1 class="mc-brand mt-1 text-2xl font-bold text-white">Confirme seu e-mail</h1>
        <p class="mt-1.5 text-sm text-zinc-400">
            Enviamos um link de confirmação. Se não chegou, você pode pedir outro envio abaixo.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg border border-teal-500/30 bg-teal-500/10 px-3 py-2 text-sm text-teal-100">
            Um novo link foi enviado para o seu e-mail.
        </div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <button type="submit" class="mc-auth-btn">Reenviar e-mail</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full rounded-lg border border-white/10 px-4 py-2.5 text-sm text-zinc-300 hover:bg-white/5 hover:text-rose-300 transition sm:w-auto">
                Sair
            </button>
        </form>
    </div>
</x-guest-layout>
