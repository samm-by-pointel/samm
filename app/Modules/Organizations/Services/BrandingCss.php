<?php

namespace App\Modules\Organizations\Services;

use App\Modules\Organizations\Models\Organization;

/**
 * Builds the CSS custom properties that let an organization recolor Sàmm.
 *
 * Only `--samm` and `--terre` (and their `-soft` / `-rgb` companions) may change
 * (charter §3): status colors, alerts, quick exit and focus stay fixed. Values
 * that are not a 6-digit hexadecimal color are ignored, so the output is safe
 * to print unescaped inside a <style> element.
 */
class BrandingCss
{
    private const LIGHT_SURFACE = '#FFFFFF';

    private const DARK_SURFACE = '#111715';

    private const LIGHT_SOFT_RATIO = 0.12;

    private const DARK_SOFT_RATIO = 0.20;

    public function for(?Organization $organization): string
    {
        if ($organization === null) {
            return '';
        }

        $light = [];
        $dark = [];

        foreach (['samm' => $organization->primary_color, 'terre' => $organization->secondary_color] as $token => $color) {
            if (! $this->isHexColor($color)) {
                continue;
            }

            $color = strtolower($color);
            $light[] = "--{$token}: {$color};";
            $light[] = "--{$token}-rgb: {$this->rgb($color)};";
            $light[] = "--{$token}-soft: {$this->blend($color, self::LIGHT_SURFACE, self::LIGHT_SOFT_RATIO)};";
            $dark[] = "--{$token}-soft: {$this->blend($color, self::DARK_SURFACE, self::DARK_SOFT_RATIO)};";
        }

        if ($light === []) {
            return '';
        }

        $darkRules = implode(' ', $dark);

        // Same selectors as app.css: explicit choice first, system preference otherwise.
        return implode("\n", [
            ':root { '.implode(' ', $light).' }',
            '[data-theme="dark"] { '.$darkRules.' }',
            '@media (prefers-color-scheme: dark) { :root:not([data-theme="light"]) { '.$darkRules.' } }',
        ]);
    }

    public function isHexColor(?string $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1;
    }

    /**
     * Mix `$ratio` of `$color` over `$background`, as the charter defines the `-soft` tints.
     */
    public function blend(string $color, string $background, float $ratio): string
    {
        [$r1, $g1, $b1] = $this->channels($color);
        [$r2, $g2, $b2] = $this->channels($background);

        return sprintf(
            '#%02x%02x%02x',
            (int) round($r2 + ($r1 - $r2) * $ratio),
            (int) round($g2 + ($g1 - $g2) * $ratio),
            (int) round($b2 + ($b1 - $b2) * $ratio),
        );
    }

    private function rgb(string $color): string
    {
        return implode(', ', $this->channels($color));
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function channels(string $color): array
    {
        return [
            hexdec(substr($color, 1, 2)),
            hexdec(substr($color, 3, 2)),
            hexdec(substr($color, 5, 2)),
        ];
    }
}
