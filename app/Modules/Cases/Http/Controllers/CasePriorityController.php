<?php

namespace App\Modules\Cases\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cases\Actions\ChangeCasePriority;
use App\Modules\Cases\Enums\CasePriority;
use App\Modules\Cases\Models\CaseFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CasePriorityController extends Controller
{
    public function store(Request $request, CaseFile $case, ChangeCasePriority $changePriority): RedirectResponse
    {
        Gate::authorize('changePriority', $case);

        $validated = $request->validate([
            'priority' => ['required', Rule::enum(CasePriority::class)],
        ]);

        $priority = CasePriority::from($validated['priority']);
        $changePriority->handle($case, $priority, $request->user());

        return redirect()->route('manage.cases.show', $case)
            ->with('status', __('manage.cases.actions.confirmations.priority_changed', ['priority' => $priority->label()]));
    }
}
