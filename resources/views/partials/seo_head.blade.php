@php
    use App\Support\SeoMeta;

    $seo = SeoMeta::forPage($seo ?? []);
    $jsonLd = ($seoJsonLd ?? true) && ! str_contains((string) $seo['robots'], 'noindex')
        ? SeoMeta::jsonLdGraphs($seo, $seoIncludeWebSite ?? true)
        : [];
@endphp

<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
@if($seo['keywords'] !== '')
    <meta name="keywords" content="{{ $seo['keywords'] }}">
@endif
<meta name="robots" content="{{ $seo['robots'] }}">
<meta name="author" content="{{ $seo['site_name'] }} · CriaSys">
<meta name="language" content="pt-BR">
<link rel="canonical" href="{{ $seo['canonical'] }}">

<meta property="og:site_name" content="{{ $seo['site_name'] }}">
<meta property="og:locale" content="{{ $seo['og_locale'] }}">
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:image" content="{{ $seo['image'] }}">
<meta property="og:image:alt" content="{{ $seo['site_name'] }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['image'] }}">
@if($seo['twitter_handle'] !== '')
    <meta name="twitter:site" content="{{ $seo['twitter_handle'] }}">
@endif

@if($seo['google_verification'] !== '')
    <meta name="google-site-verification" content="{{ $seo['google_verification'] }}">
@endif
@if($seo['bing_verification'] !== '')
    <meta name="msvalidate.01" content="{{ $seo['bing_verification'] }}">
@endif

@if(count($jsonLd))
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => $jsonLd,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endif
