<?php

namespace App\Modules\Audit\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Reporting\Models\Report;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    private const PER_PAGE = 50;

    /**
     * Paginated audit trail, newest first, optionally filtered by action.
     * Access is granted by the `can:audit.view` middleware on the route.
     */
    public function index(Request $request): Response
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

        return Inertia::render('manage/audit/Index', [
            'entries' => $entries->through(fn (AuditLog $entry): array => [
                'uuid' => $entry->uuid,
                'at' => $entry->created_at->toIso8601String(),
                'action' => $entry->action->label(),
                'actor' => $entry->actor?->name ?? __('audit.actor_kinds.'.$entry->actor_kind->value),
                'subject' => $this->subject($entry),
            ]),
            'actions' => collect(AuditAction::cases())->map(fn (AuditAction $item): array => ['value' => $item->value, 'label' => $item->label()]),
            'selectedAction' => $action?->value,
            'filteredCase' => $case ? [
                'uuid' => $case->uuid,
                'reference' => $case->report->reference,
                'url' => route('manage.cases.show', $case),
            ] : null,
            'urls' => ['index' => route('manage.audit.index')],
        ]);
    }

    /**
     * What the entry is about, by kind and public identifier only: never the
     * content of a report.
     *
     * @return array{kind: string, label: string|null, url: string|null, code: bool}|null
     */
    private function subject(AuditLog $entry): ?array
    {
        $subject = $entry->subject;

        if ($subject === null) {
            return null;
        }

        $kind = __('manage.audit.subjects.'.class_basename($subject));

        return match (true) {
            $subject instanceof CaseFile => ['kind' => $kind, 'label' => $subject->report?->reference, 'url' => route('manage.cases.show', $subject), 'code' => true],
            $subject instanceof Report => ['kind' => $kind, 'label' => $subject->reference, 'url' => null, 'code' => true],
            isset($subject->name) && is_string($subject->name) => ['kind' => $kind, 'label' => $subject->name, 'url' => null, 'code' => false],
            isset($subject->key) => ['kind' => $kind, 'label' => (string) $subject->key, 'url' => null, 'code' => false],
            default => ['kind' => $kind, 'label' => null, 'url' => null, 'code' => false],
        };
    }
}
