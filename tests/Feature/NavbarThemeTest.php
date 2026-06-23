<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class NavbarThemeTest extends TestCase
{
    public function test_navbar_does_not_render_dark_mode_toggle(): void
    {
        $this->actingAs(User::factory()->make());

        $this->view('layouts.components.navbar', [
            'navbarNotifications' => [
                'count' => 0,
                'items' => [],
                'has_more' => false,
                'next_offset' => 0,
            ],
        ])
            ->assertDontSee('id="theme-toggle"', false)
            ->assertDontSee('theme-toggle', false)
            ->assertDontSee('Ubah mode gelap');
    }

    public function test_sneat_layout_defaults_to_light_mode(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/sneat.blade.php'));

        $this->assertStringContainsString('data-bs-theme="light"', $layout);
    }

    public function test_sneat_theme_script_forces_light_mode(): void
    {
        $script = file_get_contents(resource_path('js/modules/sneat-theme.js'));

        $this->assertStringContainsString("localStorage.setItem('sneat-theme', 'light')", $script);
        $this->assertStringContainsString("root.setAttribute('data-bs-theme', 'light')", $script);
        $this->assertStringContainsString("root.classList.remove('theme-dark')", $script);
        $this->assertStringNotContainsString('prefers-color-scheme: dark', $script);
    }
}
