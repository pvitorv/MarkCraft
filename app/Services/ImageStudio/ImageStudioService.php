<?php

namespace App\Services\ImageStudio;

use App\Models\User;

/**
 * Catálogo do Image Studio no MarkCraft.
 * Layouts ≠ Pacotes; artes não persistem no servidor.
 */
class ImageStudioService
{
    public function __construct(
        protected ?BackgroundRemovalService $backgroundRemoval = null,
    ) {
        $this->backgroundRemoval ??= app(BackgroundRemovalService::class);
    }

    public function catalog(?User $user = null): array
    {
        $groups = config('image_studio.groups', []);
        $presets = collect(config('image_studio.presets', []))
            ->map(fn (array $meta, string $slug) => array_merge($meta, [
                'slug' => $slug,
                'group_label' => $groups[$meta['group'] ?? 'web'] ?? ($meta['group'] ?? 'web'),
            ]))
            ->values()
            ->all();

        $bg = $this->backgroundRemoval->status();

        return [
            'presets' => $presets,
            'groups' => $groups,
            'export_formats' => config('image_studio.export_formats', []),
            'fonts' => $this->fontCatalog(),
            'font_groups' => config('image_studio_fonts.groups', []),
            'icon_fonts' => config('image_studio_fonts.icon_fonts', []),
            'icon_glyphs' => config('image_studio_fonts.icon_glyphs', []),
            'defaults' => config('image_studio.defaults', []),
            'templates' => $this->templateCatalog()->all(),
            'packs' => $this->packsCatalog(),
            'pack_categories' => array_values(config('image_studio_packs.categories', [])),
            'brand' => $this->markCraftBrandKit(),
            'frames' => [],
            'frame_categories' => [],
            'elements' => collect(config('image_studio_stickers.elements', []))
                ->merge(config('image_studio_shapes.elements', []))
                ->merge(config('image_studio.elements', []))
                ->merge(config('image_studio_icons.elements', []))
                ->values()
                ->all(),
            'element_groups' => array_merge(
                config('image_studio_stickers.groups', []),
                config('image_studio_shapes.groups', []),
                config('image_studio.element_groups', []),
                config('image_studio_icons.groups', [])
            ),
            'background_removal_available' => $bg['available'],
            'background_removal_client' => $bg['client'],
            'background_removal_driver' => $bg['driver'],
            'background_removal_label' => $bg['label'],
            'preset_platform_map' => config('image_studio.preset_platform_map', []),
            'primary_formats' => config('image_studio.primary_formats', []),
            'group_order' => config('image_studio.group_order', []),
        ];
    }

    /**
     * Layouts de redes / formatos — só config/image_studio.php.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    protected function templateCatalog()
    {
        return collect(config('image_studio.templates', []))
            ->map(fn (array $meta, string $slug) => array_merge($meta, [
                'slug' => $slug,
                'preview' => $this->templatePreview(array_merge($meta, ['slug' => $slug])),
            ]))
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<string, array<string, mixed>>
     */
    protected function packTemplateCatalog()
    {
        return collect(config('image_studio_packs.templates', []))
            ->map(fn (array $meta, string $slug) => array_merge($meta, [
                'slug' => $slug,
                'preview' => $this->templatePreview(array_merge($meta, ['slug' => $slug])),
            ]));
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array{bg: string, accent: string, ink: string}
     */
    protected function templatePreview(array $template): array
    {
        $bg = (string) ($template['background']['color'] ?? '#171512');
        $accent = '#0d9488';
        $ink = '#f3efe7';
        $foundAccent = false;

        foreach ($template['objects'] ?? [] as $obj) {
            $kind = (string) ($obj['kind'] ?? '');
            $fill = (string) ($obj['fill'] ?? '');
            $stroke = (string) ($obj['stroke'] ?? '');
            $gradStop = (string) ($obj['gradient']['stops'][0]['color'] ?? '');

            if ($kind === 'text' && $fill !== '' && $fill !== $bg && ! str_starts_with($fill, 'transparent')) {
                $ink = $fill;
            }

            if ($foundAccent) {
                continue;
            }

            foreach ([$stroke, $gradStop, $fill] as $candidate) {
                if ($candidate === '' || $candidate === $bg || str_starts_with($candidate, 'transparent')) {
                    continue;
                }
                if (in_array($kind, ['rect', 'circle', 'ellipse', 'line'], true)) {
                    $accent = $candidate;
                    $foundAccent = true;
                    break;
                }
            }
        }

        return [
            'bg' => $bg,
            'accent' => $accent,
            'ink' => $ink,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function packsCatalog(): array
    {
        $categories = config('image_studio_packs.categories', []);
        $templatesBySlug = $this->packTemplateCatalog();
        $packs = [];

        foreach (config('image_studio_packs.packs', []) as $id => $pack) {
            $categoryId = $pack['category'] ?? $id;
            $category = $categories[$categoryId] ?? [
                'id' => $categoryId,
                'name' => $pack['name'] ?? $id,
                'description' => $pack['description'] ?? '',
                'icon' => '▦',
            ];

            $items = [];

            // MarkCraft: sem Blog — ação de marca vira tip de branding MarkCraft/CriaSys
            if (! empty($pack['action_brand_kit'])) {
                $items[] = [
                    'type' => 'brand_kit',
                    'slug' => 'action_brand_kit',
                    'name' => 'Marca MarkCraft',
                    'description' => 'Aplica cores e tipografia da família CriaSys no canvas',
                    'icon' => '◇',
                    'pack' => $categoryId,
                    'preview' => ['bg' => '#0f1419', 'accent' => '#0d9488', 'ink' => '#f3f0ea'],
                ];
            }

            foreach ($pack['items'] ?? [] as $slug) {
                $template = $templatesBySlug->get($slug);
                if (! $template) {
                    continue;
                }
                $items[] = array_merge($template, [
                    'type' => 'template',
                    'pack' => $categoryId,
                    'preview' => $template['preview'] ?? $this->templatePreview($template),
                ]);
            }

            $packs[] = [
                'id' => $id,
                'category' => $categoryId,
                'name' => $pack['name'] ?? ($category['name'] ?? $id),
                'description' => $pack['description'] ?? ($category['description'] ?? ''),
                'icon' => $category['icon'] ?? '◈',
                'items' => $items,
                'count' => count($items),
            ];
        }

        return $packs;
    }

    /**
     * @return array{name: string, logo_url: null, colors: array<string, string>, font_heading: string, font_body: string}
     */
    public function markCraftBrandKit(): array
    {
        return [
            'name' => 'MarkCraft',
            'logo_url' => null,
            'colors' => [
                'accent' => '#0d9488',
                'accent_text' => '#ffffff',
                'surface' => '#f3f0ea',
                'text' => '#0f1419',
            ],
            'font_heading' => 'Sora',
            'font_body' => 'DM Sans',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fontCatalog(): array
    {
        $groups = config('image_studio_fonts.groups', []);
        $fonts = [];

        foreach (config('image_studio_fonts.google_fonts', []) as $meta) {
            $groupKey = $meta['group'] ?? 'google_sans';
            $fonts[] = array_merge($meta, [
                'source' => 'google',
                'group_key' => $groupKey,
                'group_label' => $groups[$groupKey] ?? $groupKey,
            ]);
        }

        foreach (config('image_studio_fonts.icon_fonts', []) as $meta) {
            $groupKey = $meta['group'] ?? 'icons_fa';
            $fonts[] = array_merge($meta, [
                'source' => 'icon',
                'group_key' => $groupKey,
                'group_label' => $groups[$groupKey] ?? 'Ícones',
            ]);
        }

        return $fonts;
    }

    public function emptyDesign(?string $presetSlug = null): array
    {
        $preset = $presetSlug ?? config('image_studio.defaults.preset', 'ig_feed_square');
        $meta = config('image_studio.presets.'.$preset, config('image_studio.presets.custom', [
            'width' => 1080,
            'height' => 1080,
        ]));

        return [
            'preset' => $preset,
            'width' => $meta['width'] ?? 1080,
            'height' => $meta['height'] ?? 1080,
            'canvas' => null,
            'persisted' => false,
        ];
    }
}
