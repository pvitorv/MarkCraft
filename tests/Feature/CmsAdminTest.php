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
            'section' => 'landing',
            'blog_cta_ready' => '1',
            'blog_url' => 'https://blog.criasysweb.com.br',
            'blog_cta' => 'Ir para o Blog',
            'blog_cta_pending' => 'Em breve',
            'blog_register_url' => 'https://blog.criasysweb.com.br/cadastro',
            'bridge_eyebrow' => '',
            'bridge_headline' => '',
            'bridge_paragraph_1' => '',
            'bridge_paragraph_2' => '',
            'bridge_paragraph_3' => '',
            'bridge_footnote' => '',
            'hub_eyebrow' => '',
            'hub_headline' => '',
            'hub_intro' => '',
            'editor_headline' => '',
            'editor_intro' => '',
            'funnel_eyebrow' => '',
            'funnel_headline' => '',
        ] + $this->landingFormModulesExtras())->assertRedirect();

        $this->get('/')
            ->assertOk()
            ->assertSee('href="https://blog.criasysweb.com.br"', false)
            ->assertSee('Ir para o Blog', false);
    }

    public function test_admin_cms_home_tab_has_blog_link_configuration(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/cms?tab=landing')
            ->assertOk()
            ->assertSee('Botões e links do Blog', false)
            ->assertSee('URL (página de vendas)', false)
            ->assertSee('URL de cadastro', false);
    }

    public function test_home_never_shows_cms_configuration_hints(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/')->assertDontSee('Configurar link do Blog', false);
        $this->get('/')->assertDontSee('Configurar link do Blog', false);
    }

    public function test_admin_can_edit_landing_blog_copy_and_home_reflects_it(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'landing',
            'bridge_eyebrow' => 'Do MarkCraft para o {blog}',
            'bridge_headline' => 'Headline CMS de teste',
            'bridge_paragraph_1' => 'Parágrafo um.',
            'bridge_paragraph_2' => 'Parágrafo dois {blog}.',
            'bridge_paragraph_3' => 'Parágrafo três.',
            'bridge_footnote' => 'Nota trial CMS.',
            'hub_eyebrow' => 'Hub CMS',
            'hub_headline' => 'Módulos CMS headline',
            'hub_intro' => 'Intro hub CMS.',
            'editor_headline' => 'Editor CMS headline',
            'editor_intro' => 'Editor intro CMS.',
            'funnel_eyebrow' => 'Funil CMS',
            'funnel_headline' => 'Funil headline CMS',
            'blog_cta_ready' => '1',
            'blog_url' => 'https://vendas.example.com',
            'blog_cta' => 'Ir para vendas CMS',
            'blog_register_url' => 'https://vendas.example.com/cadastro',
            'blog_register_cta' => 'Cadastro CMS',
        ] + $this->landingFormModulesExtras())->assertRedirect();

        $this->get('/')
            ->assertOk()
            ->assertSee('Headline CMS de teste', false)
            ->assertSee('href="https://vendas.example.com"', false)
            ->assertSee('href="https://vendas.example.com/cadastro"', false)
            ->assertSee('Ir para vendas CMS', false)
            ->assertSee('Cadastro CMS', false);
    }

    public function test_admin_cms_has_landing_blog_tab(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/cms?tab=landing')
            ->assertOk()
            ->assertSee('Landing Blog', false)
            ->assertSee('Ponte — Do MarkCraft para o Blog', false)
            ->assertSee('Salvar landing + links dos botões', false);
    }

    public function test_admin_cms_has_packs_and_donations_tabs(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/cms?tab=packs')
            ->assertOk()
            ->assertSee('Packs CriaSys', false)
            ->assertSee('Link da oferta', false)
            ->assertSee('Salvar packs', false);

        $this->actingAs($admin)
            ->get('/admin/cms?tab=donations')
            ->assertOk()
            ->assertSee('Doações · Apoiar', false)
            ->assertSee('URL do gateway', false)
            ->assertSee('Salvar doações', false);
    }

    public function test_admin_can_save_packs_with_affiliate_links(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'packs',
            'packs_hub_title' => 'Hub Packs CMS',
            'packs_hub_subtitle' => 'Subtítulo CMS packs',
            'packs_hub_link_label' => 'Comprar agora →',
            'packs' => [
                ['tag' => 'Teste', 'title' => 'Pack CMS Teste', 'blurb' => 'Descrição pack CMS', 'affiliate_url' => 'https://packs.example.com/oferta'],
            ],
        ])->assertRedirect();

        $this->assertSame('Hub Packs CMS', Cms::get('packs_hub.title'));
        $this->assertSame('https://packs.example.com/oferta', Cms::get('affiliate_packs.0.affiliate_url'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Pack CMS Teste', false)
            ->assertSee('href="https://packs.example.com/oferta"', false);
    }

    public function test_admin_can_save_donations_and_modal_uses_gateway(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/cms', [
            'section' => 'donations',
            'donation_min_brl' => 5,
            'donation_pix_key' => 'pix@teste.com',
            'donation_gateway_url' => 'https://pay.example.com/doar',
            'donation_button_label' => 'Doar a partir de R$ {min}',
            'donation_modal_title' => 'Apoiar CMS título',
            'donation_modal_intro' => 'Intro CMS {min}',
            'donation_modal_body' => 'Corpo modal CMS',
            'donation_modal_note' => 'Nota CMS',
            'donation_page_title' => 'Página apoiar CMS',
            'donation_page_body_1' => 'Parágrafo 1 CMS',
            'donation_page_body_2' => 'Parágrafo 2 CMS',
            'donation_page_body_3' => '',
        ])->assertRedirect();

        $this->get('/')
            ->assertOk()
            ->assertSee('href="https://pay.example.com/doar"', false)
            ->assertSee('Doar a partir de R$ 5,00', false)
            ->assertSee('Apoiar CMS título', false);
    }

    /** @return array<string, mixed> */
    private function landingFormModulesExtras(): array
    {
        $defaults = Cms::landingDefaults();

        return [
            'bridge_steps' => $defaults['bridge']['steps'],
            'hub_modules' => array_map(function (array $mod) {
                $row = [
                    'icon' => $mod['icon'],
                    'title' => $mod['title'],
                    'text' => $mod['text'],
                ];
                if (! empty($mod['wide'])) {
                    $row['wide'] = '1';
                }

                return $row;
            }, $defaults['hub']['modules']),
            'hub_extras' => $defaults['hub']['extras'],
            'editor_columns' => $defaults['editor']['columns'],
        ];
    }
}
