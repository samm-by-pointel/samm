<?php

namespace App\Modules\Organizations\Actions;

use App\Modules\Organizations\Contracts\OrganizationResolver;
use App\Modules\Organizations\Events\OrganizationSettingsChanged;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Services\SingleOrganizationResolver;
use App\Modules\Users\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Saves the organization settings (creating the single row on first save),
 * stores or removes the logo on the private disk, and announces the changes.
 */
class UpdateOrganizationSettings
{
    public const LOGO_DISK = 'local';

    public const LOGO_DIRECTORY = 'branding';

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?UploadedFile $logo, bool $removeLogo, User $actor): Organization
    {
        $organization = Organization::query()->first() ?? new Organization;

        $serviceName = array_filter((array) ($data['service_name'] ?? []), fn ($value): bool => is_string($value) && trim($value) !== '');

        $organization->fill([
            'name' => $data['name'],
            'service_name' => $serviceName === [] ? null : $serviceName,
            'primary_color' => isset($data['primary_color']) ? strtoupper($data['primary_color']) : null,
            'secondary_color' => isset($data['secondary_color']) ? strtoupper($data['secondary_color']) : null,
            'neutral_title' => $data['neutral_title'] ?? null,
            'quick_exit_url' => $data['quick_exit_url'] ?? null,
            'default_locale' => $data['default_locale'],
        ]);

        if ($removeLogo || $logo !== null) {
            $this->deleteLogo($organization);
        }

        if ($logo !== null) {
            $path = Storage::disk(self::LOGO_DISK)->putFileAs(self::LOGO_DIRECTORY, $logo, 'logo-'.now()->format('YmdHis').'.'.strtolower($logo->extension()));
            $organization->logo_path = $path ?: null;
        }

        $changed = array_keys($organization->getDirty());
        $organization->save();

        $resolver = app(OrganizationResolver::class);
        if ($resolver instanceof SingleOrganizationResolver) {
            $resolver->forget();
        }

        if ($changed !== []) {
            OrganizationSettingsChanged::dispatch($organization, $changed, $actor);
        }

        return $organization;
    }

    private function deleteLogo(Organization $organization): void
    {
        if ($organization->logo_path && Storage::disk(self::LOGO_DISK)->exists($organization->logo_path)) {
            Storage::disk(self::LOGO_DISK)->delete($organization->logo_path);
        }

        $organization->logo_path = null;
    }
}
