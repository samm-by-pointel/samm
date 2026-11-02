<?php

namespace App\Modules\Tracking\Models;

use App\Modules\Reporting\Models\Report;
use Database\Factories\TrackingCredentialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Hashed tracking secret of a report (specification §7). The plain secret is
 * shown once to the reporting person and never stored.
 *
 * @property int $report_id
 * @property string $secret_hash
 * @property int $failed_attempts
 * @property Carbon|null $locked_until
 * @property Carbon|null $last_used_at
 */
#[Fillable(['report_id', 'secret_hash', 'failed_attempts', 'locked_until', 'last_used_at'])]
#[Hidden(['secret_hash'])]
#[UseFactory(TrackingCredentialFactory::class)]
class TrackingCredential extends Model
{
    /** @use HasFactory<TrackingCredentialFactory> */
    use HasFactory;

    public const MAX_FAILED_ATTEMPTS = 5;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'failed_attempts' => 'integer',
            'locked_until' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public static function hashSecret(string $secret): string
    {
        return Hash::make(self::normalize($secret));
    }

    /**
     * Secrets are compared without dashes, spaces or case, to forgive copy mistakes.
     */
    public static function normalize(string $secret): string
    {
        return strtoupper(preg_replace('/[^a-z0-9]/i', '', $secret) ?? '');
    }

    public function verify(string $secret): bool
    {
        return Hash::check(self::normalize($secret), $this->secret_hash);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * Count a failed attempt and lock the credential once the limit is reached.
     */
    public function registerFailure(): void
    {
        $this->failed_attempts++;

        if ($this->failed_attempts >= self::MAX_FAILED_ATTEMPTS) {
            $this->locked_until = now()->addMinutes((int) config('samm.tracking.secret_lock_minutes', 15));
            $this->failed_attempts = 0;
        }

        $this->save();
    }

    /**
     * Successful use: clear the counter and the lock.
     */
    public function registerSuccess(): void
    {
        $this->forceFill([
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_used_at' => now(),
        ])->save();
    }
}
