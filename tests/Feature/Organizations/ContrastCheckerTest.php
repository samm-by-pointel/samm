<?php

namespace Tests\Feature\Organizations;

use App\Modules\Organizations\Services\ContrastChecker;
use Tests\TestCase;

class ContrastCheckerTest extends TestCase
{
    public function test_known_ratios(): void
    {
        $checker = new ContrastChecker;

        $this->assertSame(21.0, $checker->ratio('#000000', '#FFFFFF'));
        $this->assertSame(1.0, $checker->ratio('#FAF7F2', '#FAF7F2'));
        $this->assertGreaterThanOrEqual(4.5, $checker->ratio('#0E5A55', '#FFFFFF'), 'Le vert Sàmm passe sur blanc.');
        $this->assertGreaterThanOrEqual(4.5, $checker->ratio('#A84B14', '#FAF7F2'), 'L\'ocre terre passe sur la surface.');
    }

    public function test_light_colors_are_refused_and_a_darker_shade_is_suggested(): void
    {
        $checker = new ContrastChecker;

        $this->assertTrue($checker->isAccessible('#0E5A55'));
        $this->assertFalse($checker->isAccessible('#FFFF00'));
        $this->assertFalse($checker->isAccessible('#5FC2B6'), 'La variante sombre du vert n\'est pas lisible sur blanc.');
        $this->assertFalse($checker->isAccessible('rouge'));

        $suggestion = $checker->nearestAccessible('#FFFF00');
        $this->assertNotNull($suggestion);
        $this->assertTrue($checker->isAccessible($suggestion));
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $suggestion);
        $this->assertSame('#0E5A55', $checker->nearestAccessible('#0E5A55'), 'Une couleur accessible est rendue telle quelle.');
    }
}
