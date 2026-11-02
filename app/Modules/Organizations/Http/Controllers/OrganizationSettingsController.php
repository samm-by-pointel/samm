<?php

namespace App\Modules\Organizations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Organizations\Actions\UpdateOrganizationSettings;
use App\Modules\Organizations\Contracts\OrganizationResolver;
use App\Modules\Organizations\Models\EmergencyContact;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Rules\AccessibleBrandColor;
use App\Modules\Reporting\Models\ReportCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Organization settings screen (demonstration A). Protected by `can:settings.manage`.
 */
class OrganizationSettingsController extends Controller
{
    public function edit(OrganizationResolver $organizations): Response
    {
        $organization = $organizations->current() ?? new Organization;

        return Inertia::render('manage/organization/Edit', [
            'organization' => [
                'name' => $organization->name ?? '',
                'serviceName' => (object) ($organization->service_name ?? []),
                'primaryColor' => $organization->primary_color ?? '',
                'secondaryColor' => $organization->secondary_color ?? '',
                'neutralTitle' => $organization->neutral_title ?? '',
                'quickExitUrl' => $organization->quick_exit_url ?? '',
                'defaultLocale' => $organization->default_locale ?? 'fr',
                'logoUrl' => $organization->hasLogo() ? $organization->logoUrl() : null,
            ],
            'contacts' => EmergencyContact::query()->ordered()->get()->map(fn (EmergencyContact $contact): array => [
                'uuid' => $contact->uuid,
                'label' => (object) ($contact->label ?? []),
                'phoneNumber' => $contact->phone_number,
                'position' => $contact->position,
                'isActive' => (bool) $contact->is_active,
                'updateUrl' => route('manage.organization.emergency.update', $contact),
                'deleteUrl' => route('manage.organization.emergency.destroy', $contact),
            ]),
            'categories' => ReportCategory::query()->ordered()->get()->map(fn (ReportCategory $category): array => [
                'uuid' => $category->uuid,
                'name' => (object) ($category->name ?? []),
                'description' => (object) ($category->description ?? []),
                'position' => $category->position,
                'isActive' => (bool) $category->is_active,
                'updateUrl' => route('manage.organization.categories.update', $category),
            ]),
            'locales' => collect(config('samm.locales'))->map(fn (string $name, string $code): array => ['code' => $code, 'name' => $name])->values(),
            'defaults' => [
                'primaryColor' => '#0E5A55',
                'secondaryColor' => '#A84B14',
                'quickExitUrl' => config('samm.quick_exit_url'),
            ],
            'urls' => [
                'update' => route('manage.organization.update'),
                'contactStore' => route('manage.organization.emergency.store'),
                'categoryStore' => route('manage.organization.categories.store'),
            ],
        ]);
    }

    public function update(Request $request, UpdateOrganizationSettings $update): RedirectResponse
    {
        $locales = array_keys(config('samm.locales'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'service_name' => ['nullable', 'array'],
            'service_name.*' => ['nullable', 'string', 'max:120'],
            'primary_color' => ['nullable', new AccessibleBrandColor],
            'secondary_color' => ['nullable', new AccessibleBrandColor],
            'neutral_title' => ['nullable', 'string', 'max:80'],
            'quick_exit_url' => ['nullable', 'url', 'starts_with:https://', 'max:2048'],
            'default_locale' => ['required', Rule::in($locales)],
            // Raster images only: an SVG can embed scripts, so it is refused on purpose.
            'logo' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $update->handle(
            $validated,
            $request->file('logo'),
            (bool) ($validated['remove_logo'] ?? false),
            $request->user(),
        );

        return redirect()->route('manage.organization.edit')->with('status', __('manage.organization.saved'));
    }
}
