<?php

namespace App\Modules\AccessControl\Enums;

/**
 * Every permission the application checks (specification §9).
 *
 * A permission exists only if the code verifies it somewhere: roles are sets
 * of these values, never absolute authorizations.
 */
enum Permission: string
{
    case ViewCaseList = 'cases.view_list';
    case ViewAssignedCases = 'cases.view_assigned';
    case ViewUnassignedCases = 'cases.view_unassigned';
    case ViewIdentities = 'identities.view';
    case ViewEvidence = 'evidence.view';
    case DownloadEvidence = 'evidence.download';
    case ReplyToMessages = 'messages.reply';
    case CreateInternalNotes = 'notes.create';
    case UpdateCaseStatus = 'cases.update_status';
    case AssignCases = 'cases.assign';
    case ReassignCases = 'cases.reassign';
    case CloseCases = 'cases.close';
    case ViewAuditLog = 'audit.view';
    case ViewAnalytics = 'analytics.view';
    case ManageUsers = 'users.manage';
    case ManageSettings = 'settings.manage';

    /**
     * Human-readable label in the current locale.
     */
    public function label(): string
    {
        return __('access_control.permissions.'.$this->value);
    }

    /**
     * Permissions that give access to report content, identities or evidence.
     *
     * @return array<int, self>
     */
    public static function contentRelated(): array
    {
        return [
            self::ViewCaseList,
            self::ViewAssignedCases,
            self::ViewUnassignedCases,
            self::ViewIdentities,
            self::ViewEvidence,
            self::DownloadEvidence,
            self::ReplyToMessages,
            self::CreateInternalNotes,
            self::UpdateCaseStatus,
            self::AssignCases,
            self::ReassignCases,
            self::CloseCases,
        ];
    }
}
