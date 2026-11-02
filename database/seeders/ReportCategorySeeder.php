<?php

namespace Database\Seeders;

use App\Modules\Reporting\Models\ReportCategory;
use Illuminate\Database\Seeder;

class ReportCategorySeeder extends Seeder
{
    /**
     * The eight categories listed in the specification (§6, step 1), in order.
     *
     * @var array<int, array{fr: string, en: string}>
     */
    private const CATEGORIES = [
        ['fr' => 'Harcèlement moral', 'en' => 'Psychological harassment'],
        ['fr' => 'Harcèlement sexuel', 'en' => 'Sexual harassment'],
        ['fr' => 'Violence physique ou verbale', 'en' => 'Physical or verbal violence'],
        ['fr' => 'Discrimination', 'en' => 'Discrimination'],
        ['fr' => 'Abus de pouvoir', 'en' => 'Abuse of power'],
        ['fr' => 'Représailles', 'en' => 'Retaliation'],
        ['fr' => 'Atteinte à la dignité', 'en' => 'Violation of dignity'],
        ['fr' => 'Autre situation', 'en' => 'Other situation'],
    ];

    public function run(): void
    {
        if (ReportCategory::query()->exists()) {
            return;
        }

        foreach (self::CATEGORIES as $index => $name) {
            ReportCategory::query()->create([
                'name' => $name,
                'position' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
