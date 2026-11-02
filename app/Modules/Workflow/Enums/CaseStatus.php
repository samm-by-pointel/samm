<?php

namespace App\Modules\Workflow\Enums;

/**
 * The eight case statuses of the specification (§8).
 *
 * Badge colors are locked for every organization (charter §12.7, see the
 * StatusBadge component) and "Archived" is never shown to the public: the
 * reporting person sees "Closed".
 */
enum CaseStatus: string
{
    case New = 'new';
    case ToQualify = 'to_qualify';
    case InProgress = 'in_progress';
    case AwaitingInformation = 'awaiting_information';
    case Referred = 'referred';
    case Resolving = 'resolving';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return __('workflow.statuses.'.$this->value);
    }

    /**
     * Label shown in the public tracking space: an archived case reads as closed.
     */
    public function publicLabel(): string
    {
        return $this->publicEquivalent()->label();
    }

    public function publicEquivalent(): self
    {
        return $this === self::Archived ? self::Closed : $this;
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Closed, self::Archived], true);
    }

    public function isClosed(): bool
    {
        return $this === self::Closed;
    }

    public function isArchived(): bool
    {
        return $this === self::Archived;
    }
}
