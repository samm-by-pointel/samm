<?php

namespace Tests\Feature\Tracking;

use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Services\TrackingCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingCodeGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_and_secret_have_the_expected_shape(): void
    {
        $generator = new TrackingCodeGenerator;

        $this->assertMatchesRegularExpression('/^SMM-[A-Z2-9]{4}-[A-Z2-9]{4}$/', $generator->reference());
        $this->assertMatchesRegularExpression('/^[A-Z2-9]{4}(-[A-Z2-9]{4}){3}$/', $generator->secret());
    }

    public function test_codes_never_contain_ambiguous_characters(): void
    {
        $generator = new TrackingCodeGenerator;

        for ($i = 0; $i < 500; $i++) {
            $this->assertDoesNotMatchRegularExpression('/[01OIL]/', $generator->secret());
        }

        $this->assertDoesNotMatchRegularExpression('/[01OIL]/', TrackingCodeGenerator::ALPHABET);
        $this->assertSame(31, strlen(TrackingCodeGenerator::ALPHABET));
    }

    public function test_reference_is_redrawn_on_collision(): void
    {
        Report::factory()->create(['reference' => 'SMM-AAAA-AAAA']);

        $generator = new class extends TrackingCodeGenerator
        {
            public int $draws = 0;

            protected function candidateReference(): string
            {
                $this->draws++;

                return $this->draws === 1 ? 'SMM-AAAA-AAAA' : 'SMM-BBBB-BBBB';
            }
        };

        $this->assertSame('SMM-BBBB-BBBB', $generator->reference());
        $this->assertSame(2, $generator->draws);
    }

    public function test_format_groups_by_four_and_uppercases(): void
    {
        $generator = new TrackingCodeGenerator;

        $this->assertSame('RF3M-W8TZ-K2PA-6HNE', $generator->format('rf3m w8tz k2pa 6hne'));
        $this->assertSame('SMM-7K4Q-92XD', $generator->format('smm7k4q92xd'));
    }
}
