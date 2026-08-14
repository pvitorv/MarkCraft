{{-- Recursos & Garantias (#prova-social). Depoimentos reais, se houver, vêm abaixo. --}}
@php
    $home = $cmsHome ?? [];
    $guarantees = $home['guarantees'] ?? [];
    if ($guarantees === []) {
        $guarantees = \App\Support\Cms::defaults()['home']['guarantees'] ?? [];
    }
    $section = $cms['testimonials'] ?? [];
    $items = \App\Support\Cms::publishedTestimonials();
    $hasLive = count($items) > 0;
    $heading = $section['heading'] ?? 'Depoimentos';
    $intro = $section['intro'] ?? '';
@endphp

<section
    class="mc-social-proof-slot mx-auto max-w-6xl px-4 py-10"
    id="prova-social"
    aria-labelledby="mc-guarantees-title"
>
    <div class="rounded-xl border border-white/10 bg-white/[0.02] px-5 py-8 sm:px-8 sm:py-10">
        <p class="text-[10px] uppercase tracking-[0.16em] text-teal-300/85 text-center">Portal gratuito</p>
        <h2 id="mc-guarantees-title" class="mc-brand mt-2 text-center text-lg sm:text-xl font-bold text-white">
            {{ $home['guarantees_heading'] ?? 'Recursos & Garantias' }}
        </h2>
        @if(!empty($home['guarantees_intro']))
            <p class="mx-auto mt-2 max-w-lg text-center text-sm text-zinc-400 leading-relaxed">{{ $home['guarantees_intro'] }}</p>
        @endif

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            @foreach($guarantees as $card)
                <article class="rounded-lg border border-white/10 bg-zinc-900/50 px-4 py-5 text-left">
                    <p class="text-lg" aria-hidden="true">
                        @if(($card['icon'] ?? '') === 'bolt') ⚡
                        @elseif(($card['icon'] ?? '') === 'tools') 🛠️
                        @else 🔒
                        @endif
                    </p>
                    <h3 class="mt-2 text-sm font-semibold text-white">{{ $card['title'] ?? '' }}</h3>
                    <p class="mt-2 text-sm text-zinc-400 leading-relaxed">{{ $card['text'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    </div>

    @if($hasLive)
        <div class="mt-8 rounded-xl border border-white/10 bg-white/[0.02] px-5 py-8 sm:px-8 text-center">
            <h3 class="mc-brand text-lg font-bold text-white">{{ $heading }}</h3>
            @if(filled($intro))
                <p class="mx-auto mt-2 max-w-lg text-sm text-zinc-400 leading-relaxed">{{ $intro }}</p>
            @endif
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
            </div>
        </div>
    @endif
</section>
