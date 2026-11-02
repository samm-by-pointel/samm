<?php

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'en')
                ->where('translations.public.quick_exit.label', 'Leave quickly'));
    }

    public function test_switcher_offers_each_language_with_its_flag(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('locales', 2)
                ->where('locales.0', ['code' => 'fr', 'name' => 'Français', 'flag' => 'fr'])
                ->where('locales.1', ['code' => 'en', 'name' => 'English', 'flag' => 'gb']));
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
