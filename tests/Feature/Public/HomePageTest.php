<?php

namespace Tests\Feature\Public;

use App\Modules\Organizations\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config([
            'app.name' => 'Sàmm',
            'app.locale' => 'fr',
            'samm.quick_exit_url' => 'https://exit.example',
            'samm.neutral_title' => null,
        ]);
    }

    public function test_home_page_is_rendered_in_french_with_quick_exit_button(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertOk()
            ->assertSee('<html lang="fr">', false)
            ->assertSee('Quitter rapidement')
            ->assertSee('Sàmm')
            ->assertSee('Faire un signalement')
            ->assertSee('data-quick-exit-url="https://exit.example"', false)
            ->assertSee('<title>Sàmm</title>', false);
    }

    public function test_home_page_loads_no_third_party_resource(): void
    {
        $html = $this->get(route('public.home'))->getContent();

        foreach (['fonts.googleapis.com', 'fonts.gstatic.com', 'fonts.bunny.net', 'cdn.jsdelivr.net', 'cdnjs.cloudflare.com', 'googletagmanager', 'google-analytics'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $html, "Ressource tierce interdite : {$forbidden}");
        }
    }

    public function test_neutral_title_replaces_application_name_in_browser_tab(): void
    {
        config(['samm.neutral_title' => 'Espace information']);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('<title>Espace information</title>', false)
            ->assertDontSee('<title>Sàmm', false);
    }

    public function test_organization_settings_take_precedence_over_configuration(): void
    {
        Organization::factory()
            ->withServiceName()
            ->withNeutralTitle('Espace information')
            ->withQuickExitUrl('https://exit.organisation.example')
            ->create();

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('Cellule d&#039;écoute', false)
            ->assertSee('<title>Espace information</title>', false)
            ->assertSee('data-quick-exit-url="https://exit.organisation.example"', false)
            ->assertDontSee('data-quick-exit-url="https://exit.example"', false);
    }

    public function test_organization_colors_are_injected_as_css_variables(): void
    {
        Organization::factory()->withBranding('#336699', '#996633')->create();

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('--samm: #336699;', false)
            ->assertSee('--terre: #996633;', false);
    }

    public function test_home_page_has_no_branding_style_without_organization(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertDontSee('--samm:', false);
    }

    public function test_favicon_is_present_and_not_empty(): void
    {
        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));
    }
}
