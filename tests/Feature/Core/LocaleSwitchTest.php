<?php

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config(['app.locale' => 'fr']);
    }

    public function test_visitor_can_switch_language_without_an_account(): void
    {
        $this->from(route('public.home'))
            ->post(route('locale.update'), ['locale' => 'en'])
            ->assertRedirect(route('public.home'))
            ->assertSessionHas('locale', 'en');

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Leave quickly');
    }

    public function test_switcher_shows_a_flag_next_to_each_label(): void
    {
        $html = $this->get(route('public.home'))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'class="samm-locale-flag"'), 'Un drapeau par langue.');
        $this->assertStringContainsString('<span>Français</span>', $html);
        $this->assertStringContainsString('<span>English</span>', $html);
        $this->assertMatchesRegularExpression('/<svg class="samm-locale-flag"[^>]*aria-hidden="true"/', $html);
    }

    public function test_unknown_language_is_rejected(): void
    {
        $this->from(route('public.home'))
            ->post(route('locale.update'), ['locale' => 'xx'])
            ->assertRedirect(route('public.home'))
            ->assertSessionHasErrors(['locale' => 'La valeur choisie pour langue n\'est pas valide.'])
            ->assertSessionMissing('locale');
    }

    public function test_validation_messages_are_displayed_in_french(): void
    {
        $this->from(route('public.home'))
            ->post(route('locale.update'), [])
            ->assertRedirect(route('public.home'))
            ->assertSessionHasErrors(['locale' => 'Le champ langue est obligatoire.']);
    }
}
