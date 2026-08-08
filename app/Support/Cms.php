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
                    'label' => 'Ad header A',
                    'mode' => 'placeholder', // placeholder | adsense | html
                    'adsense_client' => env('ADSENSE_CLIENT', ''),
                    'adsense_slot' => env('ADSENSE_SLOT_STUDIO_A', ''),
                    'html' => '',
                ],
                'studio_header_b' => [
                    'enabled' => true,
                    'label' => 'Ad header B',
                    'mode' => 'placeholder',
                    'adsense_client' => env('ADSENSE_CLIENT', ''),
                    'adsense_slot' => env('ADSENSE_SLOT_STUDIO_B', ''),
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
        ];
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
