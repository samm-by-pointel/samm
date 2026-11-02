<?php

namespace App\Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Messaging\Actions\PostMessage;
use App\Modules\Messaging\Enums\MessageAuthor;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Reporting\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Messages written by the reporting person from the tracking space.
 */
class PublicMessageController extends Controller
{
    public function store(Request $request, PostMessage $postMessage): RedirectResponse
    {
        /** @var Report $report */
        $report = $request->attributes->get('tracked_report');

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = $report->conversation ?? Conversation::query()->create(['report_id' => $report->getKey()]);

        $postMessage->handle($conversation, MessageAuthor::Reporter, $validated['body']);

        return redirect()->route('tracking.show')->with('status', __('tracking.message_sent'));
    }
}
