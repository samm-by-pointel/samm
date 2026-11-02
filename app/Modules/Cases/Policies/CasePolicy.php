<?php

namespace App\Modules\Cases\Policies;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Privacy\Enums\ConsentPurpose;
use App\Modules\Reporting\Enums\ConfidentialityLevel;
use App\Modules\Users\Models\User;

/**
 * Who may see a case (specification §9): permission and assignment together.
 * The list uses the same rule through CaseFile::visibleTo(), so a case that is
 * invisible here is invisible everywhere.
 */
class CasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ViewCaseList) || $user->hasPermission(Permission::ViewAssignedCases);
    }

    public function view(User $user, CaseFile $case): bool
    {
        return $case->isAssignedTo($user)
            ? $user->hasPermission(Permission::ViewAssignedCases)
            : $user->hasPermission(Permission::ViewUnassignedCases);
    }

    /**
     * Assigning an unassigned case and reassigning an assigned one are distinct permissions.
     */
    public function assign(User $user, CaseFile $case): bool
    {
        if (! $this->view($user, $case)) {
            return false;
        }

        return $case->isAssigned()
            ? $user->hasPermission(Permission::ReassignCases)
            : $user->hasPermission(Permission::AssignCases);
    }

    public function updateStatus(User $user, CaseFile $case): bool
    {
        return $this->view($user, $case) && $user->hasPermission(Permission::UpdateCaseStatus);
    }

    /**
     * Closing or archiving needs the closing permission on top of the status one.
     */
    public function close(User $user, CaseFile $case): bool
    {
        return $this->updateStatus($user, $case) && $user->hasPermission(Permission::CloseCases);
    }

    public function changePriority(User $user, CaseFile $case): bool
    {
        return $this->updateStatus($user, $case);
    }

    public function addNote(User $user, CaseFile $case): bool
    {
        return $this->view($user, $case) && $user->hasPermission(Permission::CreateInternalNotes);
    }

    public function reply(User $user, CaseFile $case): bool
    {
        return $this->view($user, $case) && $user->hasPermission(Permission::ReplyToMessages);
    }

    /**
     * The identity is readable only with the dedicated permission, on a confidential
     * report whose author agreed to its disclosure.
     */
    public function viewIdentity(User $user, CaseFile $case): bool
    {
        if (! $this->view($user, $case) || ! $user->hasPermission(Permission::ViewIdentities)) {
            return false;
        }

        $report = $case->report;

        return $report->confidentiality_level === ConfidentialityLevel::Confidential
            && $report->consents()->where('purpose', ConsentPurpose::IdentityDisclosure->value)->where('granted', true)->exists();
    }
}
