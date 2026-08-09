@php
    $landing = $cms['landing'] ?? \App\Support\Cms::landing();
    $bridge = $landing['bridge'] ?? [];
    $hub = $landing['hub'] ?? [];
    $editor = $landing['editor'] ?? [];
    $funnel = $landing['funnel'] ?? [];
@endphp

<div class="space-y-4">
    <div class="cms-card space-y-4">
        <div>
            <h1 class="mc-brand text-lg font-bold text-white">Landing Blog</h1>
            <p class="cms-help">Textos da home sobre o Blog CriaSys. <strong>Botões e URLs</strong> ficam no bloco verde abaixo — um lugar só.</p>
            <p class="cms-help mt-1"><a href="{{ route('home') }}#blog-criasys" target="_blank" class="text-teal-300 underline">Prévia na home ↗</a></p>
        </div>

        <form method="POST" action="{{ route('admin.cms.update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="section" value="landing">

            @include('admin.cms.partials.blog_buttons_links')

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="cms-btn">Salvar links dos botões</button>
                <p class="cms-help self-center mb-0">Salva só o bloco verde acima, ou use o botão no final para salvar tudo de uma vez.</p>
            </div>

            <div class="rounded-lg border border-violet-400/30 bg-violet-500/10 p-4 space-y-3">
                <h2 class="text-sm font-bold text-violet-100">Ponte — Do MarkCraft para o Blog</h2>
                <div>
                    <label class="cms-label">Eyebrow (<code class="text-zinc-500">{blog}</code> = nome do produto)</label>
                    <input class="cms-input" name="bridge_eyebrow" value="{{ $bridge['eyebrow'] ?? '' }}">
                </div>
                <div>
                    <label class="cms-label">Título principal</label>
                    <input class="cms-input" name="bridge_headline" value="{{ $bridge['headline'] ?? '' }}">
                </div>
                @foreach(['paragraph_1' => 'Parágrafo 1', 'paragraph_2' => 'Parágrafo 2 (HTML ok)', 'paragraph_3' => 'Parágrafo 3 (HTML ok)'] as $field => $label)
                    <div>
                        <label class="cms-label">{{ $label }}</label>
                        <textarea class="cms-input" name="bridge_{{ $field }}" rows="3">{{ $bridge[$field] ?? '' }}</textarea>
                    </div>
                @endforeach
                <div>
                    <label class="cms-label">Nota de rodapé (trial / planos)</label>
                    <textarea class="cms-input" name="bridge_footnote" rows="2">{{ $bridge['footnote'] ?? '' }}</textarea>
                </div>
                <div class="space-y-2">
                    <p class="cms-label mb-0">Passos ao lado da ilustração (1–3)</p>
                    @foreach($bridge['steps'] ?? [] as $i => $step)
                        <div class="grid sm:grid-cols-2 gap-2 rounded-lg border border-white/10 p-3">
                            <input class="cms-input" name="bridge_steps[{{ $i }}][label]" value="{{ $step['label'] ?? '' }}" placeholder="1. Editar">
                            <input class="cms-input" name="bridge_steps[{{ $i }}][text]" value="{{ $step['text'] ?? '' }}" placeholder="artes no studio">
                            <input type="hidden" name="bridge_steps[{{ $i }}][tone]" value="{{ $step['tone'] ?? 'green' }}">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-white/10 p-4 space-y-3">
                <h2 class="text-sm font-bold text-white">Hub — Cinco módulos do painel</h2>
                <input class="cms-input" name="hub_eyebrow" value="{{ $hub['eyebrow'] ?? '' }}">
                <input class="cms-input" name="hub_headline" value="{{ $hub['headline'] ?? '' }}">
                <textarea class="cms-input" name="hub_intro" rows="3">{{ $hub['intro'] ?? '' }}</textarea>
                @foreach($hub['modules'] ?? [] as $i => $mod)
                    <div class="rounded-lg border border-white/10 p-3 space-y-2">
                        <p class="text-xs font-semibold text-zinc-400">Módulo {{ $i + 1 }}</p>
                        <div class="grid sm:grid-cols-[8rem_1fr] gap-2">
                            <select class="cms-input" name="hub_modules[{{ $i }}][icon]">
                                @foreach(['image' => 'Image', 'link' => 'Link', 'convert' => 'Conversor', 'pdf' => 'PDF', 'landing' => 'Landing'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(($mod['icon'] ?? '') === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            <input class="cms-input" name="hub_modules[{{ $i }}][title]" value="{{ $mod['title'] ?? '' }}">
                        </div>
                        <textarea class="cms-input" name="hub_modules[{{ $i }}][text]" rows="2">{{ $mod['text'] ?? '' }}</textarea>
                        <label class="inline-flex items-center gap-2 text-xs text-zinc-400">
                            <input type="checkbox" name="hub_modules[{{ $i }}][wide]" value="1" @checked(!empty($mod['wide']))> Coluna larga (Landing Pages)
                        </label>
                    </div>
                @endforeach
                @foreach($hub['extras'] ?? [] as $i => $extra)
                    <div class="grid sm:grid-cols-2 gap-2">
                        <input class="cms-input" name="hub_extras[{{ $i }}][title]" value="{{ $extra['title'] ?? '' }}">
                        <input class="cms-input" name="hub_extras[{{ $i }}][text]" value="{{ $extra['text'] ?? '' }}">
                    </div>
                @endforeach
            </div>

            <div class="rounded-lg border border-white/10 p-4 space-y-3">
                <h2 class="text-sm font-bold text-white">Editor de verdade (3 colunas)</h2>
                <input class="cms-input" name="editor_headline" value="{{ $editor['headline'] ?? '' }}">
                <textarea class="cms-input" name="editor_intro" rows="2">{{ $editor['intro'] ?? '' }}</textarea>
                @foreach($editor['columns'] ?? [] as $i => $col)
                    <div class="rounded-lg border border-white/10 p-3 space-y-2">
                        <input class="cms-input" name="editor_columns[{{ $i }}][label]" value="{{ $col['label'] ?? '' }}">
                        <textarea class="cms-input" name="editor_columns[{{ $i }}][text]" rows="2">{{ $col['text'] ?? '' }}</textarea>
                        <input type="hidden" name="editor_columns[{{ $i }}][tone]" value="{{ $col['tone'] ?? 'teal' }}">
                    </div>
                @endforeach
            </div>

            <div class="rounded-lg border border-amber-400/30 bg-amber-500/10 p-4 space-y-3">
                <h2 class="text-sm font-bold text-amber-100">Funil — card “Família CriaSys”</h2>
                <p class="cms-help text-amber-100/70">Parágrafo do card = <strong>Blurb</strong> na aba Blog CriaSys. Botões = bloco verde acima.</p>
                <input class="cms-input" name="funnel_eyebrow" value="{{ $funnel['eyebrow'] ?? '' }}">
                <input class="cms-input" name="funnel_headline" value="{{ $funnel['headline'] ?? '' }}">
            </div>

            <button type="submit" class="cms-btn">Salvar landing + links dos botões</button>
        </form>
    </div>
</div>
