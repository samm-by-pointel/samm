<?php

namespace App\Modules\Reporting\Services;

use App\Modules\Reporting\Enums\ReportStep;

/**
 * The report being written, kept in the (encrypted) session until submission.
 * It never contains an IP address or any technical fingerprint.
 */
class ReportDraft
{
    private const KEY = 'report_draft';

    /**
     * @param  array<string, mixed>  $data
     */
    public function put(ReportStep $step, array $data): void
    {
        $draft = $this->all();
        $draft['steps'][$step->value] = $data;

        session()->put(self::KEY, $draft);
    }

    /**
     * @return array<string, mixed>
     */
    public function get(ReportStep $step): array
    {
        return $this->all()['steps'][$step->value] ?? [];
    }

    public function isCompleted(ReportStep $step): bool
    {
        return array_key_exists($step->value, $this->all()['steps'] ?? []);
    }

    /**
     * A step may be opened only once every previous step has been completed.
     */
    public function canAccess(ReportStep $step): bool
    {
        foreach ($step->previousSteps() as $previous) {
            if (! $this->isCompleted($previous)) {
                return false;
            }
        }

        return true;
    }

    public function firstIncompleteStep(): ?ReportStep
    {
        foreach (ReportStep::cases() as $step) {
            if (! $this->isCompleted($step)) {
                return $step;
            }
        }

        return null;
    }

    /**
     * A single value across steps, e.g. `value('immediate_danger')`.
     */
    public function value(string $key): mixed
    {
        foreach ($this->all()['steps'] ?? [] as $data) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
        }

        return null;
    }

    /**
     * @return array{steps?: array<string, array<string, mixed>>}
     */
    public function all(): array
    {
        return session()->get(self::KEY, []);
    }

    public function hasAny(): bool
    {
        return ($this->all()['steps'] ?? []) !== [];
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }
}
