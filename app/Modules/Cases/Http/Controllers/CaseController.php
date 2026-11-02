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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CaseController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request, CasePageData $pageData): Response
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

        return Inertia::render('manage/cases/Index', [
            'cases' => $cases->through(fn (CaseFile $case): array => [
                'url' => route('manage.cases.show', $case),
                'reference' => $case->report->reference,
                'category' => $case->report->category->name(),
                'status' => $pageData->status($case->status),
                'priority' => ['label' => $case->priority->label(), 'isHigh' => $case->priority->isHigh()],
                'assignee' => $case->assignee?->name,
                'submittedAt' => $case->report->submitted_at?->toIso8601String(),
                'unread' => (int) ($case->report->conversation?->unread_for_staff_count ?? 0),
            ]),
            'filters' => (object) $filters,
            'statuses' => collect(CaseStatus::cases())->map(fn (CaseStatus $status): array => $pageData->status($status)),
            'priorities' => collect(CasePriority::cases())->map(fn (CasePriority $priority): array => ['value' => $priority->value, 'label' => $priority->label()]),
            'categories' => ReportCategory::query()->active()->ordered()->get()
                ->map(fn (ReportCategory $category): array => ['value' => $category->uuid, 'label' => $category->name()]),
        ]);
    }

    /**
     * Case page: content, status history, messages. The identity stays masked;
     * every view is recorded in the audit log.
     */
    public function show(Request $request, CaseFile $case, CasePageData $pageData): Response
    {
        Gate::authorize('view', $case);

        $case->loadMissing('report.conversation');
        $case->report->conversation?->markReadByStaff();

        CaseViewed::dispatch($case, $request->user());

        return Inertia::render('manage/cases/Show', $pageData->for($case, $request->user()));
    }
}
