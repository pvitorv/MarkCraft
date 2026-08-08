<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Cms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_cms(): void
    {
        $this->get('/admin/cms')->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_cms(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/cms')->assertForbidden();
    }

    public function test_admin_can_view_and_save_footer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/cms')->assertOk();

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'footer',
            'tagline' => 'Tagline CMS',
            'portfolio_label' => 'Portfólio',
            'portfolio_url' => 'https://example.com/portfolio',
            'criasysweb_label' => 'CriaSys Web',
            'criasysweb_url' => 'https://criasysweb.com.br',
            'socials' => [
                ['network' => 'instagram', 'label' => 'Instagram', 'url' => 'https://instagram.com/x'],
            ],
        ])->assertRedirect();

        $this->assertSame('Tagline CMS', Cms::get('footer.tagline'));
        $this->assertSame('https://example.com/portfolio', Cms::get('footer.portfolio_url'));
    }

    public function test_home_shows_footer_portfolio_when_configured(): void
    {
        Cms::put('footer', array_merge(Cms::defaults()['footer'], [
            'portfolio_url' => 'https://example.com/portfolio',
            'criasysweb_url' => 'https://criasysweb.com.br',
        ]));

        $this->get('/')
            ->assertOk()
            ->assertSee('https://example.com/portfolio', false)
            ->assertSee('https://criasysweb.com.br', false);
    }
}
