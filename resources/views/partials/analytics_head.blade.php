{{-- Scripts de métricas (head). Configure em Admin → CMS → Métricas. --}}
@php
    use App\Support\Cms;

    $surface = $analyticsSurface ?? 'landing';
    $a = $cmsAnalytics ?? Cms::analytics();
    $inject = Cms::shouldInjectAnalytics($surface);

    $gtmId = trim((string) ($a['google_tag_manager_id'] ?? ''));
    $gaId = trim((string) ($a['google_analytics_id'] ?? ''));
    $clarityId = trim((string) ($a['microsoft_clarity_id'] ?? ''));
    $pixelId = trim((string) ($a['meta_pixel_id'] ?? ''));
    $plausibleDomain = trim((string) ($a['plausible_domain'] ?? ''));
    $plausibleSrc = trim((string) ($a['plausible_script_url'] ?? 'https://plausible.io/js/script.js'))
        ?: 'https://plausible.io/js/script.js';
    $headHtml = trim((string) ($a['head_html'] ?? ''));

    // Com GTM ativo, o GA4 deve ir pelo container (evita contagem dupla).
    $useDirectGa = $gaId !== '' && $gtmId === '';
@endphp
@if($inject)
    @if($gtmId !== '')
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
            var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
            j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
            f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer',@json($gtmId));
        </script>
    @endif

    @if($useDirectGa)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gaId) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @json($gaId));
        </script>
    @endif

    @if($clarityId !== '')
        <script>
            (function(c,l,a,r,i,t,y){
                c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
            })(window, document, "clarity", "script", @json($clarityId));
        </script>
    @endif

    @if($pixelId !== '')
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($pixelId));
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id={{ urlencode($pixelId) }}&ev=PageView&noscript=1"
                 alt="">
        </noscript>
    @endif

    @if($plausibleDomain !== '')
        <script defer data-domain="{{ $plausibleDomain }}" src="{{ $plausibleSrc }}"></script>
    @endif

    @if($headHtml !== '')
        {!! $headHtml !!}
    @endif
@endif
