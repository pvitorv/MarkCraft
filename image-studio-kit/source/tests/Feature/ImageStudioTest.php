<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Editor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageStudioTest extends TestCase
{
    use RefreshDatabase;

    protected User $dono;

    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dono = User::factory()->create();
        $this->blog = Blog::create([
            'owner_id' => $this->dono->id,
            'name' => 'Blog Studio',
            'slug' => 'blog-studio',
            'is_public' => true,
        ]);
        $this->blog->settings()->create();
        $this->blog->members()->attach($this->dono->id, ['role' => 'owner', 'is_active' => true]);
    }

    public function test_dono_abre_o_studio(): void
    {
        $this->actingAs($this->dono)
            ->get(route('painel.studio'))
            ->assertOk()
            ->assertSee('Image Studio')
            ->assertSee('criasys-image-studio-presets')
            ->assertSee('studio-remove-bg-url', false)
            ->assertSee(route('painel.studio.remover-fundo'), false);
    }

    public function test_editor_tambem_abre_o_studio(): void
    {
        $editor = Editor::create([
            'name' => 'Évilyn',
            'email' => 'evilyn@trabalho.com',
            'password' => 'senha-secreta',
        ]);
        $this->blog->editors()->attach($editor->id, ['is_active' => true]);

        $this->actingAs($editor, 'editor')
            ->get(route('painel.studio'))
            ->assertOk()
            ->assertSee('Image Studio');
    }

    public function test_catalogo_json_tem_presets_e_fontes(): void
    {
        $this->actingAs($this->dono)
            ->getJson(route('painel.studio.catalogo'))
            ->assertOk()
            ->assertJsonStructure([
                'presets',
                'groups',
                'export_formats',
                'fonts',
                'elements',
                'defaults',
                'templates',
                'packs',
                'pack_categories',
                'brand',
                'background_removal_driver',
            ])
            ->assertJsonPath('background_removal_driver', 'rembg')
            ->assertJsonPath('background_removal_client', false)
            ->assertJsonPath('brand.name', 'Blog Studio');
    }

    public function test_driver_imgly_fica_isolado_ate_ativar_por_config(): void
    {
        config(['image_studio.background_removal.driver' => 'imgly']);

        $this->actingAs($this->dono)
            ->getJson(route('painel.studio.catalogo'))
            ->assertOk()
            ->assertJsonPath('background_removal_driver', 'imgly')
            ->assertJsonPath('background_removal_client', true)
            ->assertJsonPath('background_removal_available', true);
    }

    public function test_remover_fundo_rembg_responde_png_quando_servico_ok(): void
    {
        $this->mock(\App\Services\ImageStudio\BackgroundRemovalService::class, function ($mock) {
            $mock->shouldReceive('driver')->andReturn('rembg');
            $mock->shouldReceive('isAvailable')->andReturn(true);
            $mock->shouldReceive('remove')->once()->andReturnUsing(function (string $in, string $out) {
                // PNG 1x1 transparente mínimo
                $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
                file_put_contents($out, $png);
            });
        });

        $png = UploadedFile::fake()->image('foto.png', 80, 80);

        $this->actingAs($this->dono)
            ->post(route('painel.studio.remover-fundo'), ['file' => $png])
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_remover_fundo_rembg_real_quando_python_disponivel(): void
    {
        $remover = app(\App\Services\ImageStudio\BackgroundRemovalService::class);
        if ($remover->driver() !== 'rembg' || ! $remover->isAvailable()) {
            $this->markTestSkipped('rembg/Python não disponível neste ambiente.');
        }

        $png = UploadedFile::fake()->image('foto-real.png', 64, 64);

        $response = $this->actingAs($this->dono)
            ->post(route('painel.studio.remover-fundo'), ['file' => $png]);

        $response->assertOk()->assertHeader('content-type', 'image/png');
        $this->assertGreaterThan(50, strlen($response->getContent()));
    }

    public function test_salvar_png_grava_no_storage_do_blog(): void
    {
        Storage::fake('public');

        $png = UploadedFile::fake()->image('arte.png', 200, 200);

        $this->actingAs($this->dono)
            ->post(route('painel.studio.salvar'), [
                'file' => $png,
                'format' => 'png',
                'preset' => 'ig_feed_square',
            ])
            ->assertOk()
            ->assertJsonPath('export.filename', fn ($name) => str_ends_with((string) $name, '.png'));

        Storage::disk('public')->assertExists(
            'blogs/'.$this->blog->id.'/studio'
        );
    }

    public function test_convidado_nao_abre_studio(): void
    {
        $this->get(route('painel.studio'))->assertRedirect();
    }

    public function test_catalogo_prioriza_formatos_do_blog(): void
    {
        $response = $this->actingAs($this->dono)
            ->getJson(route('painel.studio.catalogo'))
            ->assertOk();

        $presets = collect($response->json('presets'));
        $this->assertTrue($presets->contains(fn ($p) => ($p['slug'] ?? null) === 'blog_cover'));
        $this->assertTrue($presets->contains(fn ($p) => ($p['slug'] ?? null) === 'blog_story'));
        $this->assertTrue($presets->contains(fn ($p) => ($p['slug'] ?? null) === 'blog_square'));

        $this->assertSame('blog_cover', $response->json('defaults.preset'));
        $this->assertSame(1200, $response->json('defaults.width'));
        $this->assertSame(630, $response->json('defaults.height'));

        $primary = collect($response->json('primary_formats'))->pluck('slug');
        $this->assertTrue($primary->isEmpty());
    }

    public function test_pagina_studio_mostra_atalhos_de_formato(): void
    {
        $html = $this->actingAs($this->dono)
            ->get(route('painel.studio'))
            ->assertOk()
            ->assertSee('studio-canvas-toolbar', false)
            ->assertSee('Encurtador', false)
            ->assertSee('Conversor', false)
            ->assertDontSee('Este slide não tem imagem ou vídeo')
            ->getContent();

        // Os 3 formatos do blog vivem só no modal (presets), não como chips soltos.
        $this->assertStringContainsString('blog_cover', $html);
        $this->assertStringContainsString('blog_story', $html);
        $this->assertStringContainsString('blog_square', $html);
        $this->assertStringContainsString('Capa do post', $html);
        $this->assertStringContainsString('Todos os formatos', $html);
        $this->assertStringContainsString('imageStudioDeleteSelection()', $html);
        $this->assertStringContainsString('Limpar área', $html);
        $this->assertStringContainsString('studio-context-menu', $html);
        $this->assertStringNotContainsString('imageStudioPrimaryFormats()', $html);
        $this->assertStringContainsString('imageStudioOpenElementsModal()', $html);
        $this->assertStringContainsString('imageStudioElementsModalOpen', $html);
        $this->assertStringContainsString('imageStudioDimensionsModalOpen', $html);
        $this->assertStringContainsString('openImageStudioDimensionsModal()', $html);
        $this->assertStringContainsString('pickImageStudioReferenceDimensions(p)', $html);
        $this->assertStringContainsString('openImageStudioTemplatesModal()', $html);
        $this->assertStringContainsString('Layouts', $html);
        $this->assertStringContainsString('openImageStudioPacksModal()', $html);
        $this->assertStringContainsString('Pacotes', $html);
        $this->assertStringContainsString('imageStudioPacksModalOpen', $html);
        $this->assertStringContainsString('Marca do blog', $html);
        $this->assertStringContainsString('criasys-image-studio-templates', $html);
        $this->assertStringContainsString('criasys-image-studio-packs', $html);
        $this->assertStringContainsString('criasys-image-studio-brand', $html);
    }

    public function test_catalogo_inclui_templates_e_kit_de_marca(): void
    {
        $response = $this->actingAs($this->dono)
            ->getJson(route('painel.studio.catalogo'))
            ->assertOk();

        $templates = collect($response->json('templates'));
        $this->assertTrue($templates->isNotEmpty());
        $this->assertTrue($templates->contains(fn ($t) => filled($t['slug'] ?? null)));
        // Layouts = redes/formatos — NÃO misturar com pacotes
        $this->assertTrue($templates->contains(fn ($t) => ($t['slug'] ?? '') === 'story_quote'));
        $this->assertFalse($templates->contains(fn ($t) => ($t['slug'] ?? '') === 'pack_blog_cover_ink'));
        $this->assertSame('Blog Studio', $response->json('brand.name'));
        $this->assertNotEmpty($response->json('brand.colors.accent'));

        $packs = collect($response->json('packs'));
        $this->assertGreaterThanOrEqual(4, $packs->count());
        $this->assertTrue($packs->contains(fn ($p) => ($p['category'] ?? '') === 'web_blog'));
        $this->assertTrue($packs->contains(fn ($p) => ($p['category'] ?? '') === 'brand'));
        $this->assertTrue($packs->contains(fn ($p) => ($p['category'] ?? '') === 'sales'));
        $this->assertTrue($packs->contains(fn ($p) => ($p['category'] ?? '') === 'mockups'));
        $webPack = $packs->firstWhere('category', 'web_blog');
        $this->assertTrue(collect($webPack['items'] ?? [])->contains(fn ($i) => ($i['slug'] ?? '') === 'pack_blog_cover_ink'));
        $salesPack = $packs->firstWhere('category', 'sales');
        $this->assertGreaterThanOrEqual(10, count($salesPack['items'] ?? []));
        $this->assertTrue(collect($salesPack['items'] ?? [])->contains(fn ($i) => str_contains(($i['name'] ?? ''), 'R$') || str_contains(($i['description'] ?? ''), 'R$')));
        $brandPack = $packs->firstWhere('category', 'brand');
        $this->assertGreaterThanOrEqual(15, count($brandPack['items'] ?? []));
        $this->assertTrue(collect($brandPack['items'])->contains(fn ($i) => ($i['type'] ?? '') === 'brand_kit'));
        $this->assertNotEmpty($response->json('pack_categories'));
    }

    public function test_menu_do_painel_tem_atalho_studio(): void
    {
        $this->actingAs($this->dono)
            ->get(route('painel.dashboard'))
            ->assertOk()
            ->assertSee('Ferramentas')
            ->assertSee(route('painel.studio'), false);
    }
}
