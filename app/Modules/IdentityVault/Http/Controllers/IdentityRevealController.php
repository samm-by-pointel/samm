<?php

namespace App\Modules\IdentityVault\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Cases\Services\CasePageData;
use App\Modules\IdentityVault\Events\IdentityRevealed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Explicit, justified and audited access to the identity of the reporting
 * person (charter §12.10). The identity is rendered in this single response
 * and never kept in session: reloading the page masks it again.
 */
class IdentityRevealController extends Controller
{
    public function store(Request $request, CaseFile $case, CasePageData $pageData): View
    {
        Gate::authorize('viewIdentity', $case);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:190'],
        ]);

        $identity = $case->report->identity()->firstOrFail();

        IdentityRevealed::dispatch($case, $request->user(), $validated['reason']);

        return view('manage.cases.show', $pageData->for($case, $request->user(), $identity));
    }
}
