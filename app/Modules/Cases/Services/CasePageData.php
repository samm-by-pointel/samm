<?php

namespace App\Modules\Cases\Services;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\IdentityVault\Models\ReportIdentity;
use App\Modules\Privacy\Enums\ConsentPurpose;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Enums\CaseStatus;
use App\Modules\Workflow\Services\StatusTransitions;
use Illuminate\Support\Collection;

/**
 * Everything the case page needs, assembled once so that the view never
 * queries the database and the identity is never loaded by accident.
 */
class CasePageData
{
    public function __construct(private readonly StatusTransitions $transitions) {}

    /**
     * @return array<string, mixed>
     */
    public function for(CaseFile $case, User $viewer, ?ReportIdentity $revealedIdentity = null): array
    {
        $case->loadMissing(['report.category', 'report.consents', 'assignee', 'statusHistories.changedBy', 'report.conversation.messages', 'internalNotes.author']);

        $report = $case->report;

        return [
            'case' => $case,
            'report' => $report,
            'messages' => $report->conversation?->messages ?? collect(),
            'histories' => $case->statusHistories,
            'canViewIdentity' => $viewer->can('viewIdentity', $case),
            'identityConsentGranted' => $report->consents->contains(fn ($consent): bool => $consent->purpose === ConsentPurpose::IdentityDisclosure && $consent->granted),
            'revealedIdentity' => $revealedIdentity,
            'notes' => $case->internalNotes,
            'can' => $can = [
                'assign' => $viewer->can('assign', $case),
                'updateStatus' => $viewer->can('updateStatus', $case),
                'close' => $viewer->can('close', $case),
                'changePriority' => $viewer->can('changePriority', $case),
                'addNote' => $viewer->can('addNote', $case),
                'reply' => $viewer->can('reply', $case),
            ],
            'allowedTransitions' => array_values(array_filter(
                $this->transitions->allowedFrom($case->status),
                fn (CaseStatus $status): bool => $status->isOpen() || $can['close'],
            )),
            'assignableUsers' => $can['assign'] ? $this->assignableUsers() : collect(),
        ];
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
