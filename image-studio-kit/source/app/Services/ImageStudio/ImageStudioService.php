<?php

namespace App\Services\ImageStudio;

use App\Models\Blog;

/**
 * Catálogo e utilitários do Image Studio no painel do blog.
 * Sem Project/Thumbnail do CriaSys — só configs locais.
 */
class ImageStudioService
{
    public function __construct(
        protected ?BackgroundRemovalService $backgroundRemoval = null,
    ) {
        $this->backgroundRemoval ??= app(BackgroundRemovalService::class);
    }

    public function catalog(?Blog $blog = null): array
    {
        $groups = config('image_studio.groups', []);
        $presets = collect(config('image_studio.presets', []))
            ->map(fn (array $meta, string $slug) => array_merge($meta, [
                'slug' => $slug,
                'group_label' => $groups[$meta['group'] ?? 'web'] ?? ($meta['group'] ?? 'web'),
            ]))
            ->values();

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
            'templates' => $this->templateCatalog(),
            'packs' => $this->packsCatalog(),
            'pack_categories' => array_values(config('image_studio_packs.categories', [])),
            'brand' => $blog ? $this->brandKit($blog) : null,
            'frames' => [],
            'frame_categories' => [],
            'elements' => collect(config('image_studio_stickers.elements', []))
                ->merge(config('image_studio_shapes.elements', []))
                ->merge(config('image_studio.elements', []))
                ->merge(config('image_studio_icons.elements', []))
                ->values(),
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
     * Layouts de redes / formatos (modal Layouts) — só config/image_studio.php.
     * Pacotes (web/marca/mockups) ficam em packsCatalog() e NÃO entram aqui.
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
     * Templates só dos pacotes (editorial, marca, mockups).
     *
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
     * Cores para miniatura do modal (não precisa renderizar canvas).
     *
     * @param  array<string, mixed>  $template
     * @return array{bg: string, accent: string, ink: string}
     */
    protected function templatePreview(array $template): array
    {
        $bg = (string) ($template['background']['color'] ?? '#171512');
        $accent = '#c4a574';
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
     * Pacotes curados com itens resolvidos (templates + ações especiais).
     *
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
            if (! empty($pack['action_brand_kit'])) {
                $items[] = [
                    'type' => 'brand_kit',
                    'slug' => 'action_brand_kit',
                    'name' => 'Marca do meu blog',
                    'description' => 'Aplica logo, cores e nome do blog no canvas (sem apagar tudo)',
                    'icon' => '◇',
                    'pack' => $categoryId,
                    'preview' => ['bg' => '#171512', 'accent' => '#c4a574', 'ink' => '#f3efe7'],
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
     * Cores, logo e tipografia do blog — kit de marca 1 clique no Studio.
     *
     * @return array{name: string, logo_url: ?string, colors: array<string, string>, font_heading: ?string, font_body: ?string}
     */
    public function brandKit(Blog $blog): array
    {
        $settings = $blog->settings;

        return [
            'name' => (string) $blog->name,
            'logo_url' => filled($blog->logo_path)
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($blog->logo_path)
                : null,
            'colors' => [
                'accent' => (string) ($settings?->color_accent ?: '#7c3aed'),
                'accent_text' => (string) ($settings?->color_accent_text ?: '#ffffff'),
                'surface' => (string) ($settings?->color_surface ?: '#ffffff'),
                'text' => (string) ($settings?->color_text ?: '#111827'),
            ],
            'font_heading' => $settings?->font_heading,
            'font_body' => $settings?->font_body,
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
}
