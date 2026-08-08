<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingStudioShortcutsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_studio_preset_links(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('/studio?preset=ig_feed_square', false);
        $response->assertSee('/studio?preset=ig_story', false);
        $response->assertSee('/studio?preset=yt_thumb', false);
        $response->assertSee('/studio?preset=li_feed', false);
        $response->assertSee('/studio?preset=fb_cover', false);
    }

    public function test_guest_studio_preset_redirects_to_login_with_intended(): void
    {
        $response = $this->get('/studio?preset=ig_story');

        $response->assertRedirect('/login');
        $this->assertTrue(str_contains((string) session('url.intended'), 'preset=ig_story'));
    }

    public function test_authenticated_user_opens_studio_with_preset_meta(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/studio?preset=yt_thumb');

        $response->assertOk();
        $response->assertSee('name="studio-initial-preset"', false);
        $response->assertSee('content="yt_thumb"', false);
    }

    public function test_login_after_preset_shortcut_lands_on_studio_with_preset(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->get('/studio?preset=li_feed')->assertRedirect('/login');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/studio?preset=li_feed');
    }
}
