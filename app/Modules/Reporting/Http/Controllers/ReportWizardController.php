<?php

namespace App\Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Actions\SubmitReport;
use App\Modules\Reporting\Enums\ConfidentialityLevel;
use App\Modules\Reporting\Enums\ReportStep;
use App\Modules\Reporting\Models\ReportCategory;
use App\Modules\Reporting\Services\ReportDraft;
use App\Modules\Tracking\Services\TrackingCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public reporting form, one request per step: the draft lives in the session,
 * never in the browser.
 */
class ReportWizardController extends Controller
{
    public function __construct(private readonly ReportDraft $draft) {}

    public function start(): Response
    {
        return Inertia::render('report/Start', [
            'hasDraft' => $this->draft->hasAny(),
            'beginUrl' => route('report.step.show', ReportStep::first()),
            'resumeUrl' => route('report.step.show', $this->draft->firstIncompleteStep() ?? ReportStep::first()),
            'abandonUrl' => route('report.abandon'),
        ]);
    }

    public function show(ReportStep $step): Response|RedirectResponse
    {
        abort_unless($step->isServed(), 404);

        if (! $this->draft->canAccess($step)) {
            return $this->redirectToFirstIncompleteStep();
        }

        return Inertia::render('report/Step', [
            'step' => $step->value,
            'title' => $step->label(),
            'steps' => collect(ReportStep::cases())->map(fn (ReportStep $item): array => ['key' => $item->value, 'label' => $item->label()]),
            'values' => (object) $this->draft->get($step),
            'immediateDanger' => $this->draft->value('immediate_danger') === true,
            'action' => $step === ReportStep::Consent ? route('report.submit') : route('report.step.store', $step),
            'backUrl' => $step->previous() ? route('report.step.show', $step->previous()) : route('report.start'),
            'abandonUrl' => route('report.abandon'),
            'today' => now()->toDateString(),
            ...$this->stepData($step),
        ]);
    }

    public function store(Request $request, ReportStep $step): RedirectResponse
    {
        abort_unless($step->isServed(), 404);

        if (! $this->draft->canAccess($step)) {
            return $this->redirectToFirstIncompleteStep();
        }

        if ($step === ReportStep::Consent) {
            return $this->submit($request);
        }

        $validated = $request->validate($step->rules($this->rulesContext()), $step->messages());

        $this->draft->put($step, $step->sanitize($validated));

        return redirect()->route('report.step.show', $step->next() ?? ReportStep::Consent);
    }

    /**
     * Last step: consents are validated, then the whole draft becomes a report
     * in one transaction. The codes are flashed for a single display.
     */
    public function submit(Request $request, ?SubmitReport $submitReport = null): RedirectResponse
    {
        $step = ReportStep::Consent;

        if (! $this->draft->canAccess($step)) {
            return $this->redirectToFirstIncompleteStep();
        }

        $validated = $request->validate($step->rules($this->rulesContext()), $step->messages());
        $this->draft->put($step, $step->sanitize($validated));

        $submitted = ($submitReport ?? app(SubmitReport::class))->handle($this->draft->all()['steps'], app()->getLocale());

        $this->draft->clear();

        return redirect()->route('report.confirmation')->with('submitted_report', [
            'reference' => $submitted->reference,
            'secret' => $submitted->secret,
        ]);
    }

    /**
     * Shown once: the codes come from the flashed session data and are gone on reload.
     */
    public function confirmation(TrackingCodeGenerator $codes): Response|RedirectResponse
    {
        $submitted = session('submitted_report');

        if (! is_array($submitted) || empty($submitted['reference']) || empty($submitted['secret'])) {
            return redirect()->route('public.home');
        }

        $reference = $codes->format($submitted['reference']);
        $secret = $codes->format($submitted['secret']);

        // Shown once: the page must not survive in the browser history either.
        Inertia::clearHistory();

        return Inertia::render('report/Confirmation', [
            'reference' => $reference,
            'secret' => $secret,
            'downloadText' => __('report_form.confirmation.download_content', [
                'reference' => $reference,
                'secret' => $secret,
                'url' => route('tracking.login'),
            ]),
        ]);
    }

    public function abandon(): RedirectResponse
    {
        $this->draft->clear();

        return redirect()->route('public.home')->with('status', __('report_form.abandoned'));
    }

    private function redirectToFirstIncompleteStep(): RedirectResponse
    {
        return redirect()->route('report.step.show', $this->draft->firstIncompleteStep() ?? ReportStep::first());
    }

    /**
     * Data each screen needs, resolved here so that views never query the database.
     *
     * @return array<string, mixed>
     */
    private function stepData(ReportStep $step): array
    {
        return match ($step) {
            ReportStep::Situation => ['categories' => ReportCategory::query()->active()->ordered()->get()
                ->map(fn (ReportCategory $category): array => [
                    'value' => $category->uuid,
                    'label' => $category->name(),
                    'description' => $category->description(),
                ])],
            ReportStep::Confidentiality => ['levels' => collect(ConfidentialityLevel::cases())
                ->map(fn (ConfidentialityLevel $level): array => [
                    'value' => $level->value,
                    'label' => $level->label(),
                    'description' => $level->description(),
                ])],
            ReportStep::Consent => $this->summary(),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesContext(): array
    {
        return ['confidentiality_level' => $this->draft->value('confidentiality_level')];
    }

    /**
     * Recap shown before submission: choices only, never the narrative.
     *
     * @return array<string, mixed>
     */
    private function summary(): array
    {
        $level = $this->draft->value('confidentiality_level');
        $categoryUuid = $this->draft->value('category');

        $category = $categoryUuid ? ReportCategory::query()->where('uuid', $categoryUuid)->first() : null;

        return [
            'summary' => [
                'category' => $category?->name(),
                'level' => $level ? ConfidentialityLevel::from($level)->label() : null,
            ],
            'isConfidential' => $level === ConfidentialityLevel::Confidential->value,
        ];
    }
}
