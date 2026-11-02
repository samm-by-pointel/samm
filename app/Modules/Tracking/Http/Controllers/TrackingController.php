<?php

namespace App\Modules\Tracking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Messaging\Models\Message;
use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Actions\AuthenticateTracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public tracking space: no account, the two codes only.
 */
class TrackingController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('tracking/Login', [
            'action' => route('tracking.authenticate'),
        ]);
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

    /**
     * The reporting person sees the state of the case and the exchanges, never
     * internal notes nor the names of the staff (charter §12.8).
     */
    public function show(Request $request): Response
    {
        /** @var Report $report */
        $report = $request->attributes->get('tracked_report');
        $report->loadMissing(['case', 'category', 'conversation.messages']);

        $report->conversation?->markReadByReporter();

        $status = $report->case->status->publicEquivalent();

        return Inertia::render('tracking/Show', [
            'report' => [
                'reference' => $report->reference,
                'category' => $report->category->name(),
                'submittedAt' => $report->submitted_at->toIso8601String(),
                'status' => ['value' => $status->value, 'label' => $status->label()],
            ],
            'messages' => ($report->conversation?->messages ?? collect())->map(fn (Message $message): array => [
                'body' => $message->body,
                'fromReporter' => $message->isFromReporter(),
                'author' => $message->author_type->publicLabel(),
                'createdAt' => $message->created_at->toIso8601String(),
            ])->values(),
            'messageAction' => route('tracking.messages.store'),
            'logoutAction' => route('tracking.logout'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        AuthenticateTracking::forget($request->session());

        return redirect()->route('public.home')->with('status', __('tracking.logged_out'));
    }
}
