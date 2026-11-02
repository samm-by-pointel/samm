<?php

namespace App\Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Actions\SubmitReport;
use App\Modules\Reporting\Enums\ConfidentialityLevel;
use App\Modules\Reporting\Enums\ReportStep;
use App\Modules\Reporting\Models\ReportCategory;
use App\Modules\Reporting\Services\ReportDraft;
use App\Modules\Tracking\Services\TrackingCodeGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Public reporting form, one classic request per step: usable without JavaScript.
 */
class ReportWizardController extends Controller
{
    public function __construct(private readonly ReportDraft $draft) {}

    public function start(): View
    {
        return view('public.report.start', [
            'firstStep' => ReportStep::first(),
            'hasDraft' => $this->draft->hasAny(),
            'resumeStep' => $this->draft->firstIncompleteStep() ?? ReportStep::first(),
        ]);
    }

    public function show(ReportStep $step): View|RedirectResponse
    {
        abort_unless($step->isServed(), 404);

        if (! $this->draft->canAccess($step)) {
            return $this->redirectToFirstIncompleteStep();
        }

        return view($step->view(), [
            'step' => $step,
            'values' => $this->draft->get($step),
            'immediateDanger' => $this->draft->value('immediate_danger') === true,
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
    public function confirmation(TrackingCodeGenerator $codes): View|RedirectResponse
    {
        $submitted = session('submitted_report');

        if (! is_array($submitted) || empty($submitted['reference']) || empty($submitted['secret'])) {
            return redirect()->route('public.home');
        }

        return view('public.report.confirmation', [
            'reference' => $codes->format($submitted['reference']),
            'secret' => $codes->format($submitted['secret']),
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
            ReportStep::Situation => ['categories' => ReportCategory::query()->active()->ordered()->get()],
            ReportStep::Confidentiality => ['levels' => ConfidentialityLevel::cases()],
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

        return [
            'category' => $categoryUuid ? ReportCategory::query()->where('uuid', $categoryUuid)->first() : null,
            'level' => $level ? ConfidentialityLevel::from($level) : null,
            'isConfidential' => $level === ConfidentialityLevel::Confidential->value,
            'formAction' => route('report.submit'),
            'submitLabel' => __('report_form.consent.submit'),
        ];
    }
}
