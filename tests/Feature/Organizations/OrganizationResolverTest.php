<?php

namespace Tests\Feature\Organizations;

use App\Modules\Organizations\Contracts\OrganizationResolver;
use App\Modules\Organizations\Models\Organization;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolver_returns_the_single_organization(): void
    {
        $organization = Organization::factory()->create(['name' => 'Organisation fictive']);

        $resolved = $this->app->make(OrganizationResolver::class)->current();

        $this->assertNotNull($resolved);
        $this->assertTrue($organization->is($resolved));
        $this->assertSame('Organisation fictive', $resolved->name);
    }

    public function test_resolver_returns_null_when_the_installation_is_not_configured(): void
    {
        $this->assertNull($this->app->make(OrganizationResolver::class)->current());
    }

    public function test_database_refuses_a_second_organization(): void
    {
        Organization::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        Organization::factory()->create();
    }

    public function test_organization_settings_fall_back_to_configuration(): void
    {
        config(['samm.quick_exit_url' => 'https://exit.example', 'samm.neutral_title' => null]);

        $plain = Organization::factory()->make();
        $custom = Organization::factory()->withQuickExitUrl('https://exit.organisation.example')->withNeutralTitle('Espace information')->make();

        $this->assertSame('https://exit.example', $plain->effectiveQuickExitUrl());
        $this->assertNull($plain->effectiveNeutralTitle());
        $this->assertSame('https://exit.organisation.example', $custom->effectiveQuickExitUrl());
        $this->assertSame('Espace information', $custom->effectiveNeutralTitle());
    }

    public function test_service_name_uses_the_organization_default_locale_as_fallback(): void
    {
        $organization = Organization::factory()->make([
            'default_locale' => 'en',
            'service_name' => ['en' => 'Listening unit', 'fr' => 'Cellule d\'écoute'],
        ]);

        app()->setLocale('wo');

        $this->assertSame('Listening unit', $organization->serviceName());
    }
}
