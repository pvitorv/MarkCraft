{{-- Menu conta: login / usuário + perfil + sair (dark) --}}
@auth
    <div class="relative" x-data="{ accountOpen: false }" @keydown.escape.window="accountOpen = false">
        <button
            type="button"
            class="inline-flex items-center gap-2 rounded-md border border-white/10 bg-white/[0.04] px-2.5 py-1.5 text-sm text-zinc-100 hover:bg-white/[0.08] transition"
            @click="accountOpen = !accountOpen"
            :aria-expanded="accountOpen.toString()"
        >
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-teal-500/20 text-teal-300 text-xs font-bold uppercase">
                {{ mb_substr(Auth::user()->name, 0, 1) }}
            </span>
            <span class="max-w-[9rem] truncate font-medium">{{ Auth::user()->name }}</span>
            <svg class="h-3.5 w-3.5 text-zinc-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>

        <div
            x-show="accountOpen"
            x-cloak
            @click.outside="accountOpen = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="absolute right-0 z-[520] mt-2 w-64 overflow-hidden rounded-xl border border-white/10 bg-[#0c1118]/95 shadow-2xl backdrop-blur-md"
            role="menu"
        >
            <div class="border-b border-white/10 px-3.5 py-3">
                <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                <p class="mt-0.5 text-xs text-zinc-400 truncate">{{ Auth::user()->email }}</p>
            </div>
            <div class="p-1.5 text-sm">
                @unless(!empty($markcraftDesktop))
                    <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-zinc-200 hover:bg-white/5" role="menuitem" @click="accountOpen = false">
                        Página inicial
                    </a>
                @endunless
                <a href="{{ route('studio') }}" class="block rounded-lg px-3 py-2 text-zinc-200 hover:bg-white/5" role="menuitem" @click="accountOpen = false">
                    Studio
                </a>
                @if(Auth::user()->is_admin)
                    <a href="{{ route('admin.cms.index') }}" class="block rounded-lg px-3 py-2 text-amber-200 hover:bg-amber-500/10" role="menuitem" @click="accountOpen = false">
                        Painel CMS
                    </a>
                @endif
                <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2 text-zinc-200 hover:bg-white/5" role="menuitem" @click="accountOpen = false">
                    Perfil · e-mail e senha
                </a>
            </div>
            <div class="border-t border-white/10 p-1.5">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-rose-300 hover:bg-rose-500/10" role="menuitem">
                        Desconectar
                    </button>
                </form>
            </div>
        </div>
    </div>
@else
    <a href="{{ route('login') }}" class="px-2.5 py-1.5 rounded-md hover:bg-white/5 hover:text-white transition">Entrar</a>
    @if(! empty($markcraftAllowsRegister))
        <a href="{{ route('register') }}" class="mc-cta inline-flex rounded-md bg-teal-500 px-3.5 py-1.5 font-semibold text-zinc-950 hover:bg-teal-400">Criar conta</a>
    @endif
@endauth
