<?php

namespace Tests\Feature\Cases;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Privacy\Enums\ConsentPurpose;
use App\Modules\Privacy\Models\Consent;
use App\Modules\Reporting\Models\Report;
use App\Modules\Users\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function confidentialCaseWithConsent(bool $granted = true): CaseFile
    {
        $report = Report::factory()->withIdentity()->create();
        Consent::factory()->forPurpose(ConsentPurpose::IdentityDisclosure)->create(['report_id' => $report->id, 'granted' => $granted, 'granted_at' => $granted ? now() : null]);

        return CaseFile::factory()->create(['report_id' => $report->id]);
    }

    public function test_case_manager_sees_only_assigned_cases(): void
    {
        $manager = $this->userWithRole('case_manager');
        $mine = CaseFile::factory()->assignedTo($manager)->create();
        $other = CaseFile::factory()->assignedTo(User::factory()->create())->create();
        $unassigned = CaseFile::factory()->create();

        $this->assertTrue($manager->can('viewAny', CaseFile::class));
        $this->assertTrue($manager->can('view', $mine));
        $this->assertFalse($manager->can('view', $other));
        $this->assertFalse($manager->can('view', $unassigned));
        $this->assertEqualsCanonicalizing([$mine->id], CaseFile::query()->visibleTo($manager)->pluck('id')->all());
    }

    public function test_supervisor_sees_every_case(): void
    {
        $supervisor = $this->userWithRole('supervisor');
        $cases = [
            CaseFile::factory()->assignedTo(User::factory()->create())->create(),
            CaseFile::factory()->create(),
        ];

        foreach ($cases as $case) {
            $this->assertTrue($supervisor->can('view', $case));
        }

        $this->assertSame(2, CaseFile::query()->visibleTo($supervisor)->count());
    }

    public function test_administrators_and_auditor_see_no_case(): void
    {
        $case = CaseFile::factory()->create();

        foreach (['technical_admin', 'organization_admin', 'auditor'] as $role) {
            $user = $this->userWithRole($role);
            $case->forceFill(['assigned_to_user_id' => $user->id])->save();

            $this->assertFalse($user->can('viewAny', CaseFile::class), $role);
            $this->assertFalse($user->can('view', $case), "{$role} ne doit pas voir un dossier, même affecté");
            $this->assertSame(0, CaseFile::query()->visibleTo($user)->count(), $role);
        }
    }

    public function test_external_third_party_sees_assigned_cases_but_never_identities(): void
    {
        $third = $this->userWithRole('external_third_party');
        $case = $this->confidentialCaseWithConsent();
        $case->forceFill(['assigned_to_user_id' => $third->id])->save();

        $this->assertTrue($third->can('view', $case));
        $this->assertFalse($third->can('viewIdentity', $case));
        $this->assertFalse($third->can('view', CaseFile::factory()->create()));
    }

    public function test_identity_requires_permission_confidential_level_and_consent(): void
    {
        $supervisor = $this->userWithRole('supervisor');

        $this->assertTrue($supervisor->can('viewIdentity', $this->confidentialCaseWithConsent()));
        $this->assertFalse($supervisor->can('viewIdentity', $this->confidentialCaseWithConsent(granted: false)), 'Sans consentement');
        $this->assertFalse($supervisor->can('viewIdentity', CaseFile::factory()->create()), 'Signalement anonyme');

        $manager = $this->userWithRole('case_manager');
        $case = $this->confidentialCaseWithConsent();
        $this->assertFalse($manager->can('viewIdentity', $case), 'Dossier non affecté au gestionnaire');
        $case->forceFill(['assigned_to_user_id' => $manager->id])->save();
        $this->assertTrue($manager->can('viewIdentity', $case->fresh()));
    }
}
