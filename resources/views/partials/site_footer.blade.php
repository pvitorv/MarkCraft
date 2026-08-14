@php
    $footer = $cmsFooter ?? [];
    $socials = collect($footer['socials'] ?? [])->filter(fn ($s) => filled($s['url'] ?? null));
    $portfolioUrl = trim((string) ($footer['portfolio_url'] ?? ''));
    $criasysUrl = trim((string) ($footer['criasysweb_url'] ?? ''));
    $tools = config('markcraft.tools', []);
    $tagline = $footer['tagline'] ?? 'Studio de imagem gratuito e hub de ferramentas da família CriaSys';
@endphp

<style>
    .mc-site-footer { border-top: 1px solid rgba(255,255,255,0.06); background: #05070a; color: #9aa6b2; }
    .mc-site-footer a { text-decoration: none; }
    .mc-site-footer a:hover { color: #5eead4; }
    .mc-ft-kicker { font-size: 10px; letter-spacing: 0.16em; text-transform: uppercase; color: #6b7280; font-weight: 600; }
    .mc-ft-link { color: #d4d4d8; }
    .mc-ft-chip {
        display: inline-flex; align-items: center; gap: 0.4rem;
        border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.1);
        padding: 0.4rem 0.7rem; color: #e4e4e7; font-size: 12px;
    }
    .mc-ft-chip:hover { border-color: rgba(45,212,191,0.4); color: #99f6e4; }
    .mc-ft-chip--teal { border-color: rgba(20,184,166,0.35); background: rgba(20,184,166,0.1); color: #ccfbf1; }
</style>

<footer class="mc-site-footer">
    <div class="mx-auto max-w-6xl px-4 py-12 text-sm">
        <div class="grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <p class="mc-brand text-xl text-white">MarkCraft</p>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-teal-300/80">Studio + hub, 100% grátis</p>
                <p class="mt-2 leading-relaxed text-zinc-400">{{ $tagline }}</p>
                <p class="mt-3 text-xs leading-relaxed text-zinc-500">
                    Image Studio no navegador (sem marca d’água), conversor PNG/JPG/WebP, encurtador, PDF e compressor — artes processadas no seu dispositivo.
                    Código aberto sob AGPL-3.0.
                </p>
                <p class="mt-3 text-xs leading-relaxed text-zinc-500">
                    Mantido de forma independente com anúncios, afiliados e doações voluntárias. Obrigado por apoiar o ecossistema.
                </p>
                @if($criasysUrl !== '')
                    <p class="mt-4 text-xs">
                        Família
                        <a href="{{ $criasysUrl }}" target="_blank" rel="noopener" class="text-teal-300 hover:underline">
                            {{ $footer['criasysweb_label'] ?? 'CriaSys Web' }}
                        </a>
                    </p>
                @endif
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('studio') }}" class="mc-ft-chip mc-ft-chip--teal">Abrir o Studio</a>
                    <button type="button" @click="openHub('apoiar')" class="mc-ft-chip">Apoiar o projeto</button>
                </div>
            </div>

            <div class="lg:col-span-8 grid grid-cols-2 sm:grid-cols-4 gap-8">
                <div>
                    <p class="mc-ft-kicker">Navegar</p>
                    <div class="mt-3 flex flex-col gap-2">
                        <a href="{{ route('studio') }}" class="mc-ft-link">Studio</a>
                        <a href="{{ url('/#ferramentas') }}" class="mc-ft-link">Ferramentas</a>
                        <a href="{{ url('/#formatos') }}" class="mc-ft-link">Formatos</a>
                        <a href="{{ url('/#newsletter') }}" class="mc-ft-link">Newsletter</a>
                        <a href="{{ route('legal.show', 'privacidade') }}" class="mc-ft-link">Privacidade</a>
                        <a href="{{ route('legal.show', 'termos') }}" class="mc-ft-link">Termos de Uso</a>
                        <button type="button" @click="openHub('apoiar')" class="mc-ft-link text-left">Apoiar</button>
                        <button type="button" @click="openCredits()" class="mc-ft-link text-left">Créditos</button>
                        @guest
                            <a href="{{ route('register') }}" class="mc-ft-link">Criar conta</a>
                        @else
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.cms.index') }}" class="text-amber-300/90 hover:text-amber-200">CMS</a>
                            @endif
                        @endguest
                    </div>
                </div>

                <div>
                    <p class="mc-ft-kicker">Utilitários</p>
                    <div class="mt-3 flex flex-col gap-2">
                        @foreach($tools as $tool)
                            <a href="{{ url('/#ferramentas') }}" class="mc-ft-link">{{ $tool['short'] ?? $tool['name'] }}</a>
                        @endforeach
                        <p class="mt-3 text-[11px] leading-snug text-zinc-600">Roda no navegador. PSD · PNG · JPG · WebP · SVG · PDF</p>
                    </div>
                </div>

                <div>
                    <p class="mc-ft-kicker">Legal</p>
                    <div class="mt-3 flex flex-col gap-2">
                        @foreach(config('legal.pages', []) as $slug => $meta)
                            <a href="{{ route('legal.show', $slug) }}" class="mc-ft-link">{{ $meta['nav'] ?? $meta['title'] }}</a>
                        @endforeach
                        <a href="https://github.com/pvitorv/MarkCraft" target="_blank" rel="noopener" class="mc-ft-link">Código-fonte</a>
                    </div>
                </div>

                <div>
                    <p class="mc-ft-kicker">CriaSys</p>
                    <div class="mt-3 flex flex-col gap-2">
                        @if($portfolioUrl !== '')
                            <a href="{{ $portfolioUrl }}" target="_blank" rel="noopener" class="mc-ft-chip mc-ft-chip--teal">
                                {{ $footer['portfolio_label'] ?? 'Portfólio' }}
                            </a>
                        @endif
                        @if($criasysUrl !== '')
                            <a href="{{ $criasysUrl }}" target="_blank" rel="noopener" class="mc-ft-chip">
                                {{ $footer['criasysweb_label'] ?? 'CriaSys Web' }}
                            </a>
                        @endif
                        @if($portfolioUrl === '' && $criasysUrl === '')
                            <p class="text-xs text-zinc-500 leading-relaxed">Linha CriaSys: studio gratuito, conteúdo e produtos irmãos.</p>
                        @endif

                        <p class="mc-ft-kicker mt-5">Redes</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse($socials as $social)
                                <a
                                    href="{{ $social['url'] }}"
                                    target="_blank"
                                    rel="noopener me"
                                    class="mc-ft-chip"
                                >{{ $social['label'] ?: ucfirst($social['network'] ?? 'Link') }}</a>
                            @empty
                                <a href="https://github.com/pvitorv/MarkCraft" target="_blank" rel="noopener" class="mc-ft-chip">GitHub</a>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t border-white/5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-[11px] text-zinc-600">
            <p>© {{ date('Y') }} CriaSys Web e MarkCraft. Todos os direitos reservados.</p>
            <p class="flex flex-wrap gap-x-3 gap-y-1">
                <a href="{{ route('legal.show', 'privacidade') }}" class="hover:text-teal-300 transition">Privacidade</a>
                <a href="{{ route('legal.show', 'termos') }}" class="hover:text-teal-300 transition">Termos</a>
                <a href="{{ route('legal.show', 'cookies') }}" class="hover:text-teal-300 transition">Cookies</a>
                <a href="{{ route('legal.show', 'uso-aceitavel') }}" class="hover:text-teal-300 transition">Uso aceitável</a>
                <a href="https://github.com/pvitorv/MarkCraft" target="_blank" rel="noopener" class="hover:text-teal-300 transition">Código-fonte</a>
            </p>
        </div>
    </div>
</footer>
