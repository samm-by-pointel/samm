<?php

namespace App\Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Messaging\Actions\PostMessage;
use App\Modules\Messaging\Enums\MessageAuthor;
use App\Modules\Messaging\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Replies written by the team from the case page, visible in the tracking space.
 */
class StaffMessageController extends Controller
{
    public function store(Request $request, CaseFile $case, PostMessage $postMessage): RedirectResponse
    {
        Gate::authorize('reply', $case);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = $case->report->conversation ?? Conversation::query()->create(['report_id' => $case->report_id]);

        $postMessage->handle($conversation, MessageAuthor::Staff, $validated['body'], $request->user());
        $case->forceFill(['last_activity_at' => now()])->save();

        return redirect()->route('manage.cases.show', $case)->with('status', __('manage.cases.actions.confirmations.reply_sent'));
    }
}
