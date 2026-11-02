<?php

namespace Tests\Feature\Organizations;

use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Users\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr', 'samm.quick_exit_url' => 'https://exit.example']);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('organization_admin');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Organisation fictive',
            'service_name' => ['fr' => 'Cellule d\'écoute', 'en' => 'Listening unit'],
            'primary_color' => '#336699',
            'secondary_color' => '#8A3B10',
            'neutral_title' => 'Espace information',
            'quick_exit_url' => 'https://exit.organisation.example',
            'default_locale' => 'en',
        ], $overrides);
    }

    public function test_screen_requires_the_settings_permission(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('case_manager');

        $this->get(route('manage.organization.edit'))->assertRedirect(route('login'));
        $this->actingAs($manager)->get(route('manage.organization.edit'))->assertForbidden();
        $this->actingAs($manager)->put(route('manage.organization.update'), $this->payload())->assertForbidden();
        $this->actingAs($this->admin)->get(route('manage.organization.edit'))->assertOk()->assertSee('Paramètres de l\'organisation');
    }

    public function test_first_save_creates_the_single_row_and_the_public_space_reflects_it(): void
    {
        $this->assertDatabaseCount('organizations', 0);

        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload())
            ->assertRedirect(route('manage.organization.edit'))
            ->assertSessionHas('status', 'Paramètres enregistrés.');

        $organization = Organization::query()->sole();
        $this->assertSame('Organisation fictive', $organization->name);
        $this->assertSame('#336699', $organization->primary_color);
        $this->assertSame('en', $organization->default_locale);

        auth()->logout();
        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Listening unit')
            ->assertSee('<title>Espace information</title>', false)
            ->assertSee('--samm: #336699;', false)
            ->assertSee('data-quick-exit-url="https://exit.organisation.example"', false);

        $this->withSession(['locale' => 'fr'])->get(route('public.home'))->assertSee('<html lang="fr">', false)->assertSee('Cellule d&#039;écoute', false);
    }

    public function test_inaccessible_color_is_refused_with_a_suggestion(): void
    {
        $this->actingAs($this->admin)->from(route('manage.organization.edit'))
            ->put(route('manage.organization.update'), $this->payload(['primary_color' => '#FFFF00']))
            ->assertRedirect(route('manage.organization.edit'))
            ->assertSessionHasErrors('primary_color');

        $message = session('errors')->first('primary_color');
        $this->assertStringContainsString('contraste suffisant', $message);
        $this->assertMatchesRegularExpression('/#[0-9A-F]{6}/', $message);

        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload(['secondary_color' => 'rouge']))
            ->assertSessionHasErrors(['secondary_color' => 'La couleur doit être au format #RRGGBB.']);

        $this->assertDatabaseCount('organizations', 0);
    }

    public function test_quick_exit_url_must_use_https_and_locale_must_be_known(): void
    {
        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload(['quick_exit_url' => 'http://exit.example']))
            ->assertSessionHasErrors('quick_exit_url');

        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload(['default_locale' => 'wo']))
            ->assertSessionHasErrors('default_locale');
    }

    public function test_changes_are_audited_with_the_list_of_fields(): void
    {
        Organization::factory()->create(['name' => 'Avant']);

        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload(['name' => 'Après', 'primary_color' => null, 'secondary_color' => null, 'neutral_title' => null, 'quick_exit_url' => null, 'service_name' => [], 'default_locale' => 'fr']))
            ->assertRedirect();

        $entry = AuditLog::query()->where('action', AuditAction::SettingsUpdated->value)->sole();
        $this->assertTrue($entry->actor->is($this->admin));
        $this->assertSame(['name'], $entry->context['fields']);
        $this->assertDatabaseCount('organizations', 1);
    }

    public function test_navigation_link_follows_the_permission(): void
    {
        $this->actingAs($this->admin)->get(route('manage.dashboard'))->assertSee(route('manage.organization.edit'));

        $manager = User::factory()->create();
        $manager->assignRole('case_manager');
        $this->actingAs($manager)->get(route('manage.dashboard'))->assertDontSee(route('manage.organization.edit'));
    }
}
