@php
    $footer = $cmsFooter ?? [];
    $blog = $cmsBlog ?? config('markcraft.blog');
    $socials = collect($footer['socials'] ?? [])->filter(fn ($s) => filled($s['url'] ?? null));
    $portfolioUrl = trim((string) ($footer['portfolio_url'] ?? ''));
    $criasysUrl = trim((string) ($footer['criasysweb_url'] ?? ($blog['url'] ?? '')));
@endphp

<footer class="border-t border-white/5 bg-[#05070a] text-zinc-400">
    <div class="mx-auto max-w-6xl px-4 py-10 text-sm">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-md">
                <p class="mc-brand text-lg text-white">MarkCraft</p>
                <p class="mt-2">{{ $footer['tagline'] ?? 'Studio gratuito da família CriaSys' }}</p>
                @if($criasysUrl !== '')
                    <p class="mt-2">
                        Site pai:
                        <a href="{{ $criasysUrl }}" target="_blank" rel="noopener" class="text-teal-300 hover:underline">
                            {{ $footer['criasysweb_label'] ?? 'CriaSys Web' }}
                        </a>
                    </p>
                @endif
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.14em] text-zinc-500">Navegar</p>
                    <div class="mt-2 flex flex-col gap-2">
                        <a href="{{ $blog['url'] ?? '#' }}" target="_blank" rel="noopener" class="hover:text-teal-300 transition">{{ $blog['name'] ?? 'Blog CriaSys Web' }}</a>
                        <button type="button" @click="openHub('packs')" class="text-left hover:text-teal-300 transition">Packs CriaSys</button>
                        <a href="#ferramentas" class="hover:text-teal-300 transition">Ferramentas</a>
                        <button type="button" @click="openHub('apoiar')" class="text-left hover:text-teal-300 transition">Apoiar</button>
                        <button type="button" @click="openCredits()" class="text-left hover:text-teal-300 transition">Créditos</button>
                        @guest
                            <a href="{{ route('register') }}" class="hover:text-teal-300 transition">Criar conta</a>
                        @else
                            <a href="{{ route('studio') }}" class="hover:text-teal-300 transition">Studio</a>
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.cms.index') }}" class="text-amber-300/90 hover:text-amber-200 transition">CMS</a>
                            @endif
                        @endguest
                    </div>
                </div>

                <div>
                    <p class="text-[10px] uppercase tracking-[0.14em] text-zinc-500">Legal</p>
                    <div class="mt-2 flex flex-col gap-2">
                        @foreach(config('legal.pages', []) as $slug => $meta)
                            <a href="{{ route('legal.show', $slug) }}" class="hover:text-teal-300 transition">{{ $meta['nav'] ?? $meta['title'] }}</a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="text-[10px] uppercase tracking-[0.14em] text-zinc-500">CriaSys</p>
                    <div class="mt-2 flex flex-col gap-2">
                        @if($portfolioUrl !== '')
                            <a href="{{ $portfolioUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-md border border-teal-500/30 bg-teal-500/10 px-3 py-2 text-teal-100 hover:bg-teal-500/20 transition">
                                {{ $footer['portfolio_label'] ?? 'Portfólio' }}
                            </a>
                        @else
                            <span class="text-zinc-600 text-xs">Portfólio (configure no CMS)</span>
                        @endif
                        @if($criasysUrl !== '')
                            <a href="{{ $criasysUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-md border border-white/10 px-3 py-2 text-zinc-200 hover:bg-white/5 transition">
                                {{ $footer['criasysweb_label'] ?? 'CriaSys Web' }}
                            </a>
                        @endif
                    </div>
                </div>

                <div>
                    <p class="text-[10px] uppercase tracking-[0.14em] text-zinc-500">Redes</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse($socials as $social)
                            <a
                                href="{{ $social['url'] }}"
                                target="_blank"
                                rel="noopener me"
                                class="rounded-md border border-white/10 px-2.5 py-1.5 text-xs text-zinc-300 hover:border-teal-500/40 hover:text-teal-200 transition"
                            >{{ $social['label'] ?: ucfirst($social['network'] ?? 'Link') }}</a>
                        @empty
                            <span class="text-zinc-600 text-xs">Links sociais no painel CMS</span>
                        @endforelse
                    </div>
                    <p class="mt-4 text-[11px] text-zinc-600">PSD · PNG · JPG · WebP · SVG · PDF</p>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-white/5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-[11px] text-zinc-600">
            <p>© {{ date('Y') }} CriaSys Web e MarkCraft. Todos os direitos reservados.</p>
            <p class="flex flex-wrap gap-x-3 gap-y-1">
                <a href="{{ route('legal.show', 'privacidade') }}" class="hover:text-teal-300 transition">Privacidade</a>
                <a href="{{ route('legal.show', 'termos') }}" class="hover:text-teal-300 transition">Termos</a>
                <a href="{{ route('legal.show', 'cookies') }}" class="hover:text-teal-300 transition">Cookies</a>
                <a href="{{ route('legal.show', 'uso-aceitavel') }}" class="hover:text-teal-300 transition">Uso aceitável</a>
            </p>
        </div>
    </div>
</footer>
