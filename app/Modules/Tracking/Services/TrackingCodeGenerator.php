<?php

namespace App\Modules\Tracking\Services;

use App\Modules\Reporting\Models\Report;

/**
 * Public case reference and tracking secret (charter §12.6): an alphabet
 * without ambiguous characters (no 0/O, 1/I/L), groups of four separated by
 * dashes, so that a person can copy them by hand without error.
 */
class TrackingCodeGenerator
{
    public const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public const REFERENCE_PREFIX = 'SMM';

    private const GROUP_LENGTH = 4;

    private const SECRET_GROUPS = 4;

    /**
     * Unique public reference such as SMM-7K4Q-92XD (about 40 bits).
     */
    public function reference(): string
    {
        do {
            $reference = $this->candidateReference();
        } while (Report::query()->where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Tracking secret such as RF3M-W8TZ-K2PA-6HNE (about 79 bits). Shown once, then only its hash survives.
     */
    public function secret(): string
    {
        return implode('-', array_map(fn (): string => $this->group(), range(1, self::SECRET_GROUPS)));
    }

    /**
     * Display form: upper case, groups of four separated by dashes.
     */
    public function format(string $raw): string
    {
        $clean = strtoupper(preg_replace('/[^a-z0-9]/i', '', $raw) ?? '');

        if (str_starts_with($clean, self::REFERENCE_PREFIX)) {
            $body = substr($clean, strlen(self::REFERENCE_PREFIX));

            return self::REFERENCE_PREFIX.($body === '' ? '' : '-'.implode('-', str_split($body, self::GROUP_LENGTH)));
        }

        return implode('-', str_split($clean, self::GROUP_LENGTH));
    }

    protected function candidateReference(): string
    {
        return self::REFERENCE_PREFIX.'-'.$this->group().'-'.$this->group();
    }

    private function group(): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $characters = '';

        for ($i = 0; $i < self::GROUP_LENGTH; $i++) {
            $characters .= self::ALPHABET[random_int(0, $max)];
        }

        return $characters;
    }
}
