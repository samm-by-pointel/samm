<?php

namespace App\Modules\IdentityVault\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\IdentityVault\Events\IdentityRevealed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Explicit, justified and audited access to the identity of the reporting
 * person (charter §12.10). The identity is sent in this single JSON response
 * and only kept in the page's memory: it is never stored in the session nor in
 * the browser history, and reloading the page masks it again.
 */
class IdentityRevealController extends Controller
{
    public function store(Request $request, CaseFile $case): JsonResponse
    {
        Gate::authorize('viewIdentity', $case);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:190'],
        ]);

        $identity = $case->report->identity()->firstOrFail();

        IdentityRevealed::dispatch($case, $request->user(), $validated['reason']);

        return response()->json([
            'identity' => [
                'fullName' => $identity->full_name,
                'email' => $identity->email,
                'phone' => $identity->phone,
            ],
        ])->header('Cache-Control', 'no-store');
    }
}
