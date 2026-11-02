<?php

namespace App\Modules\Cases\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cases\Models\CaseFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InternalNoteController extends Controller
{
    public function store(Request $request, CaseFile $case): RedirectResponse
    {
        Gate::authorize('addNote', $case);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
        ]);

        $case->internalNotes()->create([
            'author_user_id' => $request->user()->getKey(),
            'body' => $validated['note'],
            'created_at' => now(),
        ]);

        $case->forceFill(['last_activity_at' => now()])->save();

        return redirect()->route('manage.cases.show', $case)->with('status', __('manage.cases.actions.confirmations.note_added'));
    }
}
