{{-- Noscript GTM + HTML custom após <body>. Configure em Admin → CMS → Métricas. --}}
@php
    use App\Support\Cms;

    $surface = $analyticsSurface ?? 'landing';
    $a = $cmsAnalytics ?? Cms::analytics();
    $inject = Cms::shouldInjectAnalytics($surface);
    $gtmId = trim((string) ($a['google_tag_manager_id'] ?? ''));
    $bodyHtml = trim((string) ($a['body_html'] ?? ''));
@endphp
@if($inject)
    @if($gtmId !== '')
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id={{ urlencode($gtmId) }}"
                    height="0" width="0" style="display:none;visibility:hidden"
                    title="Google Tag Manager"></iframe>
        </noscript>
    @endif
    @if($bodyHtml !== '')
        {!! $bodyHtml !!}
    @endif
@endif
