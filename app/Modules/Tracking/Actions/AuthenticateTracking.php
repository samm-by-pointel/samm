<?php

namespace App\Modules\Tracking\Actions;

use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Events\TrackingAccessFailed;
use App\Modules\Tracking\Events\TrackingAccessSucceeded;
use App\Modules\Tracking\Services\TrackingCodeGenerator;
use Illuminate\Contracts\Session\Session;

/**
 * Opens the tracking space with the case reference and the secret.
 *
 * Every failure (unknown reference, wrong secret, locked credential) returns
 * the same result, so that nobody can learn whether a reference exists.
 */
class AuthenticateTracking
{
    public const SESSION_REPORT = 'tracking.report_id';

    public const SESSION_LAST_ACTIVITY = 'tracking.last_activity_at';

    public function __construct(private readonly TrackingCodeGenerator $codes) {}

    public function handle(string $reference, string $secret, Session $session): bool
    {
        $report = Report::query()
            ->with('trackingCredential')
            ->where('reference', $this->codes->format($reference))
            ->first();

        $credential = $report?->trackingCredential;

        if ($credential === null || $credential->isLocked() || ! $credential->verify($secret)) {
            $credential?->registerFailure();
            TrackingAccessFailed::dispatch($report);

            return false;
        }

        $credential->registerSuccess();

        $session->regenerate();
        $session->put(self::SESSION_REPORT, $report->getKey());
        $session->put(self::SESSION_LAST_ACTIVITY, now()->timestamp);

        TrackingAccessSucceeded::dispatch($report);

        return true;
    }

    public static function forget(Session $session): void
    {
        $session->forget([self::SESSION_REPORT, self::SESSION_LAST_ACTIVITY]);
        $session->regenerate();
    }
}
