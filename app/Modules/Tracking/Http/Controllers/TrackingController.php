<?php

namespace App\Modules\Tracking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Actions\AuthenticateTracking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Public tracking space: no account, the two codes only.
 */
class TrackingController extends Controller
{
    public function showLogin(): View
    {
        return view('public.tracking.login');
    }

    public function authenticate(Request $request, AuthenticateTracking $authenticate): RedirectResponse
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:32'],
            'secret' => ['required', 'string', 'max:32'],
        ]);

        if (! $authenticate->handle($validated['reference'], $validated['secret'], $request->session())) {
            return back()
                ->withInput($request->only('reference'))
                ->withErrors(['reference' => __('tracking.login.failed')]);
        }

        return redirect()->route('tracking.show');
    }

    public function show(Request $request): View
    {
        /** @var Report $report */
        $report = $request->attributes->get('tracked_report');
        $report->loadMissing(['case', 'category', 'conversation.messages']);

        $report->conversation?->markReadByReporter();

        return view('public.tracking.show', [
            'report' => $report,
            'case' => $report->case,
            'messages' => $report->conversation?->messages ?? collect(),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        AuthenticateTracking::forget($request->session());

        return redirect()->route('public.home')->with('status', __('tracking.logged_out'));
    }
}
