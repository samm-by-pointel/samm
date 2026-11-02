<?php

namespace App\Modules\Organizations\Models;

use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Core\Concerns\HasTranslations;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The single organization served by this installation.
 *
 * @property string $name
 * @property array<string, string>|null $service_name
 * @property string|null $logo_path
 * @property string|null $primary_color
 * @property string|null $secondary_color
 * @property string|null $neutral_title
 * @property string|null $quick_exit_url
 * @property string $default_locale
 */
#[Fillable(['name', 'service_name', 'logo_path', 'primary_color', 'secondary_color', 'neutral_title', 'quick_exit_url', 'default_locale'])]
#[UseFactory(OrganizationFactory::class)]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory, HasPublicIdentifier;

    use HasTranslations {
        translationFallbackLocales as protected defaultTranslationFallbackLocales;
    }

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'default_locale' => 'fr',
        'single_row' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_name' => 'array',
            'single_row' => 'boolean',
        ];
    }

    /**
     * Name of the reporting service shown to the public, in the current locale.
     */
    public function serviceName(): ?string
    {
        return $this->translate('service_name');
    }

    /**
     * Quick exit destination: the organization's own, or the application default.
     */
    public function effectiveQuickExitUrl(): string
    {
        return $this->quick_exit_url ?: config('samm.quick_exit_url');
    }

    /**
     * Neutral browser tab title: the organization's own, or the application default (may be null).
     */
    public function effectiveNeutralTitle(): ?string
    {
        return $this->neutral_title ?: config('samm.neutral_title');
    }

    public function hasLogo(): bool
    {
        return $this->logo_path !== null;
    }

    /**
     * Public URL of the logo, served by a route from the private disk; the
     * timestamp defeats browser caches after a change.
     */
    public function logoUrl(): ?string
    {
        return $this->hasLogo() ? route('organization.logo', ['v' => $this->updated_at?->timestamp]) : null;
    }

    /**
     * @return array<int, string>
     */
    protected function translationFallbackLocales(): array
    {
        return array_values(array_unique(array_filter([
            $this->default_locale,
            ...$this->defaultTranslationFallbackLocales(),
        ])));
    }
}
