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
                'tagline' => 'Studio gratuito da família CriaSys',
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
            ],
            'home' => [
                'hero_title' => 'Crie artes para redes — rápido e grátis',
                'hero_blurb' => 'Image Studio da família CriaSys. Formatos prontos, exportação limpa, sem travar seu fluxo.',
                'formats_heading' => 'Comece por um formato',
                'formats_blurb' => 'Escolha um formato e abra direto no Studio.',
                'show_format_shortcuts' => true,
                'show_hub' => true,
                'show_blog_bridge' => true,
                'show_landing_promo' => true,
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
                'eyebrow' => 'Do MarkCraft para o {blog}',
                'headline' => 'Você já tem o editor. No Blog, ele vira operação completa.',
                'paragraph_1' => 'No MarkCraft o caminho é direto: abrir o studio, montar a arte, exportar e limpar. É a porta de entrada da família CriaSys — o mesmo DNA visual que o Image Studio do Blog usa por dentro.',
                'paragraph_2' => 'No <strong>{blog}</strong> você não fica só no arquivo baixado. Cria a conta, ganha <span class="text-zinc-300">/@seu-nome</span>, escreve em blocos, publica, ranqueia com SEO e monetiza com AdSense na lateral e afiliado dentro do post — <strong class="text-zinc-200">100% seus</strong>, sem split de anúncios. A plataforma cobra só pela assinatura do sistema.',
                'paragraph_3' => 'O diferencial está no hub <strong class="text-zinc-200">Ferramentas</strong> do painel: abuse do nosso Image Studio, do encurtador de links, do conversor de imagens, do PDF/HTML e do construtor de Landing Pages — tudo no mesmo lugar, sem WordPress, sem plugin e sem sair da plataforma. Cada módulo alimenta o próximo passo do crescimento.',
                'footnote' => 'Trial Pro Studio sem cartão no cadastro · depois Essencial, Pro Studio ou passe · cartão ou Pix. Depoimentos entram só com feedback real do acesso antecipado.',
                'steps' => [
                    ['label' => '1. Editar', 'text' => 'artes no studio', 'tone' => 'green'],
                    ['label' => '2. Publicar', 'text' => 'blog + capa', 'tone' => 'violet'],
                    ['label' => '3. Monetizar', 'text' => 'LP · ads · afiliado', 'tone' => 'cyan'],
                ],
            ],
            'hub' => [
                'eyebrow' => 'Hub Ferramentas · painel do Blog',
                'headline' => 'Cinco módulos. Um painel. Sem sair da plataforma.',
                'intro' => 'No plano Pro Studio (ou trial/cortesia) o hub reúne Image Studio, Encurtador, Conversor, PDF/HTML e Landing Pages. Fora das abas, o arsenal ainda inclui assistente de texto (IA), AdSense na lateral e bloco de produto/afiliado no post.',
                'modules' => [
                    ['icon' => 'image', 'title' => 'Image Studio', 'text' => 'Canvas no painel: capas, stories, feed, YouTube, TikTok. Layouts, pacotes, tipografia, formas, crop, filtros, export e remoção de fundo (rembg). A ferramenta-estrela — sem Canva externo.', 'wide' => false],
                    ['icon' => 'link', 'title' => 'Encurtador', 'text' => 'URLs curtas no próprio blog (<span class="text-zinc-300">/@seu-blog/l/código</span>), com título, liga/desliga e contador de cliques. Ideal para afiliados, bio e campanhas.', 'wide' => false],
                    ['icon' => 'convert', 'title' => 'Conversor de imagens', 'text' => 'PNG ↔ JPG ↔ WebP (qualidade e largura), favicon 32×32 e gravação direta nas mídias do blog. Sobe, ajusta e usa no post — sem site externo.', 'wide' => false],
                    ['icon' => 'pdf', 'title' => 'PDF / HTML', 'text' => 'HTML → PDF (A4/Carta), PDF → HTML (texto) e exportar artigo do blog em PDF a partir dos blocos. Material de apoio, e-book leve ou backup legível.', 'wide' => false],
                    ['icon' => 'landing', 'title' => 'Landing Pages', 'text' => 'Páginas de captura no tema do blog, com views, cliques no CTA, leads e biblioteca de imagens. Integra com o Image Studio — e no fluxo afiliado gera LP de divulgação do CriaSys.', 'wide' => true],
                ],
                'extras' => [
                    ['title' => 'Assistente de texto (IA)', 'text' => 'melhora títulos, bios e SEO no fluxo (sua chave).'],
                    ['title' => 'AdSense na lateral', 'text' => 'cola o ca-pub; receita 100% do blogueiro.'],
                    ['title' => 'Bloco produto/afiliado', 'text' => 'vitrine dentro do artigo, também 100% sua.'],
                ],
            ],
            'editor' => [
                'headline' => 'Editor de verdade — feito para quem publica',
                'intro' => 'O mesmo tipo de Image Studio que roda no Blog CriaSys Web, disponível grátis aqui para criar, exportar e seguir no ecossistema.',
                'columns' => [
                    ['label' => 'Studio completo', 'text' => 'Layouts de redes, pacotes, tipografia, shapes, export e remoção de fundo no servidor.', 'tone' => 'teal'],
                    ['label' => 'Baixe e limpe', 'text' => 'Edite → baixe PNG/JPG → limpe o workspace. Privacidade por padrão — artes não ficam no site.', 'tone' => 'amber'],
                    ['label' => 'Próximo nível', 'text' => 'Para blog, afiliados e cobrança no mesmo fluxo: {blog}.', 'tone' => 'sky'],
                ],
            ],
            'funnel' => [
                'eyebrow' => 'Família CriaSys',
                'headline' => 'Criou a arte. E o resto do funil?',
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

        return trim((string) ($blog['cta_pending'] ?? 'Página de vendas em breve'));
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
