<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Support\Cms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PortalLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_positions_as_free_portal_not_saas_teaser(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('100% Gratuito', false)
            ->assertSee('processadas no navegador', false)
            ->assertSee('Recursos &amp; Garantias', false)
            ->assertSee('Nossa Linha de Ferramentas Gratuitas', false)
            ->assertSee('Inspire-se', false)
            ->assertSee('Domine o Design', false)
            ->assertSee('qualidade profissional', false)
            ->assertSee('Inspire-se: Exemplos de Criação Profissional', false)
            ->assertSee('Domine o Design: Dicas Rápidas para Criadores', false)
            ->assertSee('Inscreva-se para Receber Novidades e Packs Gratuitos', false)
            ->assertSee('Quero Receber', false)
            ->assertSee('Precisa de Hospedagem para Seus Projetos?', false)
            ->assertSee('https://hostoo.io/?ref=8pLhQonM', false)
            ->assertSee('rel="sponsored nofollow"', false)
            ->assertDontSee('images/hostoo-logo.png', false)
            ->assertDontSee('Logo Hostoo', false)
            ->assertDontSee('Página de vendas em breve', false)
            ->assertDontSee('TODO: #prova-social', false)
            ->assertDontSee('Blog CriaSys', false)
            ->assertDontSee('Packs CriaSys', false)
            ->assertDontSee('Vitrine CriaSys', false)
            ->assertDontSee('Conteúdo da família CriaSys', false)
            ->assertDontSee('Aprenda e evolua com conteúdo gratuito', false);
    }

    public function test_hosting_card_hidden_without_affiliate_url(): void
    {
        Cms::put('hosting_partner', array_merge(Cms::defaults()['hosting_partner'], [
            'enabled' => true,
            'url' => '',
        ]));

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Precisa de Hospedagem para Seus Projetos?', false);
    }

    public function test_hosting_card_shows_with_sponsored_link(): void
    {
        Cms::put('home', array_merge(Cms::defaults()['home'], [
            'show_hosting_partner' => true,
        ]));
        Cms::put('hosting_partner', array_merge(Cms::defaults()['hosting_partner'], [
            'enabled' => true,
            'url' => 'https://hostoo.io/?ref=8pLhQonM',
        ]));

        $this->get('/')
            ->assertOk()
            ->assertSee('Precisa de Hospedagem para Seus Projetos?', false)
            ->assertSee('Hospede seus sites, sistemas e aplicações', false)
            ->assertSee('Conhecer Planos Hostoo →', false)
            ->assertSee('rel="sponsored nofollow"', false)
            ->assertDontSee('rel="sponsored nofollow noopener"', false)
            ->assertSee('https://hostoo.io/?ref=8pLhQonM', false);
    }

    public function test_newsletter_stores_email_and_returns_thanks(): void
    {
        Mail::fake();

        $this->postJson('/newsletter', ['email' => 'lead@example.com'])
            ->assertOk()
            ->assertJsonFragment([
                'ok' => true,
                'message' => 'Obrigado! Seu e-mail foi cadastrado para os próximos lançamentos.',
            ]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'lead@example.com',
        ]);
    }

    public function test_newsletter_rejects_invalid_email(): void
    {
        $this->postJson('/newsletter', ['email' => 'nao-e-email'])
            ->assertStatus(422);
    }
}
