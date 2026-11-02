<?php

namespace App\Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Events\ReportCategoryChanged;
use App\Modules\Reporting\Models\ReportCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Categories are never deleted: a report may refer to them. They are deactivated instead.
 */
class ReportCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $category = ReportCategory::query()->create($this->validated($request));
        ReportCategoryChanged::dispatch($category, 'created', $request->user());

        return redirect()->route('manage.organization.edit')->with('status', __('manage.organization.categories.saved'));
    }

    public function update(Request $request, ReportCategory $category): RedirectResponse
    {
        $category->update($this->validated($request));
        ReportCategoryChanged::dispatch($category, 'updated', $request->user());

        return redirect()->route('manage.organization.edit')->with('status', __('manage.organization.categories.saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = Validator::make($request->all(), [
            'name' => ['required', 'array'],
            'name.*' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:500'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ])->after(function ($validator) use ($request): void {
            if (array_filter((array) $request->input('name', []), fn ($value): bool => is_string($value) && trim($value) !== '') === []) {
                $validator->errors()->add('name', __('manage.organization.errors.name_required'));
            }
        })->validateWithBag('categories');

        $clean = fn (array $values): array => array_filter($values, fn ($value): bool => is_string($value) && trim($value) !== '');
        $description = $clean((array) ($validated['description'] ?? []));

        return [
            'name' => $clean($validated['name']),
            'description' => $description === [] ? null : $description,
            'position' => $validated['position'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }
}
