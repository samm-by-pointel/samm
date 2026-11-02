<?php

namespace App\Modules\Organizations\Services;

/**
 * WCAG contrast checks for the two customizable brand colors (charter §3):
 * a color is accepted only if it reaches 4.5:1 against white (text on a
 * solid button) and against the light surface (text on the page).
 */
class ContrastChecker
{
    public const MIN_RATIO = 4.5;

    private const WHITE = '#FFFFFF';

    private const LIGHT_SURFACE = '#FFFFFF';

    public function isHexColor(?string $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1;
    }

    public function ratio(string $first, string $second): float
    {
        $a = $this->luminance($first);
        $b = $this->luminance($second);

        return round((max($a, $b) + 0.05) / (min($a, $b) + 0.05), 2);
    }

    public function isAccessible(string $color): bool
    {
        return $this->isHexColor($color)
            && $this->ratio($color, self::WHITE) >= self::MIN_RATIO
            && $this->ratio($color, self::LIGHT_SURFACE) >= self::MIN_RATIO;
    }

    /**
     * Darkens the color step by step until it passes; null if even near-black fails (it never does).
     */
    public function nearestAccessible(string $color): ?string
    {
        if (! $this->isHexColor($color)) {
            return null;
        }

        [$r, $g, $b] = $this->channels($color);

        for ($step = 0; $step <= 50; $step++) {
            $factor = 1 - $step * 0.02;
            $candidate = sprintf('#%02X%02X%02X', (int) round($r * $factor), (int) round($g * $factor), (int) round($b * $factor));

            if ($this->isAccessible($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function luminance(string $color): float
    {
        [$r, $g, $b] = array_map(function (int $channel): float {
            $value = $channel / 255;

            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, $this->channels($color));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function channels(string $color): array
    {
        return [hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2))];
    }
}
