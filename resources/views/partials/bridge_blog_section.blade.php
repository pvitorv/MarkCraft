{{-- Mini-copy persuasiva MarkCraft → Blog CriaSys Web (abaixo do hub) --}}
@php
    use App\Support\Cms;

    $blog = $cmsBlog ?? config('markcraft.blog', []);
    $blogName = $blog['name'] ?? 'Blog CriaSys Web';
    $bridge = $cmsLanding['bridge'] ?? Cms::landing('bridge', []);
    $hub = $cmsLanding['hub'] ?? Cms::landing('hub', []);
    $blogReady = Cms::blogCtaReady($blog);
    $url = Cms::blogCtaUrl($blog);
    $registerUrl = Cms::blogRegisterUrl($blog);
    $ctaLabel = Cms::blogCtaLabel($blog);
    $registerCta = Cms::blogRegisterLabel($blog);

    $stepToneClass = static function (string $tone): string {
        return match ($tone) {
            'violet' => 'text-violet-300',
            'cyan' => 'text-cyan-300',
            default => 'text-[#39ff14]/90',
        };
    };
@endphp

<section class="mc-bridge-section mx-auto max-w-6xl px-4 pb-14 pt-2" id="blog-criasys" aria-labelledby="mc-bridge-title">
    <div class="mc-bridge-grid">
        <div class="min-w-0">
            <p class="text-[10px] uppercase tracking-[0.16em] text-violet-300/85">{!! Cms::landingText($bridge['eyebrow'] ?? '', $blogName) !!}</p>
            <h2 id="mc-bridge-title" class="mc-brand mt-2 text-2xl sm:text-3xl font-bold text-white leading-tight">
                {{ $bridge['headline'] ?? '' }}
            </h2>

            <div class="mt-4 space-y-3.5 text-sm sm:text-[0.95rem] text-zinc-400 leading-relaxed max-w-xl">
                @if(!empty($bridge['paragraph_1']))
                    <p>{{ $bridge['paragraph_1'] }}</p>
                @endif
                @if(!empty($bridge['paragraph_2']))
                    <p>{!! Cms::landingText($bridge['paragraph_2'], $blogName) !!}</p>
                @endif
                @if(!empty($bridge['paragraph_3']))
                    <p>{!! Cms::landingText($bridge['paragraph_3'], $blogName) !!}</p>
                @endif
            </div>

            <div class="mt-6 flex flex-col sm:flex-row flex-wrap gap-3">
                @if($blogReady)
                    <a
                        href="{{ $url }}"
                        class="mc-cta mc-cta-blog inline-flex justify-center rounded-md px-5 py-2.5 text-sm font-semibold"
                        target="_blank"
                        rel="noopener"
                    >
                        {{ $ctaLabel }} →
                    </a>
                    <a
                        href="{{ $registerUrl }}"
                        class="inline-flex justify-center rounded-md border border-violet-400/40 bg-violet-500/10 px-5 py-2.5 text-sm font-semibold text-violet-100 hover:bg-violet-500/20 transition"
                        @if(!str_starts_with($registerUrl, '#')) target="_blank" rel="noopener" @endif
                    >
                        {{ $registerCta }}
                    </a>
                @else
                    <span class="inline-flex justify-center rounded-md border border-dashed border-violet-400/35 bg-violet-500/5 px-5 py-2.5 text-sm font-semibold text-violet-200/90">
                        {{ $ctaLabel }}
                    </span>
                @endif
            </div>
            @if(!empty($bridge['footnote']))
                <p class="mt-3 text-[11px] text-zinc-500 max-w-md">{{ $bridge['footnote'] }}</p>
            @endif
        </div>

        <aside class="mc-bridge-art" aria-hidden="true">
            <svg viewBox="0 0 360 300" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto max-w-[340px] mx-auto">
                <defs>
                    <linearGradient id="mcBridgeGlow" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#f43f5e" stop-opacity="0.55"/>
                        <stop offset="55%" stop-color="#a855f7" stop-opacity="0.45"/>
                        <stop offset="100%" stop-color="#22d3ee" stop-opacity="0.35"/>
                    </linearGradient>
                    <linearGradient id="mcBridgeStroke" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#39ff14"/>
                        <stop offset="50%" stop-color="#a855f7"/>
                        <stop offset="100%" stop-color="#22d3ee"/>
                    </linearGradient>
                </defs>
                <ellipse cx="180" cy="150" rx="150" ry="110" fill="url(#mcBridgeGlow)" opacity="0.22"/>
                <path d="M70 78 C120 78, 140 150, 180 150 C220 150, 240 222, 290 222"
                      stroke="url(#mcBridgeStroke)" stroke-width="2.5" stroke-linecap="round" stroke-dasharray="6 7" opacity="0.85"/>
                <g transform="translate(28,36)">
                    <rect x="0" y="0" width="88" height="72" rx="10" fill="#0c1118" stroke="#39ff14" stroke-width="1.5" opacity="0.95"/>
                    <rect x="12" y="12" width="64" height="40" rx="4" fill="#121820" stroke="rgba(57,255,20,0.35)"/>
                    <rect x="18" y="18" width="36" height="5" rx="1.5" fill="#39ff14" opacity="0.75"/>
                    <rect x="18" y="28" width="24" height="3" rx="1" fill="#fff" opacity="0.25"/>
                    <text x="44" y="66" text-anchor="middle" fill="#39ff14" font-size="9" font-family="system-ui,sans-serif" font-weight="700">MarkCraft</text>
                </g>
                <g transform="translate(136,112)">
                    <rect x="0" y="0" width="88" height="72" rx="10" fill="#0c1118" stroke="#a855f7" stroke-width="1.5"/>
                    <path d="M18 16 h52 v40 H18 z" stroke="#c084fc" stroke-width="1.2" fill="rgba(168,85,247,0.12)"/>
                    <path d="M26 28 h36 M26 36 h28 M26 44 h32" stroke="#e9d5ff" stroke-width="1.5" stroke-linecap="round" opacity="0.7"/>
                    <text x="44" y="66" text-anchor="middle" fill="#e9d5ff" font-size="9" font-family="system-ui,sans-serif" font-weight="700">Blog</text>
                </g>
                <g transform="translate(244,184)">
                    <rect x="0" y="0" width="88" height="72" rx="10" fill="#0c1118" stroke="#22d3ee" stroke-width="1.5"/>
                    <circle cx="44" cy="30" r="14" stroke="#67e8f9" stroke-width="1.4" fill="rgba(34,211,238,0.1)"/>
                    <path d="M44 22 v16 M38 30 h12" stroke="#a5f3fc" stroke-width="1.6" stroke-linecap="round"/>
                    <text x="44" y="66" text-anchor="middle" fill="#a5f3fc" font-size="9" font-family="system-ui,sans-serif" font-weight="700">Receita</text>
                </g>
            </svg>
            @if(!empty($bridge['steps']))
                <ol class="mc-bridge-steps mt-3 grid grid-cols-3 gap-2 text-center text-[10px] sm:text-[11px] text-zinc-500">
                    @foreach($bridge['steps'] as $step)
                        <li>
                            <span class="block font-semibold {{ $stepToneClass($step['tone'] ?? 'green') }}">{{ $step['label'] ?? '' }}</span>{{ $step['text'] ?? '' }}
                        </li>
                    @endforeach
                </ol>
            @endif
        </aside>
    </div>

    <div class="mt-10 sm:mt-12">
        @if(!empty($hub['eyebrow']))
            <p class="text-[10px] uppercase tracking-[0.16em] text-zinc-500">{{ $hub['eyebrow'] }}</p>
        @endif
        @if(!empty($hub['headline']))
            <h3 class="mc-brand mt-1.5 text-lg sm:text-xl font-bold text-white">{{ $hub['headline'] }}</h3>
        @endif
        @if(!empty($hub['intro']))
            <p class="mt-2 max-w-2xl text-sm text-zinc-400 leading-relaxed">{{ $hub['intro'] }}</p>
        @endif

        @if(!empty($hub['modules']))
            <p class="mt-8 text-[10px] uppercase tracking-[0.16em] text-zinc-500">Atalhos do hub</p>
            <div class="mt-3">
                @include('partials.tool_shortcut_buttons', ['variant' => 'chip'])
            </div>
            <ul class="mc-bridge-tools mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($hub['modules'] as $mod)
                    <li @class(['mc-bridge-tool', 'sm:col-span-2 lg:col-span-1' => !empty($mod['wide'])])>
                        <span class="mc-bridge-tool-icon" aria-hidden="true">
                            @include('partials.bridge_tool_icon', ['icon' => $mod['icon'] ?? 'image'])
                        </span>
                        <div>
                            @if(!empty($mod['title']))
                                <p class="mc-bridge-tool-title">{{ $mod['title'] }}</p>
                            @endif
                            @if(!empty($mod['text']))
                                <p class="mc-bridge-tool-text">{!! $mod['text'] !!}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        @if(!empty($hub['extras']))
            <div class="mc-bridge-extras mt-5 grid gap-2 sm:grid-cols-3 text-xs sm:text-sm text-zinc-400">
                @foreach($hub['extras'] as $extra)
                    <p>
                        @if(!empty($extra['title']))
                            <span class="text-zinc-200 font-semibold">{{ $extra['title'] }}</span>
                        @endif
                        @if(!empty($extra['text']))
                            — {{ $extra['text'] }}
                        @endif
                    </p>
                @endforeach
            </div>
        @endif
    </div>
</section>
