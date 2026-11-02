<?php

namespace App\Modules\Organizations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Organizations\Actions\UpdateOrganizationSettings;
use App\Modules\Organizations\Contracts\OrganizationResolver;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves the organization logo from the private disk: no symbolic link into
 * public/ is needed at installation time.
 */
class OrganizationLogoController extends Controller
{
    public function show(OrganizationResolver $organizations): StreamedResponse
    {
        $path = $organizations->current()?->logo_path;

        abort_unless($path && Storage::disk(UpdateOrganizationSettings::LOGO_DISK)->exists($path), 404);

        return Storage::disk(UpdateOrganizationSettings::LOGO_DISK)->response($path, null, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
