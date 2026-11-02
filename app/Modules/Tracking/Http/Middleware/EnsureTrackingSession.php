<?php

namespace App\Modules\Tracking\Http\Middleware;

use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Actions\AuthenticateTracking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The tracking space stays open for a short idle period only
 * (samm.tracking.session_minutes), shorter than the general session.
 */
class EnsureTrackingSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        $reportId = $session->get(AuthenticateTracking::SESSION_REPORT);
        $lastActivity = (int) $session->get(AuthenticateTracking::SESSION_LAST_ACTIVITY, 0);
        $idleLimit = max(1, (int) config('samm.tracking.session_minutes', 30)) * 60;

        if ($reportId === null || now()->timestamp - $lastActivity > $idleLimit) {
            AuthenticateTracking::forget($session);

            return redirect()->route('tracking.login')->with('status', __('tracking.session_expired'));
        }

        $report = Report::query()->find($reportId);

        if ($report === null) {
            AuthenticateTracking::forget($session);

            return redirect()->route('tracking.login');
        }

        $session->put(AuthenticateTracking::SESSION_LAST_ACTIVITY, now()->timestamp);
        $request->attributes->set('tracked_report', $report);

        return $next($request);
    }
}
