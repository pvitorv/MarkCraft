<?php

namespace App\Support;

class SeoMeta
{
    /**
     * @param  array{
     *   title?: string,
     *   description?: string,
     *   keywords?: string,
     *   canonical?: string|null,
     *   image?: string|null,
     *   type?: string,
     *   robots?: string,
     *   noindex?: bool,
     * }  $overrides
     * @return array<string, mixed>
     */
    public static function forPage(array $overrides = []): array
    {
        $siteName = (string) config('seo.site_name', config('app.name', 'MarkCraft'));
        $title = (string) ($overrides['title'] ?? config('seo.default_title'));
        $description = (string) ($overrides['description'] ?? config('seo.default_description'));
        $keywords = (string) ($overrides['keywords'] ?? config('seo.default_keywords'));
        $type = (string) ($overrides['type'] ?? 'website');
        $noindex = (bool) ($overrides['noindex'] ?? false);
        $robots = (string) ($overrides['robots'] ?? ($noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'));

        $canonical = $overrides['canonical'] ?? url()->current();
        if (is_string($canonical) && $canonical !== '' && ! str_starts_with($canonical, 'http')) {
            $canonical = url($canonical);
        }

        $imagePath = $overrides['image'] ?? config('seo.og_image');
        $image = self::absoluteUrl(is_string($imagePath) ? $imagePath : '/brand/icon-512.png');

        $cmsAnalytics = Cms::analytics();
        $googleVerification = trim((string) ($cmsAnalytics['google_site_verification'] ?? ''));
        if ($googleVerification === '') {
            $googleVerification = trim((string) config('seo.google_site_verification', ''));
        }
        $bingVerification = trim((string) ($cmsAnalytics['bing_site_verification'] ?? ''));
        if ($bingVerification === '') {
            $bingVerification = trim((string) config('seo.bing_site_verification', ''));
        }

        return [
            'site_name' => $siteName,
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $canonical,
            'image' => $image,
            'type' => $type,
            'robots' => $robots,
            'locale' => (string) config('seo.locale', 'pt_BR'),
            'og_locale' => (string) config('seo.og_locale', 'pt_BR'),
            'twitter_handle' => (string) config('seo.twitter_handle', ''),
            'google_verification' => $googleVerification,
            'bing_verification' => $bingVerification,
            'app_url' => rtrim((string) config('app.url'), '/'),
        ];
    }

    public static function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    /**
     * @param  array<string, mixed>  $seo
     * @return array<int, array<string, mixed>>
     */
    public static function jsonLdGraphs(array $seo, bool $includeWebSite = true): array
    {
        $orgUrl = $seo['app_url'] ?: url('/');
        $sameAs = config('seo.organization.same_as', []);

        $graphs = [
            [
                '@type' => 'Organization',
                '@id' => $orgUrl.'/#organization',
                'name' => config('seo.organization.name', 'MarkCraft'),
                'legalName' => config('seo.organization.legal_name', 'CriaSys'),
                'url' => $orgUrl,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => self::absoluteUrl('/brand/icon-512.png'),
                ],
                'sameAs' => array_values(array_filter($sameAs)),
            ],
        ];

        if ($includeWebSite) {
            $graphs[] = [
                '@type' => 'WebSite',
                '@id' => $orgUrl.'/#website',
                'url' => $orgUrl,
                'name' => $seo['site_name'],
                'description' => $seo['description'],
                'publisher' => ['@id' => $orgUrl.'/#organization'],
                'inLanguage' => 'pt-BR',
            ];

            $graphs[] = [
                '@type' => 'SoftwareApplication',
                'name' => 'MarkCraft',
                'applicationCategory' => 'DesignApplication',
                'operatingSystem' => 'Web',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'BRL',
                ],
                'url' => $orgUrl,
                'description' => $seo['description'],
                'image' => $seo['image'],
                'publisher' => ['@id' => $orgUrl.'/#organization'],
            ];
        }

        return $graphs;
    }
}
