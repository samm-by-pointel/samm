<?php

namespace App\Modules\Organizations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Organizations\Events\EmergencyContactChanged;
use App\Modules\Organizations\Models\EmergencyContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmergencyContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $contact = EmergencyContact::query()->create($validated);
        EmergencyContactChanged::dispatch($contact, 'created', $request->user());

        return redirect()->route('manage.organization.edit')->with('status', __('manage.organization.emergency.saved'));
    }

    public function update(Request $request, EmergencyContact $contact): RedirectResponse
    {
        $contact->update($this->validated($request));
        EmergencyContactChanged::dispatch($contact, 'updated', $request->user());

        return redirect()->route('manage.organization.edit')->with('status', __('manage.organization.emergency.saved'));
    }

    public function destroy(Request $request, EmergencyContact $contact): RedirectResponse
    {
        EmergencyContactChanged::dispatch($contact, 'deleted', $request->user());
        $contact->delete();

        return redirect()->route('manage.organization.edit')->with('status', __('manage.organization.emergency.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = Validator::make($request->all(), [
            'label' => ['required', 'array'],
            'label.*' => ['nullable', 'string', 'max:80'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9+][0-9+ ().-]{0,31}$/'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ])->after(function ($validator) use ($request): void {
            if (array_filter((array) $request->input('label', []), fn ($value): bool => is_string($value) && trim($value) !== '') === []) {
                $validator->errors()->add('label', __('manage.organization.errors.label_required'));
            }
        })->validateWithBag('emergency');

        return [
            'label' => array_filter($validated['label'], fn ($value): bool => is_string($value) && trim($value) !== ''),
            'phone_number' => trim($validated['phone_number']),
            'position' => $validated['position'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }
}
