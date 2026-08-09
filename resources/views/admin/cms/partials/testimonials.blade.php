@php
    $testimonials = $cms['testimonials'] ?? [];
    $rows = $testimonialFormRows ?? \App\Support\Cms::testimonialItems();
    if ($rows === []) {
        $rows = [['id' => '', 'enabled' => true, 'name' => '', 'role' => '', 'quote' => '', 'image' => '', 'image_url' => '']];
    }
    $published = \App\Support\Cms::publishedTestimonials();
    $previewHasLive = count($published) > 0;
    $previewHeading = $testimonials['heading'] ?? 'Depoimentos e prova social';
    $previewIntro = $testimonials['intro'] ?? '';
@endphp

<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
    <div class="space-y-4">
        <div class="cms-card border-teal-500/25 bg-teal-500/5">
            <p class="text-sm font-semibold text-teal-100">Como publicar</p>
            <ol class="mt-2 space-y-1 text-sm text-zinc-300 list-decimal list-inside">
                <li>Escolha a <strong class="text-white">imagem</strong> (obrigatório para publicar só com print)</li>
                <li>Marque <strong class="text-white">Publicar na home</strong></li>
                <li>Clique <strong class="text-white">Salvar depoimentos</strong></li>
            </ol>
        </div>

        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.cms.testimonials.add-row') }}">
                @csrf
                <button type="submit" class="cms-btn">+ Adicionar depoimento</button>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.cms.update') }}" enctype="multipart/form-data" class="cms-card space-y-6">
            @csrf
            <input type="hidden" name="section" value="testimonials">

            <div>
                <h1 class="mc-brand text-lg font-bold text-white">Depoimentos da home</h1>
                <p class="cms-help mt-1">{{ count($rows) }} bloco(s) no editor · formulário HTML simples (sem JavaScript)</p>
            </div>

            <input type="hidden" name="section_enabled" value="0">
            <label class="flex items-center gap-2 rounded-lg border border-white/10 px-3 py-2.5 text-sm text-zinc-300">
                <input type="checkbox" name="section_enabled" value="1" @checked(($testimonials['section_enabled'] ?? true))>
                Seção ligada na home
            </label>

            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="cms-label">Título na home</label>
                    <input class="cms-input" name="heading" value="{{ $previewHeading }}">
                </div>
                <div>
                    <label class="cms-label">Subtítulo (opcional)</label>
                    <input class="cms-input" name="intro" value="{{ $previewIntro }}">
                </div>
            </div>

            <div class="space-y-5">
                @foreach($rows as $i => $row)
                    @php
                        $imageUrl = $row['image_url'] ?? \App\Support\Cms::mediaUrl($row['image'] ?? '');
                        $isLive = ! empty($row['enabled']) && (
                            filled($row['image'] ?? '') ||
                            (filled($row['name'] ?? '') && filled($row['quote'] ?? ''))
                        );
                    @endphp
                    <fieldset
                        data-testimonial-card
                        class="rounded-xl border p-4 sm:p-5 space-y-4 {{ $isLive ? 'border-teal-500/40 bg-teal-500/5' : 'border-white/10 bg-black/20' }}"
                    >
                        <legend class="px-2 text-sm font-bold text-white">
                            Depoimento {{ $i + 1 }}
                            @if($isLive)
                                <span class="ml-2 text-[10px] uppercase text-teal-300">· na home</span>
                            @endif
                        </legend>

                        <input type="hidden" name="items[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                        <input type="hidden" name="items[{{ $i }}][image]" value="{{ $row['image'] ?? '' }}">

                        <div>
                            <label class="cms-label">Imagem (print / screenshot / qualquer imagem)</label>
                            <div class="rounded-xl border border-dashed border-white/20 bg-black/40 px-4 py-4 text-center">
                                <img
                                    data-testimonial-preview
                                    src="{{ $imageUrl }}"
                                    alt="Prévia depoimento {{ $i + 1 }}"
                                    class="mx-auto w-full max-h-80 rounded-md object-contain border border-white/10 bg-black/60 {{ $imageUrl ? '' : 'hidden' }}"
                                >
                                <p data-testimonial-empty class="text-sm text-zinc-500 py-4 {{ $imageUrl ? 'hidden' : '' }}">Nenhuma imagem ainda</p>
                                <input
                                    class="cms-input mt-3 text-sm"
                                    type="file"
                                    name="items[{{ $i }}][image_file]"
                                    accept="image/*"
                                    onchange="window.mcPreviewTestimonialImage(this)"
                                >
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="cms-label">Nome (opcional)</label>
                                <input class="cms-input" name="items[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}">
                            </div>
                            <div>
                                <label class="cms-label">Cargo / contexto</label>
                                <input class="cms-input" name="items[{{ $i }}][role]" value="{{ $row['role'] ?? '' }}">
                            </div>
                        </div>
                        <div>
                            <label class="cms-label">Texto (opcional)</label>
                            <textarea class="cms-input" rows="3" name="items[{{ $i }}][quote]">{{ $row['quote'] ?? '' }}</textarea>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label class="inline-flex items-center gap-2 text-sm text-zinc-200">
                                <input type="hidden" name="items[{{ $i }}][enabled]" value="0">
                                <input type="checkbox" name="items[{{ $i }}][enabled]" value="1" @checked(! empty($row['enabled']))>
                                <strong class="text-white">Publicar na home</strong>
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-rose-300">
                                <input type="checkbox" name="items[{{ $i }}][remove]" value="1">
                                Remover ao salvar
                            </label>
                        </div>
                    </fieldset>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-3 pt-3 border-t border-white/10">
                <button type="submit" class="cms-btn text-base px-6 py-3">Salvar depoimentos</button>
                <a href="{{ route('home') }}#prova-social" target="_blank" class="cms-btn-ghost">Ver na home ↗</a>
            </div>
        </form>
    </div>

    <aside class="cms-card h-fit xl:sticky xl:top-24 space-y-3">
        <p class="text-[10px] uppercase tracking-[0.14em] text-zinc-500">Na home agora</p>
        @if($previewHasLive)
            <p class="text-xs text-teal-300">{{ count($published) }} publicado(s)</p>
            @foreach(array_slice($published, 0, 3) as $item)
                @if(!empty($item['image']))
                    <img src="{{ \App\Support\Cms::mediaUrl($item['image']) }}" alt="" class="w-full rounded border border-white/10 object-contain bg-black/50 max-h-32">
                @endif
            @endforeach
        @else
            <p class="text-xs text-amber-200/80">Nenhum publicado. Salve com imagem + Publicar marcado.</p>
        @endif
    </aside>
</div>

<script>
    window.mcPreviewTestimonialImage = function (input) {
        const card = input.closest('[data-testimonial-card]');
        if (!card) return;
        const img = card.querySelector('[data-testimonial-preview]');
        const empty = card.querySelector('[data-testimonial-empty]');
        const file = input.files && input.files[0];
        if (!file) return;
        img.src = URL.createObjectURL(file);
        img.classList.remove('hidden');
        if (empty) empty.classList.add('hidden');
    };
</script>
