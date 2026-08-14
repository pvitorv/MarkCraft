@php
    $nl = $cmsNewsletter ?? \App\Support\Cms::defaults()['newsletter'];
    $home = $cmsHome ?? [];
    $show = !empty($home['show_newsletter']) && !empty($nl['enabled']);
    $thanks = $nl['success'] ?? 'Obrigado! Em breve enviaremos as novidades para o seu e-mail.';
@endphp
@if($show)
<section class="mx-auto max-w-6xl px-4 pb-10" id="newsletter" aria-labelledby="mc-nl-title">
    <div
        class="rounded-xl border border-teal-500/25 bg-gradient-to-br from-teal-500/10 via-white/[0.03] to-amber-500/5 px-5 py-8 sm:px-8"
        x-data="{ sent: false, msg: '' }"
    >
        <h2 id="mc-nl-title" class="mc-brand text-xl sm:text-2xl font-bold text-white max-w-2xl leading-tight">
            {{ $nl['title'] ?? '' }}
        </h2>
        <p class="mt-2 max-w-xl text-sm text-zinc-400 leading-relaxed">{{ $nl['description'] ?? '' }}</p>

        <form
            method="POST"
            action="{{ route('newsletter.store') }}"
            class="mt-6 flex flex-col sm:flex-row gap-3 max-w-xl"
            @submit.prevent="
                fetch($el.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData($el)
                }).then(async (r) => {
                    const d = await r.json().catch(() => ({}));
                    sent = true;
                    msg = d.message || @js($thanks);
                }).catch(() => { sent = true; msg = @js($thanks); })
            "
        >
            @csrf
            <label class="sr-only" for="newsletter-email">E-mail</label>
            <input
                id="newsletter-email"
                type="email"
                name="email"
                required
                autocomplete="email"
                class="flex-1 rounded-md border border-white/15 bg-black/40 px-4 py-3 text-sm text-white placeholder:text-zinc-500"
                placeholder="{{ $nl['placeholder'] ?? 'seu.email@exemplo.com' }}"
            >
            <button type="submit" class="mc-cta-primary inline-flex justify-center rounded-md px-5 py-3 text-sm font-semibold">
                {{ $nl['cta'] ?? 'Quero Receber' }}
            </button>
        </form>
        <p class="mt-3 text-sm text-teal-200" x-show="sent" x-cloak x-text="msg"></p>
    </div>
</section>
@endif
