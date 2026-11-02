<?php

namespace App\Modules\Workflow\Actions;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Enums\CaseStatus;
use App\Modules\Workflow\Events\CaseStatusChanged;
use App\Modules\Workflow\Exceptions\InvalidStatusTransition;
use App\Modules\Workflow\Services\StatusTransitions;
use Illuminate\Support\Facades\DB;

/**
 * The only way to change a case status: checks the transition, updates the
 * case, writes the history and announces the change (audited by the Audit module).
 */
class ChangeCaseStatus
{
    public function __construct(private readonly StatusTransitions $transitions) {}

    /**
     * @throws InvalidStatusTransition
     */
    public function handle(CaseFile $case, CaseStatus $to, ?User $actor = null, ?string $comment = null): CaseFile
    {
        $from = $case->status;

        $this->transitions->assertAllowed($from, $to);

        DB::transaction(function () use ($case, $from, $to, $actor, $comment): void {
            $case->status = $to;
            $case->last_activity_at = now();

            if ($to->isClosed()) {
                $case->closed_at = now();
                $case->closure_reason = $comment;
            }

            if ($to->isArchived()) {
                $case->archived_at = now();
            }

            if ($to->isOpen()) {
                $case->closed_at = null;
                $case->closure_reason = null;
                $case->archived_at = null;
            }

            $case->save();

            $case->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $to,
                'changed_by_user_id' => $actor?->getKey(),
                'comment' => $comment,
                'created_at' => now(),
            ]);
        });

        CaseStatusChanged::dispatch($case, $from, $to, $actor);

        return $case;
    }
}
