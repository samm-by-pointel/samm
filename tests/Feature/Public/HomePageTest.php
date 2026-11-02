<?php

namespace Tests\Feature\Public;

use App\Modules\Organizations\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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
            ->assertSee('<title>Sàmm</title>', false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('public/Home')
                ->where('locale', 'fr')
                ->where('site.name', 'Sàmm')
                ->where('site.quickExitUrl', 'https://exit.example')
                ->where('translations.public.quick_exit.label', 'Quitter rapidement')
                ->where('translations.public.home.report_cta', 'Faire un signalement'));
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
            ->assertSee('<title>Espace information</title>', false)
            ->assertInertia(fn (Assert $page) => $page
                ->where('site.name', "Cellule d'écoute")
                ->where('site.neutralTitle', 'Espace information')
                ->where('site.quickExitUrl', 'https://exit.organisation.example'));
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
