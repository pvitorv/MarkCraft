<?php

namespace App\Support;

use App\Models\CmsSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Conteúdo editável do site (home, rodapé, promos, ads, afiliados).
 * DB sobrescreve defaults de config/markcraft.php.
 */
class Cms
{
    public const CACHE_KEY = 'markcraft.cms.all';

    public static function defaults(): array
    {
        return [
            'footer' => [
                'tagline' => 'Crie capas, stories e banners no navegador — privado, sem marca d\'água, com conversor e PDF no mesmo hub.',
                'portfolio_label' => 'Portfólio',
                'portfolio_url' => '',
                'criasysweb_label' => 'CriaSys Web',
                'criasysweb_url' => env('CRIASYS_WEB_URL', 'https://criasysweb.com.br'),
                'socials' => [
                    ['network' => 'instagram', 'label' => 'Instagram', 'url' => ''],
                    ['network' => 'youtube', 'label' => 'YouTube', 'url' => ''],
                    ['network' => 'linkedin', 'label' => 'LinkedIn', 'url' => ''],
                    ['network' => 'x', 'label' => 'X / Twitter', 'url' => ''],
                    ['network' => 'facebook', 'label' => 'Facebook', 'url' => ''],
                    ['network' => 'tiktok', 'label' => 'TikTok', 'url' => ''],
                ],
            ],
            'blog' => config('markcraft.blog', []),
            'promos' => config('markcraft.promos', []),
            'affiliate_packs' => config('markcraft.affiliate_packs', []),
            'packs_hub' => config('markcraft.packs_hub', []),
            'donations' => config('markcraft.donations', []),
            'ads' => [
                'studio_header_a' => [
                    'enabled' => true,
                    'label' => 'Ad faixa A',
                    'mode' => 'placeholder', // placeholder | adsense | html
                    'adsense_client' => env('ADSENSE_CLIENT', ''),
                    'adsense_slot' => env('ADSENSE_SLOT_STUDIO_A', ''),
                    'html' => '',
                ],
                'studio_header_b' => [
                    'enabled' => true,
                    'label' => 'Ad faixa B',
                    'mode' => 'placeholder',
                    'adsense_client' => env('ADSENSE_CLIENT', ''),
                    'adsense_slot' => env('ADSENSE_SLOT_STUDIO_B', ''),
                    'html' => '',
                ],
                'studio_header_c' => [
                    'enabled' => true,
                    'label' => 'Ad faixa C',
                    'mode' => 'placeholder',
                    'adsense_client' => env('ADSENSE_CLIENT', ''),
                    'adsense_slot' => env('ADSENSE_SLOT_STUDIO_C', ''),
                    'html' => '',
                ],
                'landing_mid' => [
                    'enabled' => true,
                    'label' => 'Ad home · meio',
                    'mode' => 'placeholder',
                    'adsense_client' => env('ADSENSE_CLIENT', ''),
                    'adsense_slot' => env('ADSENSE_SLOT_LANDING_MID', ''),
                    'html' => '',
                    'art_heading' => 'Packs e ofertas',
                    'art_blurb' => 'Quando houver link de afiliado, o anúncio entra aqui. Até lá, este espaço é arte do portal.',
                    'art_image' => '',
                ],
                'landing_footer' => [
                    'enabled' => true,
                    'label' => 'Ad home · rodapé',
                    'mode' => 'placeholder',
                    'adsense_client' => env('ADSENSE_CLIENT', ''),
                    'adsense_slot' => env('ADSENSE_SLOT_LANDING_FOOTER', ''),
                    'html' => '',
                    'art_heading' => 'Novidades do MarkCraft',
                    'art_blurb' => 'Ferramentas grátis no navegador. Sem teaser de produto pago neste espaço.',
                    'art_image' => '',
                ],
            ],
            /*
            | Métricas / analytics — IDs e snippets configurados no CMS (sem código).
            | Scripts só entram nas páginas marcadas; admin nunca recebe tracking.
            */
            'analytics' => [
                'enabled' => false,
                'inject_landing' => true,
                'inject_auth' => false,
                'inject_studio' => false,
                'google_analytics_id' => '',
                'google_tag_manager_id' => '',
                'microsoft_clarity_id' => '',
                'meta_pixel_id' => '',
                'plausible_domain' => '',
                'plausible_script_url' => 'https://plausible.io/js/script.js',
                'google_site_verification' => env('SEO_GOOGLE_SITE_VERIFICATION', ''),
                'bing_site_verification' => env('SEO_BING_SITE_VERIFICATION', ''),
                'head_html' => '',
                'body_html' => '',
                'admin_notes' => '',
            ],
            'home' => [
                'hero_eyebrow' => 'Studio de imagem gratuito',
                'hero_title' => 'MarkCraft — Studio de Imagem 100% Gratuito & Privado',
                'hero_blurb' => 'Crie artes para redes sociais, capas e banners com qualidade profissional, sem cadastro, sem marca d\'água e sem complicações.',
                'hero_badges' => 'Sem cartão · 100% grátis · Artes privadas (processadas no navegador)',
                'formats_heading' => 'Comece por um formato',
                'formats_blurb' => 'Escolha um formato e abra direto no Studio.',
                'guarantees_heading' => 'Recursos & Garantias',
                'guarantees_intro' => 'Ferramentas grátis, no navegador, sem truque de assinatura.',
                'guarantees' => [
                    [
                        'icon' => 'lock',
                        'title' => 'Privacidade em Primeiro Lugar',
                        'text' => 'Suas imagens são processadas localmente e não ficam salvas em nossos servidores.',
                    ],
                    [
                        'icon' => 'bolt',
                        'title' => 'Sem Restrições',
                        'text' => 'Exporte quantas imagens, capas ou PDFs precisar em alta resolução sem pagar nada.',
                    ],
                    [
                        'icon' => 'tools',
                        'title' => 'Hub de Utilitários',
                        'text' => 'Converta formatos (PNG/JPG/WebP) e comprima arquivos no mesmo ambiente.',
                    ],
                ],
                'show_format_shortcuts' => true,
                'show_hub' => true,
                'show_blog_bridge' => false,
                'show_landing_promo' => true,
                'show_newsletter' => true,
                'show_hosting_partner' => true,
                'show_landing_ads' => true,
                'show_hero_showcase' => true,
                'showcase' => [
                    'inspire' => [
                        'eyebrow' => 'Inspire-se',
                        'title' => 'Inspire-se: Exemplos de Criação Profissional',
                        'text' => 'De capas de YouTube impactantes a stories de Instagram elegantes, veja o que é possível criar no Studio.',
                        'image' => '',
                    ],
                    'design' => [
                        'eyebrow' => 'Domine o Design',
                        'title' => 'Domine o Design: Dicas Rápidas para Criadores',
                        'text' => 'Hierarquia visual simples, paletas de cores coesas e contraste perfeito para destacar suas publicações.',
                        'image' => '',
                    ],
                ],
            ],
            'newsletter' => [
                'enabled' => true,
                'title' => 'Inscreva-se para Receber Novidades e Packs Gratuitos',
                'description' => 'Fique por dentro das novas ferramentas e receba conteúdos exclusivos de design direto na sua caixa de entrada.',
                'cta' => 'Quero Receber',
                'placeholder' => 'seu.email@exemplo.com',
                'success' => 'Obrigado! Seu e-mail foi cadastrado para os próximos lançamentos.',
                'to_email' => env('NEWSLETTER_TO', 'markcraft@markcraft.criasysweb.com.br'),
            ],
            'hosting_partner' => [
                'enabled' => true,
                'title' => 'Precisa de Hospedagem Rápida para Seus Projetos?',
                'blurb' => 'Hospede seus sites, sistemas e aplicações com alta velocidade, servidores SSD no Brasil e suporte 24/7.',
                'cta' => 'Conhecer Planos Hostoo →',
                'url' => env('HOSTOO_AFFILIATE_URL', 'https://hostoo.io/?ref=8pLhQonM'),
            ],
            'studio' => [
                'show_blog_bridge_btn' => true,
                'show_sidebar_promo' => true,
                'show_header_ads' => true,
                'aside_blog_blurb' => 'Leve esta arte para o Blog CriaSys Web — publicar, afiliados e studio no mesmo fluxo.',
            ],
            /*
            | Depoimentos da home (#prova-social).
            | Sem itens publicados → mantém o bloco “espaço reservado” atual.
            */
            'testimonials' => [
                'section_enabled' => true,
                'heading' => 'Depoimentos e prova social',
                'intro' => 'Feedback real de quem testou o MarkCraft.',
                'items' => [],
            ],
            'landing' => self::landingDefaults(),
        ];
    }

    public static function landingDefaults(): array
    {
        return [
            'bridge' => [
                'eyebrow' => 'Aprenda e evolua · {blog}',
                'headline' => 'Aprenda e evolua com conteúdo gratuito',
                'paragraph_1' => 'O MarkCraft é o studio e o hub de utilitários grátis: edite, converta, encurte links e exporte — no navegador, sem marca d\'água.',
                'paragraph_2' => 'No <strong>{blog}</strong> você encontra tutoriais, dicas de criação e conteúdo da família CriaSys para ir além da arte do dia a dia — sem pressão de “acesso antecipado”.',
                'paragraph_3' => 'Abaixo está a nossa linha de ferramentas gratuitas: Image Studio, encurtador, conversor PNG/JPG/WebP, PDF e compressor. Tudo no mesmo ambiente.',
                'footnote' => 'Conteúdo e ferramentas grátis. Conta no MarkCraft só se você quiser guardar preferências — as artes continuam no seu dispositivo.',
                'steps' => [
                    ['label' => '1. Criar', 'text' => 'artes no studio', 'tone' => 'green'],
                    ['label' => '2. Utilitários', 'text' => 'converter · encurtar', 'tone' => 'violet'],
                    ['label' => '3. Aprender', 'text' => 'conteúdo no Blog', 'tone' => 'cyan'],
                ],
            ],
            'hub' => [
                'eyebrow' => 'Nossa linha',
                'headline' => 'Nossa Linha de Ferramentas Gratuitas',
                'intro' => 'Os mesmos utilitários do hub: Studio, encurtador, conversor e PDF — sem assinatura para usar o básico.',
                'modules' => [
                    ['icon' => 'image', 'title' => 'Image Studio', 'text' => 'Canvas no navegador: capas, stories, feed, YouTube, TikTok. Layouts, tipografia, formas, crop, filtros, export e remoção de fundo.', 'wide' => false],
                    ['icon' => 'link', 'title' => 'Encurtador', 'text' => 'URLs curtas para bio, campanhas e materiais. Sem sair do MarkCraft.', 'wide' => false],
                    ['icon' => 'convert', 'title' => 'Conversor de imagens', 'text' => 'PNG ↔ JPG ↔ WebP com qualidade e largura — no mesmo ambiente do editor.', 'wide' => false],
                    ['icon' => 'pdf', 'title' => 'PDF / HTML', 'text' => 'HTML → PDF e imagens ↔ PDF para material de apoio e backup leve.', 'wide' => false],
                    ['icon' => 'landing', 'title' => 'Compressor', 'text' => 'Reduza peso de imagens e PDFs para publicar mais rápido em redes e sites.', 'wide' => true],
                ],
                'extras' => [
                    ['title' => 'Sem marca d\'água', 'text' => 'exporte PNG/JPG/PDF no plano gratuito.'],
                    ['title' => 'Privado por padrão', 'text' => 'a arte fica no seu navegador.'],
                    ['title' => 'Hub no mesmo site', 'text' => 'converter, encurtar e comprimir sem sair do MarkCraft.'],
                ],
            ],
            'editor' => [
                'headline' => 'Editor de verdade — feito para quem cria',
                'intro' => 'Image Studio gratuito: crie, exporte e use os utilitários no mesmo hub — no navegador, sem marca d\'água.',
                'columns' => [
                    ['label' => 'Studio completo', 'text' => 'Layouts de redes, tipografia, shapes, export e remoção de fundo.', 'tone' => 'teal'],
                    ['label' => 'Baixe e limpe', 'text' => 'Edite → baixe PNG/JPG → limpe o workspace. Privacidade por padrão — artes não ficam no site.', 'tone' => 'amber'],
                    ['label' => 'Aprenda mais', 'text' => 'Tutoriais e conteúdo gratuito no hub e na newsletter.', 'tone' => 'sky'],
                ],
            ],
            'funnel' => [
                'eyebrow' => 'Família CriaSys',
                'headline' => 'Criou a arte. Quer aprender o próximo passo?',
            ],
        ];
    }

    /** Substitui {blog} pelo nome do produto Blog. */
    public static function landingText(string $text, ?string $blogName = null): string
    {
        $blogName = $blogName ?? (string) (self::get('blog.name') ?? config('markcraft.blog.name', 'Blog CriaSys Web'));

        return str_replace('{blog}', e($blogName), $text);
    }

    public static function landing(?string $key = null, mixed $default = null): mixed
    {
        $landing = array_replace_recursive(self::landingDefaults(), (array) self::get('landing', []));

        return $key === null ? $landing : data_get($landing, $key, $default);
    }

    /** Caminho público relativo (/storage/...) para arquivo no disco public. */
    /** Link do Blog CriaSys no card do hero (URL + toggle no CMS). */
    public static function blogCtaReady(?array $blog = null): bool
    {
        $blog = $blog ?? (array) self::get('blog', config('markcraft.blog', []));
        if (empty($blog['cta_ready'])) {
            return false;
        }

        $url = trim((string) ($blog['url'] ?? ''));

        return $url !== '' && $url !== '#';
    }

    public static function blogCtaLabel(?array $blog = null): string
    {
        $blog = $blog ?? (array) self::get('blog', config('markcraft.blog', []));

        if (self::blogCtaReady($blog)) {
            return trim((string) ($blog['cta'] ?? 'Conhecer o Blog CriaSys Web'));
        }

        return trim((string) ($blog['cta_pending'] ?? 'Conteúdo e tutoriais no Blog'));
    }

    public static function blogCtaUrl(?array $blog = null): string
    {
        $blog = $blog ?? (array) self::get('blog', config('markcraft.blog', []));

        if (! self::blogCtaReady($blog)) {
            return '#blog-criasys';
        }

        return trim((string) ($blog['url'] ?? '#')) ?: '#';
    }

    public static function blogRegisterUrl(?array $blog = null): string
    {
        $blog = $blog ?? (array) self::get('blog', config('markcraft.blog', []));
        $url = trim((string) ($blog['register_url'] ?? ''));
        if ($url !== '' && $url !== '#') {
            return $url;
        }
        if (self::blogCtaReady($blog)) {
            return self::blogCtaUrl($blog);
        }

        return '#blog-criasys';
    }

    public static function blogRegisterLabel(?array $blog = null): string
    {
        $blog = $blog ?? (array) self::get('blog', config('markcraft.blog', []));

        return trim((string) ($blog['register_cta'] ?? 'Começar teste grátis'));
    }

    public static function blogStudioButtonUrl(): string
    {
        $blog = (array) self::get('blog', config('markcraft.blog', []));
        $custom = trim((string) ($blog['studio_url'] ?? ''));
        if ($custom !== '') {
            return str_starts_with($custom, '/') ? url($custom) : $custom;
        }

        return route('studio');
    }

    public static function blogMarkcraftRegisterUrl(): string
    {
        $blog = (array) self::get('blog', config('markcraft.blog', []));
        $custom = trim((string) ($blog['markcraft_register_url'] ?? ''));
        if ($custom !== '') {
            return str_starts_with($custom, '/') ? url($custom) : $custom;
        }

        return route('register');
    }

    public static function blogContinueStudioLabel(?array $blog = null): string
    {
        $blog = $blog ?? (array) self::get('blog', config('markcraft.blog', []));

        return trim((string) ($blog['continue_studio_cta'] ?? 'Continuar no Studio'));
    }

    public static function blogCreateAccountLabel(?array $blog = null): string
    {
        $blog = $blog ?? (array) self::get('blog', config('markcraft.blog', []));

        return trim((string) ($blog['create_account_cta'] ?? 'Criar conta no MarkCraft'));
    }

    /** Substitui {min} pelo valor mínimo formatado (doações). */
    public static function donationText(string $text, ?array $donations = null): string
    {
        $donations = $donations ?? (array) self::get('donations', config('markcraft.donations', []));
        $min = number_format((float) ($donations['min_brl'] ?? 2), 2, ',', '.');

        return str_replace('{min}', $min, $text);
    }

    /**
     * Config de métricas (GA4, GTM, Clarity, Pixel, Plausible, HTML custom).
     *
     * @return array<string, mixed>|mixed
     */
    public static function analytics(?string $key = null, mixed $default = null): mixed
    {
        $analytics = array_replace_recursive(
            self::defaults()['analytics'],
            (array) data_get(self::all(), 'analytics', [])
        );

        return $key === null ? $analytics : data_get($analytics, $key, $default);
    }

    /**
     * Injeta scripts só nas superfícies marcadas no CMS.
     * Nunca no painel admin.
     *
     * @param  'landing'|'auth'|'studio'  $surface
     */
    public static function shouldInjectAnalytics(string $surface): bool
    {
        if (request()->routeIs('admin.*')) {
            return false;
        }

        $analytics = self::analytics();
        if (empty($analytics['enabled'])) {
            return false;
        }

        return match ($surface) {
            'landing' => ! empty($analytics['inject_landing']),
            'auth' => ! empty($analytics['inject_auth']),
            'studio' => ! empty($analytics['inject_studio']),
            default => false,
        };
    }

    public static function publicStoragePath(string $storedPath): string
    {
        return '/storage/'.str_replace('\\', '/', ltrim($storedPath, '/'));
    }

    /** Normaliza URL absoluta antiga → /storage/... */
    public static function normalizeStoragePath(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?: $path;
        }

        return str_starts_with($path, '/') ? $path : '/'.$path;
    }

    /**
     * URL de mídia só se o arquivo existir em public/. Caminho quebrado → string vazia (usa CSS).
     */
    public static function existingPublicUrl(?string $path): string
    {
        $path = self::normalizeStoragePath($path);
        if ($path === '' || str_contains($path, '..')) {
            return '';
        }

        $relative = ltrim($path, '/');
        if (! is_file(public_path($relative))) {
            return '';
        }

        return asset($relative);
    }

    /** Caminho público /images/... (relativo ao host). Ignora upload /storage/ — na Hostoo isso não está no public_html. */
    public static function publicArt(?string $configured, string $fallbackRelative): string
    {
        $fallback = self::normalizeStoragePath($fallbackRelative);
        $configured = self::normalizeStoragePath($configured);
        if ($configured !== '' && str_starts_with($configured, '/images/')) {
            return $configured;
        }

        return $fallback;
    }

    /** URL absoluta correta para o host atual (Laragon, produção, etc.). */
    public static function mediaUrl(?string $path): string
    {
        $path = self::normalizeStoragePath($path);
        if ($path === '') {
            return '';
        }

        return asset(ltrim($path, '/'));
    }

    /**
     * Depoimentos salvos, no formato que o editor do painel consome.
     * Sem limite de quantidade — o admin adiciona quantos quiser.
     */
    public static function testimonialItems(): array
    {
        $section = (array) self::get('testimonials', []);
        $items = [];

        foreach (array_values($section['items'] ?? []) as $i => $row) {
            $row = (array) $row;
            $id = (string) ($row['id'] ?? '');
            $imagePath = self::normalizeStoragePath($row['image'] ?? '');

            $items[] = [
                'key' => $id !== '' ? $id : 'row-'.$i,
                'id' => $id,
                'enabled' => ! empty($row['enabled']),
                'name' => (string) ($row['name'] ?? ''),
                'role' => (string) ($row['role'] ?? ''),
                'quote' => (string) ($row['quote'] ?? ''),
                'image' => $imagePath,
                'image_url' => self::mediaUrl($imagePath),
                'preview' => '',
            ];
        }

        return $items;
    }

    /** Depoimentos publicados: ligado + (imagem OU nome com texto). */
    public static function publishedTestimonials(): array
    {
        $section = (array) self::get('testimonials', []);
        if (empty($section['section_enabled'])) {
            return [];
        }

        return collect($section['items'] ?? [])
            ->filter(function ($item) {
                $hasImage = filled($item['image'] ?? null);
                $hasText = filled($item['name'] ?? null) && filled($item['quote'] ?? null);

                return ! empty($item['enabled']) && ($hasImage || $hasText);
            })
            ->map(function ($item) {
                $item = (array) $item;
                $item['image'] = self::normalizeStoragePath($item['image'] ?? '');
                $item['image_url'] = self::mediaUrl($item['image']);

                return $item;
            })
            ->values()
            ->all();
    }

    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            $merged = self::defaults();
            try {
                foreach (CmsSetting::query()->get() as $row) {
                    if (! is_array($row->value)) {
                        continue;
                    }
                    $merged[$row->key] = self::deepMerge(
                        $merged[$row->key] ?? [],
                        $row->value
                    );
                }
            } catch (\Throwable) {
                // Migração ainda não rodou / DB de teste sem tabela → defaults
            }

            return $merged;
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();

        return data_get($all, $key, $default);
    }

    public static function put(string $key, array $value): void
    {
        CmsSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        self::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** Promo slot (compatível com <x-promo-slot>). */
    public static function promo(string $slot): array
    {
        return (array) self::get('promos.'.$slot, config('markcraft.promos.'.$slot, []));
    }

    public static function deepMerge(array $base, array $over): array
    {
        foreach ($over as $k => $v) {
            if (is_array($v) && isset($base[$k]) && is_array($base[$k]) && self::isAssoc($v)) {
                $base[$k] = self::deepMerge($base[$k], $v);
            } else {
                $base[$k] = $v;
            }
        }

        return $base;
    }

    private static function isAssoc(array $arr): bool
    {
        if ($arr === []) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
