<?php

namespace Tests\Feature\Workflow;

use App\Modules\Workflow\Enums\CaseStatus;
use Tests\TestCase;

class CaseStatusTest extends TestCase
{
    public function test_there_are_exactly_eight_statuses_with_labels_in_both_languages(): void
    {
        $this->assertCount(8, CaseStatus::cases());

        foreach (CaseStatus::cases() as $status) {
            foreach (['fr', 'en'] as $locale) {
                $this->assertNotSame('workflow.statuses.'.$status->value, __('workflow.statuses.'.$status->value, [], $locale));
            }
        }
    }

    public function test_archived_is_shown_as_closed_to_the_public(): void
    {
        app()->setLocale('fr');

        $this->assertSame('Archivé', CaseStatus::Archived->label());
        $this->assertSame('Clôturé', CaseStatus::Archived->publicLabel());
        $this->assertSame(CaseStatus::Closed, CaseStatus::Archived->publicEquivalent());
        $this->assertSame('En cours de traitement', CaseStatus::InProgress->publicLabel());
    }

    public function test_open_closed_and_archived_helpers(): void
    {
        foreach ([CaseStatus::New, CaseStatus::ToQualify, CaseStatus::InProgress, CaseStatus::AwaitingInformation, CaseStatus::Referred, CaseStatus::Resolving] as $status) {
            $this->assertTrue($status->isOpen(), $status->value);
        }

        $this->assertFalse(CaseStatus::Closed->isOpen());
        $this->assertTrue(CaseStatus::Closed->isClosed());
        $this->assertFalse(CaseStatus::Archived->isOpen());
        $this->assertTrue(CaseStatus::Archived->isArchived());
    }
}
