@props([
    'context' => 'landing', // landing | studio
])

@php
    $tools = config('markcraft.tools', []);
    $isStudio = $context === 'studio';
    $desktop = ! empty($markcraftDesktop);
@endphp

@php
    $studioWebFull = $context === 'studio' && empty($markcraftDesktop);
    $shellWidthClass = $studioWebFull
        ? 'w-full max-w-none px-3 lg:px-5 xl:px-6'
        : ($isStudio ? 'mx-auto max-w-[1600px] w-full px-3' : 'mx-auto max-w-6xl px-4');
@endphp

<header class="sticky top-0 z-[500] border-b border-white/5 bg-[#07090c]/90 backdrop-blur-md">
    <div class="{{ $shellWidthClass }} py-3 flex items-center justify-between gap-3">
        @include('partials.markcraft_brand', [
            'href' => $desktop ? route('studio') : route('home'),
            'variant' => 'mark',
            'badge' => $desktop ? 'desktop' : 'criasys',
        ])

        @if($isStudio && ! $desktop)
            <div class="hidden xl:flex min-w-0 max-w-[14rem] items-center">
                <a
                    href="{{ route('home') }}"
                    class="truncate text-xs text-zinc-400 hover:text-teal-200 transition"
                >
                    Portal de ferramentas gratuitas
                </a>
            </div>
        @endif

        {{-- Desktop / large tablet landscape --}}
        <nav class="hidden lg:flex flex-wrap items-center justify-end gap-1.5 xl:gap-2 text-sm text-zinc-300 relative z-[501]">
            @if($isStudio && ! $desktop)
                <a href="{{ route('home') }}" class="px-2.5 py-1.5 rounded-md hover:bg-white/5 hover:text-white transition">Início</a>
            @endif
            @unless($isStudio)
                <a href="{{ route('studio') }}" class="px-2.5 py-1.5 rounded-md hover:bg-white/5 hover:text-white transition">Studio</a>
                <a href="#ferramentas" class="px-2.5 py-1.5 rounded-md hover:bg-white/5 hover:text-white transition">Ferramentas</a>
                <a href="{{ route('legal.show', 'privacidade') }}" class="px-2.5 py-1.5 rounded-md hover:bg-white/5 hover:text-white transition">Privacidade</a>
                <a href="{{ route('legal.show', 'termos') }}" class="px-2.5 py-1.5 rounded-md hover:bg-white/5 hover:text-white transition">Termos de Uso</a>
            @endunless
            @include('partials.account_menu')
            @unless($desktop)
                @include('partials.hub_shortcut_buttons', ['variant' => 'nav', 'showPacks' => $isStudio])
                @include('partials.studio_nav_button')
            @endunless
        </nav>

        {{-- Mobile / tablet: Studio neon + hamburger --}}
        <div class="flex lg:hidden items-center gap-2 relative z-[501]">
            @unless($desktop)
                @include('partials.studio_nav_button')
            @endunless
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
                <div class="mb-4 grid grid-cols-{{ !empty($markcraftAllowsRegister) ? '2' : '1' }} gap-2">
                    <a href="{{ route('login') }}" class="rounded-lg border border-white/15 px-3 py-2.5 text-center text-sm text-zinc-100 hover:bg-white/5" @click="closeNav()">Entrar</a>
                    @if(! empty($markcraftAllowsRegister))
                        <a href="{{ route('register') }}" class="rounded-lg bg-teal-500 px-3 py-2.5 text-center text-sm font-semibold text-zinc-950 hover:bg-teal-400" @click="closeNav()">Criar conta</a>
                    @endif
                </div>
            @endauth

            <p class="mb-2 text-[10px] uppercase tracking-[0.16em] text-zinc-500">Navegação</p>
            <div class="space-y-1 mb-4">
                @unless($desktop)
                    <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm text-zinc-100 hover:bg-white/5" @click="closeNav()">
                        Página inicial
                    </a>
                @endunless
                @auth
                    <a href="{{ route('studio') }}" class="mc-studio-neon flex items-center justify-center gap-2 rounded-xl px-3 py-3 text-sm font-semibold text-[#39ff14]" @click="closeNav()">
                        Abrir Studio
                    </a>
                @else
                    <a href="{{ route('login') }}" class="mc-studio-neon flex items-center justify-center gap-2 rounded-xl px-3 py-3 text-sm font-semibold text-[#39ff14]" @click="closeNav()">
                        Studio (entrar)
                    </a>
                @endauth
                @unless($isStudio)
                    <a href="#ferramentas" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm text-zinc-100 hover:bg-white/5" @click="closeNav()">Ferramentas</a>
                    <a href="{{ route('legal.show', 'privacidade') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm text-zinc-100 hover:bg-white/5" @click="closeNav()">Privacidade</a>
                    <a href="{{ route('legal.show', 'termos') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm text-zinc-100 hover:bg-white/5" @click="closeNav()">Termos de Uso</a>
                @endunless
            </div>

            @unless($desktop)
                @if(! $isStudio)
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
                @endif

                <p class="mb-2 text-[10px] uppercase tracking-[0.16em] text-zinc-500">Hub</p>
                <div class="grid {{ $isStudio ? 'grid-cols-2' : 'grid-cols-1' }} gap-2">
                    @if($isStudio)
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
                    @endif
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
            @endunless
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
