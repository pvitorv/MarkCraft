@extends('layouts.account')

@section('title', 'Minha conta')

@section('content')
    <div class="mb-6">
        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-teal-400/90">Conta</p>
        <h1 class="mc-brand mt-1 text-2xl font-bold text-white sm:text-3xl">Minha conta</h1>
        <p class="mt-1.5 text-sm text-zinc-400">Nome, e-mail, senha e sessão.</p>
        <div class="mt-4 flex flex-wrap gap-3 text-sm">
            <a href="{{ route('studio') }}" class="mc-auth-link font-semibold">Abrir Studio</a>
            <span class="text-zinc-600">·</span>
            <a href="{{ route('home') }}" class="mc-auth-link">Início</a>
        </div>
    </div>

    <div class="space-y-5">
        <section class="mc-account-card rounded-2xl px-5 py-6 sm:px-7 sm:py-7">
            @include('profile.partials.update-profile-information-form')
        </section>

        <section class="mc-account-card rounded-2xl px-5 py-6 sm:px-7 sm:py-7">
            @include('profile.partials.update-password-form')
        </section>

        <section class="mc-account-card rounded-2xl px-5 py-6 sm:px-7 sm:py-7">
            <header class="mb-5">
                <h2 class="text-lg font-semibold text-white">Sessão</h2>
                <p class="mt-1 text-sm text-zinc-400">Encerrar o acesso neste dispositivo.</p>
            </header>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mc-auth-btn mc-auth-btn--ghost">Desconectar</button>
            </form>
        </section>

        <section class="mc-account-card rounded-2xl px-5 py-6 sm:px-7 sm:py-7 border-rose-900/40">
            @include('profile.partials.delete-user-form')
        </section>
    </div>
@endsection
