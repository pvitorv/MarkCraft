@php
    $home = $cmsHome ?? [];
    $p = $cmsHostingPartner ?? \App\Support\Cms::defaults()['hosting_partner'] ?? [];
    $configured = trim((string) ($p['url'] ?? ''));
    $url = 'https://hostoo.io/?ref=8pLhQonM';
    $show = !empty($home['show_hosting_partner']) && !empty($p['enabled']) && $configured !== '' && $configured !== '#';
    $title = 'Precisa de Hospedagem Rápida para Seus Projetos?';
    $blurb = 'Hospede seus sites, sistemas e aplicações com alta velocidade, servidores SSD no Brasil e suporte 24/7.';
    $cta = 'Conhecer Planos Hostoo →';
    $logo = 'https://hostoo.io/images/logo.png';
@endphp
@if($show)
<style>
    .mc-hostoo-card {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: linear-gradient(90deg, #2563eb 0%, #4f46e5 42%, #635bff 72%, #7c3aed 100%);
        box-shadow: 0 20px 40px rgba(49, 46, 129, 0.35);
        font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
    }
    .mc-hostoo-card__logo {
        height: 2rem;
        width: auto;
        max-height: 2rem;
        object-fit: contain;
        object-position: left;
        mix-blend-mode: screen;
    }
    .mc-hostoo-card__wordmark {
        font-size: 1.125rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #fff;
    }
    .mc-hostoo-card__badge {
        display: inline-flex;
        margin-top: 0.75rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.28);
        background: rgba(255, 255, 255, 0.12);
        padding: 0.25rem 0.75rem;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #fff;
    }
    .mc-hostoo-card__title {
        margin-top: 0.75rem;
        font-size: 1.35rem;
        line-height: 1.25;
        font-weight: 700;
        color: #fff;
    }
    .mc-hostoo-card__blurb {
        margin-top: 0.5rem;
        font-size: 0.925rem;
        line-height: 1.55;
        color: #dbeafe;
    }
    .mc-hostoo-card__cta {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        margin-top: 1.15rem;
        border-radius: 0.75rem;
        background: #fff;
        color: #1e3a8a;
        font-size: 0.875rem;
        font-weight: 700;
        padding: 0.65rem 1.35rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
        text-decoration: none;
    }
    .mc-hostoo-card__cta:hover { background: #eff6ff; color: #1e3a8a; }
    @media (min-width: 640px) {
        .mc-hostoo-card { padding: 1.5rem; }
        .mc-hostoo-card__title { font-size: 1.5rem; }
    }
</style>
<article aria-label="Parceiro Hostoo">
    <div class="mc-hostoo-card">
        <div>
            <a href="{{ $url }}" target="_blank" rel="sponsored nofollow" class="inline-flex items-center gap-2">
                <img
                    src="{{ $logo }}"
                    alt="Hostoo"
                    width="160"
                    height="40"
                    class="mc-hostoo-card__logo"
                    onerror="this.remove()"
                >
                <span class="mc-hostoo-card__wordmark">hostoo</span>
            </a>
            <p class="mc-hostoo-card__badge">Parceiro oficial de infraestrutura</p>
            <h3 class="mc-hostoo-card__title">{{ $title }}</h3>
            <p class="mc-hostoo-card__blurb">{{ $blurb }}</p>
        </div>
        <a href="{{ $url }}" target="_blank" rel="sponsored nofollow" class="mc-hostoo-card__cta">
            {{ $cta }}
        </a>
    </div>
</article>
@endif
