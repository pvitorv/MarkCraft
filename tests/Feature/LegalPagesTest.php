<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_legal_pages_are_public_and_linked(): void
    {
        foreach (['privacidade', 'termos', 'cookies', 'uso-aceitavel'] as $slug) {
            $this->get(route('legal.show', $slug))
                ->assertOk()
                ->assertSee(config('legal.pages.'.$slug.'.title'), false)
                ->assertSee('Última atualização', false);
        }
    }

    public function test_unknown_legal_page_returns_404(): void
    {
        $this->get('/legal/nao-existe')->assertNotFound();
    }

    public function test_home_footer_links_to_legal_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('legal.show', 'privacidade'), false)
            ->assertSee(route('legal.show', 'termos'), false)
            ->assertSee(route('legal.show', 'cookies'), false)
            ->assertSee(route('legal.show', 'uso-aceitavel'), false)
            ->assertSee('>Privacidade</a>', false)
            ->assertSee('>Termos de uso</a>', false)
            ->assertSee('github.com/pvitorv/MarkCraft', false)
            ->assertSee('Código-fonte', false);
    }

    public function test_sitemap_includes_legal_pages(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();

        foreach (['privacidade', 'termos', 'cookies', 'uso-aceitavel'] as $slug) {
            $response->assertSee(url('/legal/'.$slug), false);
        }
    }

    public function test_privacy_mentions_lgpd_and_contact(): void
    {
        $this->get(route('legal.show', 'privacidade'))
            ->assertOk()
            ->assertSee('LGPD', false)
            ->assertSee(config('legal.contact_email'), false)
            ->assertSee('no seu navegador', false)
            ->assertDontSee('remoção de fundo em servidor', false);
    }

    public function test_home_credits_point_to_public_agpl_source(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('github.com/pvitorv/MarkCraft', false)
            ->assertSee('AGPL-3.0', false)
            ->assertSee('driver atual:', false)
            ->assertSee('imgly', false);
    }
}
