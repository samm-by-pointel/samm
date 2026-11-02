<?php

namespace App\Modules\Organizations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Organizations\Actions\UpdateOrganizationSettings;
use App\Modules\Organizations\Contracts\OrganizationResolver;
use App\Modules\Organizations\Models\EmergencyContact;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Rules\AccessibleBrandColor;
use App\Modules\Reporting\Models\ReportCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Organization settings screen (demonstration A). Protected by `can:settings.manage`.
 */
class OrganizationSettingsController extends Controller
{
    public function edit(OrganizationResolver $organizations): View
    {
        return view('manage.organization.edit', [
            'organization' => $organizations->current() ?? new Organization,
            'contacts' => EmergencyContact::query()->ordered()->get(),
            'categories' => ReportCategory::query()->ordered()->get(),
            'locales' => config('samm.locales'),
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
