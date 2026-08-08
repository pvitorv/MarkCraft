<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Cms;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function index(): View
    {
        return view('admin.cms.index', [
            'cms' => Cms::all(),
            'tabs' => [
                'home' => 'Home',
                'footer' => 'Rodapé',
                'promos' => 'Promos / afiliados',
                'ads' => 'Ads Studio',
                'studio' => 'Studio / modais',
                'blog' => 'Blog / CriaSys',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $section = (string) $request->input('section', '');
        $allowed = ['home', 'footer', 'promos', 'ads', 'studio', 'blog', 'affiliate_packs', 'donations'];
        abort_unless(in_array($section, $allowed, true), 422);

        $payload = match ($section) {
            'home' => $this->homePayload($request),
            'footer' => $this->footerPayload($request),
            'promos' => $this->promosPayload($request),
            'ads' => $this->adsPayload($request),
            'studio' => $this->studioPayload($request),
            'blog' => $this->blogPayload($request),
            'affiliate_packs' => $this->packsPayload($request),
            'donations' => $this->donationsPayload($request),
            default => [],
        };

        if ($section === 'promos') {
            // packs + donations salvos junto na aba promos
            Cms::put('promos', $payload['promos']);
            Cms::put('affiliate_packs', $payload['affiliate_packs']);
            Cms::put('donations', $payload['donations']);
        } else {
            Cms::put($section, $payload);
        }

        return redirect()
            ->route('admin.cms.index', ['tab' => $section === 'affiliate_packs' ? 'promos' : $section])
            ->with('status', 'cms-saved');
    }

    private function homePayload(Request $request): array
    {
        return [
            'hero_title' => trim((string) $request->input('hero_title', '')),
            'hero_blurb' => trim((string) $request->input('hero_blurb', '')),
            'formats_heading' => trim((string) $request->input('formats_heading', '')),
            'formats_blurb' => trim((string) $request->input('formats_blurb', '')),
            'show_format_shortcuts' => $request->boolean('show_format_shortcuts'),
            'show_hub' => $request->boolean('show_hub'),
            'show_blog_bridge' => $request->boolean('show_blog_bridge'),
            'show_landing_promo' => $request->boolean('show_landing_promo'),
        ];
    }

    private function footerPayload(Request $request): array
    {
        $socials = [];
        foreach ((array) $request->input('socials', []) as $row) {
            $url = trim((string) ($row['url'] ?? ''));
            $socials[] = [
                'network' => (string) ($row['network'] ?? ''),
                'label' => trim((string) ($row['label'] ?? '')),
                'url' => $url,
            ];
        }

        return [
            'tagline' => trim((string) $request->input('tagline', '')),
            'portfolio_label' => trim((string) $request->input('portfolio_label', 'Portfólio')),
            'portfolio_url' => trim((string) $request->input('portfolio_url', '')),
            'criasysweb_label' => trim((string) $request->input('criasysweb_label', 'CriaSys Web')),
            'criasysweb_url' => trim((string) $request->input('criasysweb_url', '')),
            'socials' => $socials,
        ];
    }

    private function promosPayload(Request $request): array
    {
        $slots = ['landing_mid', 'studio_top', 'studio_sidebar'];
        $promos = [];
        foreach ($slots as $slot) {
            $promos[$slot] = [
                'enabled' => $request->boolean("promo_{$slot}_enabled"),
                'eyebrow' => trim((string) $request->input("promo_{$slot}_eyebrow", '')),
                'title' => trim((string) $request->input("promo_{$slot}_title", '')),
                'blurb' => trim((string) $request->input("promo_{$slot}_blurb", '')),
                'cta' => trim((string) $request->input("promo_{$slot}_cta", '')),
                'url' => trim((string) $request->input("promo_{$slot}_url", '')),
            ];
        }

        $packs = [];
        foreach ((array) $request->input('packs', []) as $row) {
            $packs[] = [
                'title' => trim((string) ($row['title'] ?? '')),
                'blurb' => trim((string) ($row['blurb'] ?? '')),
                'affiliate_url' => trim((string) ($row['affiliate_url'] ?? '')),
                'tag' => trim((string) ($row['tag'] ?? '')),
            ];
        }

        return [
            'promos' => $promos,
            'affiliate_packs' => array_values(array_filter($packs, fn ($p) => $p['title'] !== '')),
            'donations' => [
                'min_brl' => (float) $request->input('donation_min_brl', 2),
                'pix_key' => trim((string) $request->input('donation_pix_key', '')),
                'gateway_url' => trim((string) $request->input('donation_gateway_url', '')),
            ],
        ];
    }

    private function adsPayload(Request $request): array
    {
        $out = [];
        foreach (['studio_header_a', 'studio_header_b'] as $key) {
            $out[$key] = [
                'enabled' => $request->boolean("{$key}_enabled"),
                'label' => trim((string) $request->input("{$key}_label", '')),
                'mode' => in_array($request->input("{$key}_mode"), ['placeholder', 'adsense', 'html'], true)
                    ? $request->input("{$key}_mode")
                    : 'placeholder',
                'adsense_client' => trim((string) $request->input("{$key}_adsense_client", '')),
                'adsense_slot' => trim((string) $request->input("{$key}_adsense_slot", '')),
                'html' => (string) $request->input("{$key}_html", ''),
            ];
        }

        return $out;
    }

    private function studioPayload(Request $request): array
    {
        return [
            'show_blog_bridge_btn' => $request->boolean('show_blog_bridge_btn'),
            'show_sidebar_promo' => $request->boolean('show_sidebar_promo'),
            'show_header_ads' => $request->boolean('show_header_ads'),
            'aside_blog_blurb' => trim((string) $request->input('aside_blog_blurb', '')),
        ];
    }

    private function blogPayload(Request $request): array
    {
        $bullets = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', (string) $request->input('bullets', '')) ?: []
        )));

        return [
            'name' => trim((string) $request->input('name', '')),
            'eyebrow' => trim((string) $request->input('eyebrow', '')),
            'headline' => trim((string) $request->input('headline', '')),
            'blurb' => trim((string) $request->input('blurb', '')),
            'cta' => trim((string) $request->input('cta', '')),
            'url' => trim((string) $request->input('url', '')),
            'register_url' => trim((string) $request->input('register_url', '')),
            'early_access_note' => trim((string) $request->input('early_access_note', '')),
            'bullets' => $bullets,
        ];
    }

    private function packsPayload(Request $request): array
    {
        return $this->promosPayload($request)['affiliate_packs'];
    }

    private function donationsPayload(Request $request): array
    {
        return $this->promosPayload($request)['donations'];
    }
}
