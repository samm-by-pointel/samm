<?php

namespace Tests\Feature\Organizations;

use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Services\BrandingCss;
use Tests\TestCase;

class BrandingCssTest extends TestCase
{
    public function test_valid_colors_produce_the_customizable_tokens_only(): void
    {
        $organization = Organization::factory()->withBranding('#0E5A55', '#A84B14')->make();

        $css = (new BrandingCss)->for($organization);

        $this->assertStringContainsString('--samm: #0e5a55;', $css);
        $this->assertStringContainsString('--samm-rgb: 14, 90, 85;', $css);
        $this->assertStringContainsString('--samm-soft: #dee4df;', $css);
        $this->assertStringContainsString('--terre: #a84b14;', $css);
        $this->assertStringContainsString('--terre-rgb: 168, 75, 20;', $css);
        $this->assertStringContainsString('[data-bs-theme="dark"]', $css);

        foreach (['--urgent', '--attente', '--info', '--succes', '--ink', '--focus-ring'] as $lockedToken) {
            $this->assertStringNotContainsString($lockedToken, $css, "Le jeton {$lockedToken} est verrouillé.");
        }
    }

    public function test_nothing_is_emitted_without_colors_or_without_organization(): void
    {
        $service = new BrandingCss;

        $this->assertSame('', $service->for(null));
        $this->assertSame('', $service->for(Organization::factory()->make()));
    }

    public function test_invalid_colors_are_ignored(): void
    {
        $service = new BrandingCss;

        $this->assertSame('', $service->for(Organization::factory()->make(['primary_color' => 'red'])));
        $this->assertSame('', $service->for(Organization::factory()->make(['primary_color' => '#0E5A55; } body { display: none'])));

        $css = $service->for(Organization::factory()->make(['primary_color' => '#0E5A55', 'secondary_color' => '#GGGGGG']));

        $this->assertStringContainsString('--samm: #0e5a55;', $css);
        $this->assertStringNotContainsString('--terre', $css);
    }

    public function test_soft_tint_is_a_twelve_percent_blend_over_the_light_surface(): void
    {
        $this->assertSame('#dee4df', (new BrandingCss)->blend('#0e5a55', '#FAF7F2', 0.12));
        $this->assertSame('#ffffff', (new BrandingCss)->blend('#000000', '#ffffff', 0));
        $this->assertSame('#000000', (new BrandingCss)->blend('#000000', '#ffffff', 1));
    }
}
