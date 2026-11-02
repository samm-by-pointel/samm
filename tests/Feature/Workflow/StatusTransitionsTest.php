<?php

namespace Tests\Feature\Workflow;

use App\Modules\Workflow\Enums\CaseStatus;
use App\Modules\Workflow\Exceptions\InvalidStatusTransition;
use App\Modules\Workflow\Services\StatusTransitions;
use Tests\TestCase;

class StatusTransitionsTest extends TestCase
{
    /**
     * @var array<string, array<int, string>>
     */
    private const EXPECTED = [
        'new' => ['to_qualify', 'in_progress', 'closed'],
        'to_qualify' => ['in_progress', 'awaiting_information', 'referred', 'closed'],
        'in_progress' => ['awaiting_information', 'referred', 'resolving', 'closed'],
        'awaiting_information' => ['in_progress', 'referred', 'resolving', 'closed'],
        'referred' => ['in_progress', 'resolving', 'closed'],
        'resolving' => ['in_progress', 'closed'],
        'closed' => ['archived', 'in_progress'],
        'archived' => [],
    ];

    public function test_every_pair_of_statuses_is_either_allowed_or_refused_as_validated(): void
    {
        $transitions = new StatusTransitions;

        foreach (CaseStatus::cases() as $from) {
            foreach (CaseStatus::cases() as $to) {
                $expected = in_array($to->value, self::EXPECTED[$from->value], true);

                $this->assertSame($expected, $transitions->canTransition($from, $to), "{$from->value} → {$to->value}");
            }
        }
    }

    public function test_archived_is_terminal(): void
    {
        $this->assertSame([], (new StatusTransitions)->allowedFrom(CaseStatus::Archived));
    }

    public function test_refused_transition_throws_an_explicit_exception(): void
    {
        $this->expectException(InvalidStatusTransition::class);
        $this->expectExceptionMessage('[archived] to [new]');

        (new StatusTransitions)->assertAllowed(CaseStatus::Archived, CaseStatus::New);
    }

    public function test_allowed_transition_does_not_throw(): void
    {
        (new StatusTransitions)->assertAllowed(CaseStatus::Closed, CaseStatus::Archived);

        $this->assertTrue(true);
    }
}
