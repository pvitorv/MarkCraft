<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Cms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_home_keeps_reserved_social_proof_without_testimonials(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Espaço reservado · prova social', false);
    }

    public function test_admin_can_publish_testimonial_and_home_shows_it(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'testimonials',
            'section_enabled' => '1',
            'heading' => 'O que estão falando',
            'intro' => 'Feedback real',
            'items' => [
                [
                    'id' => 't1',
                    'enabled' => '1',
                    'name' => 'Ana Silva',
                    'role' => 'Designer',
                    'quote' => 'O MarkCraft acelerou minhas artes.',
                    'image' => '',
                ],
            ],
        ])->assertRedirect();

        $this->get('/')
            ->assertOk()
            ->assertSee('O MarkCraft acelerou minhas artes.', false)
            ->assertSee('Ana Silva', false)
            ->assertSee('O que estão falando', false)
            ->assertDontSee('Espaço reservado · prova social', false);
    }

    public function test_home_has_no_testimonial_submit_entry_point(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertDontSee('Inserir depoimento', false)
            ->assertDontSee('Enviar depoimento', false);
    }

    public function test_admin_can_upload_testimonial_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'testimonials',
            'section_enabled' => '1',
            'heading' => 'Depoimentos',
            'items' => [
                [
                    'id' => '',
                    'enabled' => '1',
                    'name' => 'Bruno Lima',
                    'quote' => 'Exportei tudo em minutos.',
                    'image' => '',
                    'image_file' => UploadedFile::fake()->image('foto.jpg'),
                ],
            ],
        ])->assertRedirect();

        $items = Cms::publishedTestimonials();
        $this->assertCount(1, $items);
        $this->assertNotSame('', $items[0]['image']);
        $this->assertCount(1, Storage::disk('public')->files('cms/testimonials'));
    }

    public function test_section_enabled_survives_checkbox_plus_hidden(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'testimonials',
            'section_enabled' => ['0', '1'],
            'heading' => 'Depoimentos',
            'items' => [
                [
                    'id' => '',
                    'enabled' => '1',
                    'name' => 'Ana',
                    'quote' => 'Publicado de verdade.',
                    'image' => '',
                ],
            ],
        ])->assertRedirect()
            ->assertSessionHas('testimonials_published', 1);

        $this->assertTrue(Cms::get('testimonials.section_enabled'));
        $this->get('/')
            ->assertSee('Publicado de verdade.', false)
            ->assertDontSee('Espaço reservado · prova social', false);
    }

    public function test_can_save_and_publish_image_only_testimonial(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'testimonials',
            'section_enabled' => '1',
            'heading' => 'Depoimentos',
            'items' => [
                [
                    'id' => '',
                    'enabled' => '1',
                    'name' => '',
                    'role' => '',
                    'quote' => '',
                    'image' => '',
                    'image_file' => UploadedFile::fake()->image('print.png', 900, 600),
                ],
            ],
        ])->assertRedirect()
            ->assertSessionHas('testimonials_saved', 1)
            ->assertSessionHas('testimonials_published', 1);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Espaço reservado · prova social', false);
    }

    public function test_admin_testimonials_form_has_add_button(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/cms?tab=testimonials')
            ->assertOk()
            ->assertSee('+ Adicionar depoimento', false)
            ->assertSee('Remover ao salvar', false);
    }

    public function test_admin_can_save_more_than_six_testimonials(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $items = [];
        for ($i = 1; $i <= 9; $i++) {
            $items[] = [
                'id' => '',
                'enabled' => '1',
                'name' => "Pessoa $i",
                'role' => 'Tester',
                'quote' => "Depoimento numero $i sobre o MarkCraft.",
                'image' => '',
            ];
        }

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'testimonials',
            'section_enabled' => '1',
            'heading' => 'Depoimentos',
            'items' => $items,
        ])->assertRedirect();

        $this->assertCount(9, Cms::testimonialItems());
        $this->assertCount(9, Cms::publishedTestimonials());

        $this->get('/')
            ->assertOk()
            ->assertSee('Depoimento numero 9 sobre o MarkCraft.', false);
    }

    public function test_saving_drops_empty_rows_and_keeps_filled_ones(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'testimonials',
            'section_enabled' => '1',
            'heading' => 'Depoimentos',
            'items' => [
                ['id' => '', 'enabled' => '1', 'name' => 'Ana', 'quote' => 'Muito bom mesmo.', 'image' => ''],
                ['id' => '', 'enabled' => '0', 'name' => '', 'quote' => '', 'image' => ''],
                ['id' => '', 'enabled' => '1', 'name' => 'Bruno', 'quote' => 'Rapido e limpo.', 'image' => ''],
            ],
        ])->assertRedirect();

        $saved = Cms::testimonialItems();
        $this->assertCount(2, $saved);
        $this->assertSame('Ana', $saved[0]['name']);
        $this->assertSame('Bruno', $saved[1]['name']);
    }

    public function test_admin_can_publish_testimonial_with_print_only(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'testimonials',
            'section_enabled' => '1',
            'heading' => 'Depoimentos',
            'items' => [
                [
                    'id' => '',
                    'enabled' => '1',
                    'name' => 'Cliente WhatsApp',
                    'role' => '',
                    'quote' => '',
                    'image' => '',
                    'image_file' => UploadedFile::fake()->image('print-depoimento.png', 800, 600),
                ],
            ],
        ])->assertRedirect();

        $published = Cms::publishedTestimonials();
        $this->assertCount(1, $published);
        $this->assertStringStartsWith('/storage/', $published[0]['image']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Cliente WhatsApp', false)
            ->assertSee(Cms::mediaUrl($published[0]['image']), false);
    }

    public function test_legacy_absolute_image_urls_still_load_on_home(): void
    {
        Cms::put('testimonials', [
            'section_enabled' => true,
            'heading' => 'Depoimentos',
            'intro' => '',
            'items' => [
                [
                    'id' => 't1',
                    'enabled' => true,
                    'name' => 'Ana',
                    'role' => '',
                    'quote' => 'Muito bom.',
                    'image' => 'http://markcraft.test/storage/cms/testimonials/legacy.png',
                ],
            ],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee(Cms::mediaUrl('/storage/cms/testimonials/legacy.png'), false);
    }

    public function test_home_testimonial_print_is_not_round_avatar(): void
    {
        Cms::put('testimonials', [
            'section_enabled' => true,
            'heading' => 'Depoimentos',
            'intro' => '',
            'items' => [
                [
                    'id' => 't1',
                    'enabled' => true,
                    'name' => 'Ana',
                    'role' => '',
                    'quote' => 'Muito bom.',
                    'image' => 'https://example.com/print.png',
                ],
            ],
        ]);

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('object-contain', $html);
        $this->assertStringNotContainsString('rounded-full object-cover', $html);
    }

    public function test_removing_a_testimonial_takes_it_off_the_home(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'testimonials',
            'section_enabled' => '1',
            'heading' => 'Depoimentos',
            'items' => [
                ['id' => '', 'enabled' => '1', 'name' => 'Ana', 'quote' => 'Fica no ar por enquanto.', 'image' => ''],
            ],
        ])->assertRedirect();

        $this->get('/')->assertSee('Fica no ar por enquanto.', false);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'testimonials',
            'section_enabled' => '1',
            'heading' => 'Depoimentos',
            'items' => [],
        ])->assertRedirect();

        $this->get('/')
            ->assertDontSee('Fica no ar por enquanto.', false)
            ->assertSee('Espaço reservado · prova social', false);
    }

    public function test_hero_blog_card_shows_pending_cta_until_link_is_activated(): void
    {
        Cms::put('blog', array_merge(Cms::defaults()['blog'], [
            'url' => 'https://blog.example.com',
            'cta_ready' => false,
            'cta_pending' => 'Página de vendas em breve',
        ]));

        $this->assertFalse(Cms::blogCtaReady(Cms::get('blog')));

        $this->get('/')
            ->assertOk()
            ->assertSee('Página de vendas em breve', false);
    }

    public function test_admin_can_activate_blog_link_and_home_shows_live_cta(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'blog',
            'name' => 'Blog CriaSys Web',
            'headline' => 'Blog + painel',
            'cta' => 'Ir para o Blog',
            'cta_pending' => 'Em breve',
            'cta_ready' => '1',
            'url' => 'https://blog.criasysweb.com.br',
            'register_url' => 'https://blog.criasysweb.com.br/cadastro',
            'bullets' => '',
        ])->assertRedirect();

        $this->get('/')
            ->assertOk()
            ->assertSee('href="https://blog.criasysweb.com.br"', false)
            ->assertSee('Ir para o Blog', false);
    }

    public function test_admin_cms_home_tab_has_blog_link_configuration(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/cms?tab=home')
            ->assertOk()
            ->assertSee('Link do Blog no card do hero', false)
            ->assertSee('URL da página de vendas', false)
            ->assertSee('Salvar link do Blog', false);
    }

    public function test_home_never_shows_cms_configuration_hints(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/')->assertDontSee('Configurar link do Blog', false);
        $this->get('/')->assertDontSee('Configurar link do Blog', false);
    }
}
