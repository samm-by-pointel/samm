<?php

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Enums\ActorKind;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Single entry point for writing the audit trail.
 *
 * Never copies report content: context keys that look like free text are
 * refused, and only scalar values (or lists of scalars) are accepted. The IP
 * address is recorded for internal accounts only.
 */
class AuditLogger
{
    /**
     * Context keys that would carry report content and are therefore refused.
     *
     * @var array<int, string>
     */
    private const FORBIDDEN_CONTEXT_KEYS = [
        'description', 'body', 'content', 'message', 'narrative', 'text', 'note', 'email', 'name', 'phone', 'identity',
    ];

    /**
     * @param  array<string, scalar|array<int, scalar>|null>  $context
     */
    public function record(AuditAction $action, ?Model $subject = null, array $context = [], ?User $actor = null, ActorKind $actorKind = ActorKind::System): AuditLog
    {
        $this->assertContextIsSafe($context);

        if ($actor !== null) {
            $actorKind = ActorKind::User;
        }

        return AuditLog::query()->create([
            'action' => $action,
            'actor_id' => $actor?->getKey(),
            'actor_kind' => $actorKind,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'context' => $context === [] ? null : $context,
            'ip_address' => $actorKind === ActorKind::User ? request()->ip() : null,
            'created_at' => now(),
        ]);
    }

    /**
     * Record an action performed by the currently authenticated internal account.
     *
     * @param  array<string, scalar|array<int, scalar>|null>  $context
     */
    public function recordForCurrentUser(AuditAction $action, ?Model $subject = null, array $context = []): AuditLog
    {
        $user = auth()->user();

        return $this->record($action, $subject, $context, $user instanceof User ? $user : null);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function assertContextIsSafe(array $context): void
    {
        foreach ($context as $key => $value) {
            if (! is_string($key) || in_array(strtolower($key), self::FORBIDDEN_CONTEXT_KEYS, true)) {
                throw new InvalidArgumentException("Audit context key [{$key}] is not allowed: the audit log never stores report content.");
            }

            $values = is_array($value) ? $value : [$value];

            foreach ($values as $item) {
                if ($item !== null && ! is_scalar($item)) {
                    throw new InvalidArgumentException("Audit context value for [{$key}] must be scalar.");
                }

                if (is_string($item) && mb_strlen($item) > 190) {
                    throw new InvalidArgumentException("Audit context value for [{$key}] is too long to be a reference.");
                }
            }
        }
    }
}
