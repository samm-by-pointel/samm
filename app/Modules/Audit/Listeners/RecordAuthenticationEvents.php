<?php

namespace App\Modules\Audit\Listeners;

use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Services\AuditLogger;
use App\Modules\Users\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

/**
 * Records sign-ins, failures, lockouts and sign-outs of internal accounts.
 * The e-mail address typed on a failed attempt is never written to the log.
 */
class RecordAuthenticationEvents
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(Login::class, [$this, 'handleLogin']);
        $events->listen(Failed::class, [$this, 'handleFailed']);
        $events->listen(Lockout::class, [$this, 'handleLockout']);
        $events->listen(Logout::class, [$this, 'handleLogout']);
    }

    public function handleLogin(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->audit->record(AuditAction::LoginSucceeded, $event->user, ['guard' => $event->guard, 'remember' => $event->remember], $event->user);
    }

    public function handleFailed(Failed $event): void
    {
        $user = $event->user instanceof User
            ? $event->user
            : User::query()->where('email', strtolower((string) ($event->credentials['email'] ?? '')))->first();

        $reason = match (true) {
            $user === null => 'unknown_account',
            ! $user->is_active => 'inactive',
            default => 'invalid_credentials',
        };

        $this->audit->record(AuditAction::LoginFailed, $user, ['guard' => $event->guard, 'reason' => $reason]);
    }

    public function handleLockout(Lockout $event): void
    {
        $user = User::query()->where('email', strtolower((string) $event->request->input('email')))->first();

        $this->audit->record(AuditAction::LockedOut, $user, ['guard' => 'web']);
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->audit->record(AuditAction::Logout, $event->user, ['guard' => $event->guard], $event->user);
    }
}
