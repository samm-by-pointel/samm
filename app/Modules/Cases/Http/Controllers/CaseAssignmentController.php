<?php

namespace App\Modules\Cases\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AccessControl\Enums\Permission;
use App\Modules\Cases\Actions\AssignCase;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CaseAssignmentController extends Controller
{
    /**
     * Assign, reassign or unassign (empty value). Only active accounts whose
     * role may view assigned cases are eligible.
     */
    public function store(Request $request, CaseFile $case, AssignCase $assignCase): RedirectResponse
    {
        Gate::authorize('assign', $case);

        $validated = $request->validate([
            'assignee' => ['nullable', 'string', Rule::exists('users', 'uuid')->where('is_active', true)],
        ]);

        $assignee = isset($validated['assignee']) ? User::query()->where('uuid', $validated['assignee'])->firstOrFail() : null;

        if ($assignee !== null && ! $assignee->hasPermission(Permission::ViewAssignedCases)) {
            throw ValidationException::withMessages(['assignee' => __('manage.cases.actions.errors.assignee_not_eligible')]);
        }

        $assignCase->handle($case, $assignee, $request->user());

        return redirect()->route('manage.cases.show', $case)
            ->with('status', $assignee ? __('manage.cases.actions.confirmations.assigned', ['name' => $assignee->name]) : __('manage.cases.actions.confirmations.unassigned'));
    }
}
