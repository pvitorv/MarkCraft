<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoTest extends TestCase
{
    public function test_robots_txt_lists_sitemap_and_blocks_private_areas(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Sitemap:', false);
        $response->assertSee('/sitemap.xml', false);
        $response->assertSee('Disallow: /studio', false);
        $response->assertSee('Disallow: /admin', false);
        $response->assertSee('Disallow: /login', false);
    }

    public function test_sitemap_xml_includes_home(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<urlset', false);
        $response->assertSee(url('/'), false);
        $response->assertSee('priority', false);
    }

    public function test_home_has_seo_meta_and_json_ld(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('SoftwareApplication', false);
        $response->assertSee('index, follow', false);
    }

    public function test_login_is_noindex(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('noindex, nofollow', false);
    }
}
