<?php

namespace Tests\Feature\Workflow;

use App\Modules\Workflow\Enums\CaseStatus;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class StatusBadgeComponentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('fr');
    }

    public function test_public_rendering_of_an_archived_case_reads_closed(): void
    {
        $html = Blade::render('<x-samm.status-badge :status="$status" :public="true" />', ['status' => CaseStatus::Archived]);

        $this->assertStringContainsString('Clôturé', $html);
        $this->assertStringContainsString('samm-badge-cloture', $html);
        $this->assertStringNotContainsString('Archivé', $html);
    }

    public function test_manager_rendering_shows_the_real_status_and_accepts_extra_classes(): void
    {
        $html = Blade::render('<x-samm.status-badge :status="$status" class="ms-2" />', ['status' => CaseStatus::Archived]);

        $this->assertStringContainsString('Archivé', $html);
        $this->assertStringContainsString('samm-badge-archive', $html);
        $this->assertStringContainsString('ms-2', $html);
    }

    public function test_label_is_always_written_out(): void
    {
        foreach (CaseStatus::cases() as $status) {
            $html = Blade::render('<x-samm.status-badge :status="$status" />', ['status' => $status]);

            $this->assertStringContainsString(e($status->label()), $html);
        }
    }
}
