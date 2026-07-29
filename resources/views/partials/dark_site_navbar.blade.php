@props([
    'context' => 'landing', // landing | studio
])

@php
    $tools = config('markcraft.tools', []);
    $isStudio = $context === 'studio';
@endphp

<header class="sticky top-0 z-[500] border-b border-white/5 bg-[#07090c]/90 backdrop-blur-md">
    <div class="mx-auto {{ $isStudio ? 'max-w-[1600px] px-3' : 'max-w-6xl px-4' }} py-3 flex items-center justify-between gap-3">
        <a href="{{ route('home') }}" class="mc-brand text-lg sm:text-xl font-extrabold text-white tracking-tight shrink-0 relative z-[501]">
            MarkCraft
            <span class="ms-1.5 sm:ms-2 align-middle text-[9px] font-semibold tracking-[0.14em] uppercase text-teal-300/90 border border-teal-500/30 px-1.5 py-0.5">CriaSys</span>
        </a>

        @if($isStudio)
            <div class="hidden xl:flex flex-1 min-w-0 max-w-sm mx-2 items-center">
                <a
                    href="{{ config('markcraft.blog.url') }}"
                    target="_blank"
                    rel="noopener"
                    class="truncate text-xs text-zinc-400 hover:text-amber-200 transition"
                    title="{{ config('markcraft.blog.headline') }}"
                >
                    <span class="text-amber-300/90">Blog CriaSys</span>
                    <span class="text-zinc-600"> · </span>
                    blog + painel + studio no mesmo fluxo →
                </a>
            </div>
        @endif

        {{-- Desktop / large tablet landscape --}}
        <nav class="hidden lg:flex flex-wrap items-center justify-end gap-1.5 xl:gap-2 text-sm text-zinc-300 relative z-[501]">
            @if($isStudio)
                <a href="{{ route('home') }}" class="px-2.5 py-1.5 rounded-md hover:bg-white/5 hover:text-white transition">Início</a>
                @foreach($tools as $slug => $tool)
                    <button
                        type="button"
                        @click="openTool('{{ $slug }}')"
                        class="inline-flex items-center gap-1.5 px-2 py-1.5 rounded-md hover:bg-white/5 text-zinc-300 hover:text-white"
                        title="{{ $tool['name'] }}"
                    >
                        <span class="inline-flex h-4 w-4 text-teal-300">
                            @include('partials.tool_icon', ['icon' => $tool['icon'] ?? 'link', 'size' => 16])
                        </span>
                        <span class="hidden xl:inline">{{ $tool['short'] ?? $tool['name'] }}</span>
                    </button>
                @endforeach
            @endif
            @include('partials.account_menu')
            @include('partials.hub_shortcut_buttons', ['variant' => 'nav'])
            @include('partials.studio_nav_button')
        </nav>

        {{-- Mobile / tablet: Studio neon + hamburger --}}
        <div class="flex lg:hidden items-center gap-2 relative z-[501]">
            @include('partials.studio_nav_button')
            <button
                type="button"
                class="mc-burger mc-nav-action !px-0 w-9 border border-white/15 bg-white/[0.04] text-zinc-100 hover:bg-white/[0.08] transition"
                :class="{ 'border-teal-400/50 bg-teal-500/10 text-teal-200': navOpen }"
                @click="toggleNav()"
                :aria-expanded="navOpen.toString()"
                aria-controls="mc-mobile-nav"
                aria-label="Abrir menu"
            >
                <span class="sr-only">Menu</span>
                <svg x-show="!navOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
                <svg x-show="navOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Scrim abaixo do painel quando menu aberto --}}
    <div
        x-show="navOpen"
        x-cloak
        class="lg:hidden fixed inset-0 top-[57px] z-[499] bg-black/55 backdrop-blur-[2px]"
        @click="closeNav()"
        aria-hidden="true"
    ></div>

    {{-- Painel mobile/tablet --}}
    <div
        id="mc-mobile-nav"
        x-show="navOpen"
        x-cloak
        class="lg:hidden relative z-[502] border-t border-white/5 bg-[#07090c]/98 backdrop-blur-md"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="mc-mobile-drawer mx-auto {{ $isStudio ? 'max-w-[1600px] px-3' : 'max-w-6xl px-4' }} py-4 max-h-[min(78vh,640px)] overflow-y-auto overscroll-contain">
            @auth
                <div class="mb-4 rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-3">
                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="mt-0.5 text-xs text-zinc-400 truncate">{{ Auth::user()->email }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <a href="{{ route('profile.edit') }}" class="rounded-lg border border-white/10 px-3 py-2 text-center text-xs text-zinc-200 hover:bg-white/5" @click="closeNav()">Perfil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-lg border border-rose-500/30 px-3 py-2 text-xs text-rose-300 hover:bg-rose-500/10">Desconectar</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="mb-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" class="rounded-lg border border-white/15 px-3 py-2.5 text-center text-sm text-zinc-100 hover:bg-white/5" @click="closeNav()">Entrar</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-teal-500 px-3 py-2.5 text-center text-sm font-semibold text-zinc-950 hover:bg-teal-400" @click="closeNav()">Criar conta</a>
                </div>
            @endauth

            <p class="mb-2 text-[10px] uppercase tracking-[0.16em] text-zinc-500">Navegação</p>
            <div class="space-y-1 mb-4">
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm text-zinc-100 hover:bg-white/5" @click="closeNav()">
                    Página inicial
                </a>
                @auth
                    <a href="{{ route('studio') }}" class="mc-studio-neon flex items-center justify-center gap-2 rounded-xl px-3 py-3 text-sm font-semibold text-[#39ff14]" @click="closeNav()">
                        Abrir Studio
                    </a>
                @else
                    <a href="{{ route('login') }}" class="mc-studio-neon flex items-center justify-center gap-2 rounded-xl px-3 py-3 text-sm font-semibold text-[#39ff14]" @click="closeNav()">
                        Studio (entrar)
                    </a>
                @endauth
            </div>

            <p class="mb-2 text-[10px] uppercase tracking-[0.16em] text-zinc-500">Ferramentas</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                @foreach($tools as $slug => $tool)
                    <button
                        type="button"
                        class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.03] px-3 py-3 text-left hover:border-teal-400/40"
                        @click="openTool('{{ $slug }}')"
                    >
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-teal-500/10 text-teal-300">
                            @include('partials.tool_icon', ['icon' => $tool['icon'] ?? 'link', 'size' => 18])
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-zinc-100">{{ $tool['name'] }}</span>
                            <span class="block text-[11px] text-zinc-500 truncate">{{ $tool['blurb'] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>

            <p class="mb-2 text-[10px] uppercase tracking-[0.16em] text-zinc-500">Hub</p>
            <div class="grid grid-cols-2 gap-2">
                <button
                    type="button"
                    class="flex items-center gap-2.5 rounded-xl border border-amber-400/25 bg-amber-500/10 px-3 py-3 text-left"
                    @click="openHub('packs')"
                >
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-400/30 bg-amber-500/15 text-amber-300">
                        @include('partials.tool_icon', ['icon' => 'packs', 'size' => 18])
                    </span>
                    <span class="text-sm font-semibold text-amber-100">Packs</span>
                </button>
                <button
                    type="button"
                    class="mc-shortcut-neon-shock flex items-center gap-2.5 rounded-xl px-3 py-3 text-left"
                    @click="openHub('apoiar')"
                >
                    <span class="mc-hub-icon inline-flex h-9 w-9 items-center justify-center rounded-lg border">
                        @include('partials.tool_icon', ['icon' => 'heart', 'size' => 18])
                    </span>
                    <span class="mc-shortcut-label text-sm font-semibold">Apoiar</span>
                </button>
            </div>
            <button
                type="button"
                class="mt-3 w-full rounded-xl border border-zinc-700 px-3 py-2.5 text-left text-sm text-zinc-300 hover:bg-zinc-900"
                @click="openCredits()"
            >
                Créditos e licenças
            </button>
        </div>
    </div>
</header>
