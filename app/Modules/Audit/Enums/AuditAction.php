<?php

namespace App\Modules\Audit\Enums;

/**
 * Sensitive actions recorded in the audit log. Later lots add their own
 * (case viewed, identity revealed, evidence downloaded, status changed…).
 */
enum AuditAction: string
{
    case LoginSucceeded = 'auth.login_succeeded';
    case LoginFailed = 'auth.login_failed';
    case LockedOut = 'auth.locked_out';
    case Logout = 'auth.logout';
    case RoleAssigned = 'roles.assigned';
    case RoleRemoved = 'roles.removed';
    case RolePermissionsChanged = 'roles.permissions_changed';
    case CaseStatusChanged = 'cases.status_changed';
    case CaseViewed = 'cases.viewed';
    case IdentityViewed = 'identities.viewed';
    case CaseAssigned = 'cases.assigned';
    case CaseReassigned = 'cases.reassigned';
    case CaseUnassigned = 'cases.unassigned';
    case CasePriorityChanged = 'cases.priority_changed';
    case SettingsUpdated = 'settings.updated';
    case EmergencyContactChanged = 'settings.emergency_contact_changed';
    case CategoryChanged = 'settings.category_changed';
    case ReportSubmitted = 'reports.submitted';
    case TrackingAccessSucceeded = 'tracking.access_succeeded';
    case TrackingAccessFailed = 'tracking.access_failed';

    public function label(): string
    {
        return __('audit.actions.'.$this->value);
    }
}
