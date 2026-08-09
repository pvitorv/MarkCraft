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
        ];
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
