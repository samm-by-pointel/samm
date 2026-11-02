<?php

namespace App\Modules\Cases\Services;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\Cases\Enums\CasePriority;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Cases\Models\InternalNote;
use App\Modules\Messaging\Models\Message;
use App\Modules\Privacy\Enums\ConsentPurpose;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Enums\CaseStatus;
use App\Modules\Workflow\Models\CaseStatusHistory;
use App\Modules\Workflow\Services\StatusTransitions;
use Illuminate\Support\Collection;

/**
 * Everything the case page needs, assembled once and listed field by field:
 * these props end up in the page source, so nothing leaves the server unless
 * it is named here. The identity is never part of them (see IdentityRevealController).
 */
class CasePageData
{
    public function __construct(private readonly StatusTransitions $transitions) {}

    /**
     * @return array<string, mixed>
     */
    public function for(CaseFile $case, User $viewer): array
    {
        $case->loadMissing(['report.category', 'report.consents', 'assignee', 'statusHistories.changedBy', 'report.conversation.messages', 'internalNotes.author']);

        $report = $case->report;

        $can = [
            'assign' => $viewer->can('assign', $case),
            'updateStatus' => $viewer->can('updateStatus', $case),
            'close' => $viewer->can('close', $case),
            'changePriority' => $viewer->can('changePriority', $case),
            'addNote' => $viewer->can('addNote', $case),
            'reply' => $viewer->can('reply', $case),
        ];

        return [
            'case' => [
                'reference' => $report->reference,
                'status' => $this->status($case->status),
                'priority' => ['value' => $case->priority->value, 'label' => $case->priority->label(), 'isHigh' => $case->priority->isHigh()],
                'category' => $report->category->name(),
                'confidentiality' => $report->confidentiality_level->label(),
                'reporter' => $report->displayName(),
                'assignee' => $case->assignee?->name,
                'assigneeUuid' => $case->assignee?->uuid,
                'submittedAt' => $report->submitted_at?->toIso8601String(),
                'lastActivityAt' => $case->last_activity_at?->toIso8601String(),
                'immediateDanger' => (bool) $report->immediate_danger,
            ],
            'report' => [
                'description' => $report->description,
                'factsDate' => $report->facts_date?->toDateString(),
                'factsPeriod' => $report->facts_period,
                'location' => $report->location,
                'peopleInvolved' => $report->people_involved,
                'witnesses' => $report->witnesses,
                'isRecurring' => $report->is_recurring,
                'isOngoing' => $report->is_ongoing,
            ],
            'identity' => [
                'allowed' => $report->confidentiality_level->allowsIdentity(),
                'level' => $report->confidentiality_level->label(),
                'canView' => $viewer->can('viewIdentity', $case),
                'consentGranted' => $report->consents->contains(fn ($consent): bool => $consent->purpose === ConsentPurpose::IdentityDisclosure && $consent->granted),
                'revealUrl' => route('manage.cases.identity.reveal', $case),
            ],
            'histories' => $case->statusHistories->map(fn (CaseStatusHistory $history): array => [
                'to' => $history->to_status->label(),
                'from' => $history->from_status?->label(),
                'at' => $history->created_at->toIso8601String(),
                'by' => $history->changedBy?->name ?? __('audit.actor_kinds.system'),
                'comment' => $history->comment,
            ])->values(),
            'notes' => $case->internalNotes->map(fn (InternalNote $note): array => [
                'body' => $note->body,
                'at' => $note->created_at->toIso8601String(),
                'author' => $note->author?->name ?? __('audit.actor_kinds.system'),
            ])->values(),
            'messages' => ($report->conversation?->messages ?? collect())->map(fn (Message $message): array => [
                'body' => $message->body,
                'fromReporter' => $message->isFromReporter(),
                'author' => $message->author_type->staffLabel($report),
                'createdAt' => $message->created_at->toIso8601String(),
            ])->values(),
            'can' => $can,
            'allowedTransitions' => collect($this->transitions->allowedFrom($case->status))
                ->filter(fn (CaseStatus $status): bool => $status->isOpen() || $can['close'])
                ->map(fn (CaseStatus $status): array => ['value' => $status->value, 'label' => $status->label()])
                ->values(),
            'priorities' => collect(CasePriority::cases())->map(fn (CasePriority $priority): array => ['value' => $priority->value, 'label' => $priority->label()]),
            'assignableUsers' => $can['assign'] ? $this->assignableUsers()->map(fn (User $user): array => ['value' => $user->uuid, 'label' => $user->name])->values() : [],
            'urls' => [
                'back' => route('manage.cases.index'),
                'audit' => $viewer->can('audit.view') ? route('manage.audit.index', ['case' => $case->uuid]) : null,
                'assign' => route('manage.cases.assignment.store', $case),
                'status' => route('manage.cases.status.store', $case),
                'priority' => route('manage.cases.priority.store', $case),
                'note' => route('manage.cases.notes.store', $case),
                'message' => route('manage.cases.messages.store', $case),
            ],
        ];
    }

    /**
     * @return array{value: string, label: string}
     */
    public function status(CaseStatus $status): array
    {
        return ['value' => $status->value, 'label' => $status->label()];
    }

    /**
     * Active accounts whose role may view assigned cases.
     *
     * @return Collection<int, User>
     */
    private function assignableUsers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles.permissions', fn ($query) => $query->where('permission', Permission::ViewAssignedCases->value))
            ->orderBy('name')
            ->get();
    }
}
