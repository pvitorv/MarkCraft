{{-- Prova social: mesmo bloco sempre; sem depoimentos publicados = marcador reservado; com itens = cards com print. --}}
@php
    $section = $cms['testimonials'] ?? [];
    $items = \App\Support\Cms::publishedTestimonials();
    $hasLive = count($items) > 0;
    $heading = $section['heading'] ?? 'Depoimentos e prova social';
    $intro = $section['intro'] ?? '';
@endphp

<section
    class="mc-social-proof-slot mx-auto max-w-6xl px-4 py-10"
    id="prova-social"
    aria-labelledby="mc-social-proof-title"
    data-status="{{ $hasLive ? 'live' : 'reserved' }}"
>
    <div class="mc-social-proof-frame rounded-xl border border-dashed border-white/20 bg-white/[0.02] px-5 py-8 sm:px-8 sm:py-10 text-center">
        @unless($hasLive)
            <p class="inline-flex items-center gap-2 rounded-md border border-amber-400/30 bg-amber-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-200">
                <span aria-hidden="true">▣</span> Espaço reservado · prova social
            </p>
        @endunless

        <h2 id="mc-social-proof-title" class="mc-brand mt-4 text-lg sm:text-xl font-bold {{ $hasLive ? 'text-white' : 'text-zinc-300' }}">
            {{ $heading }}
        </h2>

        @if($hasLive && filled($intro))
            <p class="mx-auto mt-2 max-w-lg text-sm text-zinc-400 leading-relaxed">{{ $intro }}</p>
        @elseif(! $hasLive)
            <p class="mx-auto mt-2 max-w-lg text-sm text-zinc-500 leading-relaxed">
                Este bloco fica marcado de propósito. Depois do teste com 10–20 usuários e feedback real,
                entram aqui os primeiros depoimentos — sem inventar números nem quotes.
            </p>
        @endif

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 {{ $hasLive ? '' : 'opacity-40 pointer-events-none select-none' }}" @unless($hasLive) aria-hidden="true" @endunless>
            @if($hasLive)
                @foreach($items as $item)
                    <article class="rounded-lg border border-white/10 bg-zinc-900/40 overflow-hidden text-left flex flex-col h-full">
                        @if(!empty($item['image']))
                            <div class="bg-black/50 border-b border-white/10">
                                <img
                                    src="{{ \App\Support\Cms::mediaUrl($item['image']) }}"
                                    alt="Print de depoimento{{ filled($item['name'] ?? '') ? ' de '.$item['name'] : '' }}"
                                    class="w-full max-h-72 object-contain object-center"
                                    loading="lazy"
                                >
                            </div>
                        @endif
                        <div class="px-4 py-4 flex flex-col flex-1">
                            @if(filled($item['quote'] ?? ''))
                                <p class="text-sm text-zinc-300 leading-relaxed flex-1">“{{ $item['quote'] }}”</p>
                            @endif
                            @if(filled($item['name'] ?? ''))
                                <div class="{{ filled($item['quote'] ?? '') ? 'mt-3 pt-3 border-t border-white/5' : '' }}">
                                    <p class="text-xs font-semibold text-white">{{ $item['name'] }}</p>
                                    @if(!empty($item['role']))
                                        <p class="text-[11px] text-zinc-500 mt-0.5">{{ $item['role'] }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            @else
                <div class="rounded-lg border border-white/10 bg-zinc-900/40 px-3 py-4 h-24"></div>
                <div class="rounded-lg border border-white/10 bg-zinc-900/40 px-3 py-4 h-24"></div>
                <div class="rounded-lg border border-white/10 bg-zinc-900/40 px-3 py-4 h-24"></div>
            @endif
        </div>

        @unless($hasLive)
            <p class="mt-4 text-[11px] text-zinc-600 font-mono">TODO: #prova-social · preencher no CMS com depoimentos reais</p>
        @endunless
    </div>
</section>
