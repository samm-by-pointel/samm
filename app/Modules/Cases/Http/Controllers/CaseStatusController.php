<?php

namespace App\Modules\Cases\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Workflow\Actions\ChangeCaseStatus;
use App\Modules\Workflow\Enums\CaseStatus;
use App\Modules\Workflow\Services\StatusTransitions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CaseStatusController extends Controller
{
    public function store(Request $request, CaseFile $case, StatusTransitions $transitions, ChangeCaseStatus $changeStatus): RedirectResponse
    {
        Gate::authorize('updateStatus', $case);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(CaseStatus::class)],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $to = CaseStatus::from($validated['status']);
        $comment = $validated['comment'] ?? null;

        if (! $transitions->canTransition($case->status, $to)) {
            throw ValidationException::withMessages(['status' => __('manage.cases.actions.errors.transition_refused')]);
        }

        if (! $to->isOpen()) {
            Gate::authorize('close', $case);
        }

        if ($to->isClosed() && mb_strlen(trim((string) $comment)) < 10) {
            throw ValidationException::withMessages(['comment' => __('manage.cases.actions.errors.closure_reason_required')]);
        }

        $changeStatus->handle($case, $to, $request->user(), $comment !== null && trim($comment) !== '' ? trim($comment) : null);

        return redirect()->route('manage.cases.show', $case)
            ->with('status', __('manage.cases.actions.confirmations.status_changed', ['status' => $to->label()]));
    }
}
