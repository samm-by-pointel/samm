<?php

namespace App\Modules\Cases\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cases\Enums\CasePriority;
use App\Modules\Cases\Events\CaseViewed;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Cases\Services\CasePageData;
use App\Modules\Messaging\Enums\MessageAuthor;
use App\Modules\Reporting\Models\ReportCategory;
use App\Modules\Workflow\Enums\CaseStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CaseController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', CaseFile::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(CaseStatus::class)],
            'category' => ['nullable', 'string', Rule::exists('report_categories', 'uuid')],
            'assignment' => ['nullable', Rule::in(['mine', 'unassigned', 'all'])],
            'priority' => ['nullable', Rule::enum(CasePriority::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $user = $request->user();

        $cases = CaseFile::query()
            ->visibleTo($user)
            ->with([
                'report.category',
                'assignee',
                'report.conversation' => fn ($query) => $query->withCount([
                    'messages as unread_for_staff_count' => fn ($messages) => $messages
                        ->where('author_type', MessageAuthor::Reporter->value)
                        ->whereNull('read_by_staff_at'),
                ]),
            ])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $uuid) => $query->whereHas('report.category', fn ($category) => $category->where('uuid', $uuid)))
            ->when(($filters['assignment'] ?? null) === 'mine', fn ($query) => $query->assignedTo($user))
            ->when(($filters['assignment'] ?? null) === 'unassigned', fn ($query) => $query->unassigned())
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereHas('report', fn ($report) => $report->whereDate('submitted_at', '>=', $from)))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereHas('report', fn ($report) => $report->whereDate('submitted_at', '<=', $to)))
            ->latest('last_activity_at')
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('manage.cases.index', [
            'cases' => $cases,
            'filters' => $filters,
            'statuses' => CaseStatus::cases(),
            'priorities' => CasePriority::cases(),
            'categories' => ReportCategory::query()->active()->ordered()->get(),
        ]);
    }

    /**
     * Case page: content, status history, messages. The identity stays masked;
     * every view is recorded in the audit log.
     */
    public function show(Request $request, CaseFile $case, CasePageData $pageData): View
    {
        Gate::authorize('view', $case);

        $case->loadMissing('report.conversation');
        $case->report->conversation?->markReadByStaff();

        CaseViewed::dispatch($case, $request->user());

        return view('manage.cases.show', $pageData->for($case, $request->user()));
    }
}
