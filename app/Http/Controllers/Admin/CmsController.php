<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Cms;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'home');
        $tabs = [
            'home' => ['label' => 'Home', 'hint' => 'Hero, link do Blog e seções'],
            'blog' => ['label' => 'Blog CriaSys', 'hint' => 'Textos da ponte MarkCraft → Blog'],
            'testimonials' => ['label' => 'Depoimentos', 'hint' => 'Prova social da home'],
            'footer' => ['label' => 'Rodapé', 'hint' => 'Redes, portfólio e CriaSys Web'],
            'promos' => ['label' => 'Promos e packs', 'hint' => 'Cards, afiliados e doações'],
            'ads' => ['label' => 'Ads do Studio', 'hint' => 'Faixa retangular abaixo do menu'],
            'studio' => ['label' => 'Studio', 'hint' => 'Botões e textos do editor'],
        ];
        if (! array_key_exists($tab, $tabs)) {
            $tab = 'home';
        }

        $testimonialFormRows = Cms::testimonialItems();
        if ($tab === 'testimonials' && $testimonialFormRows === []) {
            $testimonialFormRows = [[
                'id' => '',
                'enabled' => true,
                'name' => '',
                'role' => '',
                'quote' => '',
                'image' => '',
                'image_url' => '',
            ]];
        }

        return view('admin.cms.index', [
            'cms' => Cms::all(),
            'tabs' => $tabs,
            'activeTab' => $tab,
            'testimonialFormRows' => $testimonialFormRows,
        ]);
    }

    public function addTestimonialRow(): RedirectResponse
    {
        $section = array_merge(Cms::defaults()['testimonials'], (array) Cms::get('testimonials', []));
        $items = array_values($section['items'] ?? []);
        $items[] = [
            'id' => (string) Str::uuid(),
            'enabled' => true,
            'name' => '',
            'role' => '',
            'quote' => '',
            'image' => '',
        ];
        $section['items'] = $items;
        Cms::put('testimonials', $section);

        return redirect()
            ->route('admin.cms.index', ['tab' => 'testimonials'])
            ->with('status', 'cms-saved');
    }

    public function update(Request $request): RedirectResponse
    {
        $section = (string) $request->input('section', '');
        $allowed = ['home', 'footer', 'promos', 'ads', 'studio', 'blog', 'testimonials'];
        abort_unless(in_array($section, $allowed, true), 422);

        $payload = match ($section) {
            'home' => $this->homePayload($request),
            'footer' => $this->footerPayload($request),
            'promos' => $this->promosPayload($request),
            'ads' => $this->adsPayload($request),
            'studio' => $this->studioPayload($request),
            'blog' => $this->blogPayload($request),
            'testimonials' => $this->testimonialsPayload($request),
            default => [],
        };

        $testimonialErrors = [];
        if ($section === 'testimonials') {
            $testimonialErrors = $payload['_errors'] ?? [];
            unset($payload['_errors']);
        }

        if ($section === 'promos') {
            Cms::put('promos', $payload['promos']);
            Cms::put('affiliate_packs', $payload['affiliate_packs']);
            Cms::put('donations', $payload['donations']);
        } else {
            Cms::put($section, $payload);
        }

        $returnTab = (string) $request->input('return_tab', $section);
        if (! in_array($returnTab, $allowed, true)) {
            $returnTab = $section;
        }

        $redirect = redirect()
            ->route('admin.cms.index', ['tab' => $returnTab])
            ->with('status', 'cms-saved');

        if ($section === 'testimonials') {
            $published = count(Cms::publishedTestimonials());
            $saved = count(Cms::testimonialItems());
            $redirect->with('testimonials_published', $published)
                ->with('testimonials_saved', $saved);

            if ($testimonialErrors !== []) {
                $redirect->with('testimonial_errors', $testimonialErrors);
            }
        }

        return $redirect;
    }

    /** Checkbox vindo como "0", "1" ou ["0","1"] do form. */
    private function formTruthy(mixed $value): bool
    {
        if (is_array($value)) {
            $value = end($value);
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
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
            $socials[] = [
                'network' => (string) ($row['network'] ?? ''),
                'label' => trim((string) ($row['label'] ?? '')),
                'url' => trim((string) ($row['url'] ?? '')),
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
        $existing = array_merge(Cms::defaults()['ads'], (array) Cms::get('ads', []));
        $out = [];
        foreach (['studio_header_a', 'studio_header_b', 'studio_header_c'] as $key) {
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

        return array_merge($existing, $out);
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
        $existing = array_merge(Cms::defaults()['blog'], (array) Cms::get('blog', []));

        $bullets = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', (string) $request->input('bullets', implode("\n", $existing['bullets'] ?? []))) ?: []
        )));

        return array_merge($existing, [
            'name' => trim((string) $request->input('name', $existing['name'] ?? '')),
            'eyebrow' => trim((string) $request->input('eyebrow', $existing['eyebrow'] ?? '')),
            'headline' => trim((string) $request->input('headline', $existing['headline'] ?? '')),
            'blurb' => trim((string) $request->input('blurb', $existing['blurb'] ?? '')),
            'cta' => trim((string) $request->input('cta', $existing['cta'] ?? '')),
            'cta_pending' => trim((string) $request->input('cta_pending', $existing['cta_pending'] ?? 'Página de vendas em breve')),
            'cta_ready' => $this->formTruthy($request->input('cta_ready')),
            'url' => trim((string) $request->input('url', $existing['url'] ?? '')),
            'register_url' => trim((string) $request->input('register_url', $existing['register_url'] ?? '')),
            'early_access_note' => trim((string) $request->input('early_access_note', $existing['early_access_note'] ?? '')),
            'bullets' => $bullets,
        ]);
    }

    private function testimonialsPayload(Request $request): array
    {
        $items = [];
        $errors = [];
        $rows = (array) $request->input('items', []);
        $fileRows = (array) ($request->allFiles()['items'] ?? []);
        $indices = array_unique(array_merge(array_keys($rows), array_keys($fileRows)));
        sort($indices, SORT_NUMERIC);

        foreach ($indices as $i) {
            $row = (array) ($rows[$i] ?? []);

            if ($this->formTruthy($row['remove'] ?? false)) {
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            $quote = trim((string) ($row['quote'] ?? ''));
            $role = trim((string) ($row['role'] ?? ''));
            $image = Cms::normalizeStoragePath(trim((string) ($row['image'] ?? '')));

            $file = $request->file("items.$i.image_file");
            if ($file !== null && ! $file->isValid()) {
                $errors[] = 'Imagem do depoimento '.((int) $i + 1).' não foi aceita (muito grande ou inválida).';
            }

            if ($file !== null && $file->isValid()) {
                $stored = $file->store('cms/testimonials', 'public');
                $image = Cms::publicStoragePath($stored);
            }

            if ($name === '' && $quote === '' && $image === '') {
                if ($file !== null) {
                    $errors[] = 'Imagem do depoimento '.((int) $i + 1).' não salvou. Tente JPG ou PNG.';
                }

                continue;
            }

            $items[] = [
                'id' => filled($row['id'] ?? null) ? (string) $row['id'] : (string) Str::uuid(),
                'enabled' => $this->formTruthy($row['enabled'] ?? false),
                'name' => $name,
                'role' => $role,
                'quote' => $quote,
                'image' => $image,
            ];
        }

        if ($indices !== [] && $items === []) {
            $errors[] = 'Nenhum depoimento salvo. Envie uma imagem ou marque remover em linhas vazias.';
        }

        return [
            'section_enabled' => $this->formTruthy($request->input('section_enabled', '1')),
            'heading' => trim((string) $request->input('heading', 'Depoimentos e prova social')),
            'intro' => trim((string) $request->input('intro', '')),
            'items' => array_values($items),
            '_errors' => array_values(array_unique($errors)),
        ];
    }
}
