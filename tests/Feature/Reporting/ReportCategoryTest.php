<?php

namespace Tests\Feature\Reporting;

use App\Modules\Reporting\Models\ReportCategory;
use Database\Seeders\ReportCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_eight_specification_categories_in_order(): void
    {
        $this->seed(ReportCategorySeeder::class);

        $categories = ReportCategory::query()->active()->ordered()->get();

        $this->assertCount(8, $categories);
        $this->assertSame('Harcèlement moral', $categories->first()->translate('name', 'fr'));
        $this->assertSame('Psychological harassment', $categories->first()->translate('name', 'en'));
        $this->assertSame('Autre situation', $categories->last()->translate('name', 'fr'));
        $this->assertSame(range(1, 8), $categories->pluck('position')->all());
    }

    public function test_seeder_does_not_duplicate_existing_categories(): void
    {
        $this->seed(ReportCategorySeeder::class);
        $this->seed(ReportCategorySeeder::class);

        $this->assertSame(8, ReportCategory::query()->count());
    }

    public function test_inactive_categories_are_excluded_from_the_active_scope(): void
    {
        ReportCategory::factory()->create(['position' => 2]);
        ReportCategory::factory()->inactive()->create(['position' => 1]);

        $this->assertSame(2, ReportCategory::query()->count());
        $this->assertSame(1, ReportCategory::query()->active()->count());
    }

    public function test_name_follows_the_current_language(): void
    {
        $category = ReportCategory::factory()->make(['name' => ['fr' => 'Discrimination', 'en' => 'Discrimination (en)']]);

        app()->setLocale('en');
        $this->assertSame('Discrimination (en)', $category->name());

        app()->setLocale('fr');
        $this->assertSame('Discrimination', $category->name());
    }
}
