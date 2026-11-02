<?php

namespace App\Modules\Audit\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Models\CaseFile;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    private const PER_PAGE = 50;

    /**
     * Paginated audit trail, newest first, optionally filtered by action.
     * Access is granted by the `can:audit.view` middleware on the route.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', Rule::enum(AuditAction::class)],
            'case' => ['nullable', 'string', Rule::exists('cases', 'uuid')],
        ]);

        $action = isset($validated['action']) ? AuditAction::from($validated['action']) : null;
        $case = isset($validated['case']) ? CaseFile::query()->with('report')->where('uuid', $validated['case'])->firstOrFail() : null;

        $entries = AuditLog::query()
            ->with(['actor', 'subject' => fn (MorphTo $subject) => $subject->morphWith([CaseFile::class => ['report']])])
            ->when($action, fn ($query) => $query->where('action', $action->value))
            ->when($case, fn ($query) => $query->where(function ($subject) use ($case): void {
                $subject->where(fn ($q) => $q->where('subject_type', $case->getMorphClass())->where('subject_id', $case->getKey()))
                    ->orWhere(fn ($q) => $q->where('subject_type', $case->report->getMorphClass())->where('subject_id', $case->report->getKey()));
            }))
            ->latest('created_at')
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('manage.audit.index', [
            'entries' => $entries,
            'actions' => AuditAction::cases(),
            'selectedAction' => $action,
            'filteredCase' => $case,
        ]);
    }
}
