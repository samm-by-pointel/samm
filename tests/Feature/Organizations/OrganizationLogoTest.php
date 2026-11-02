<?php

namespace Tests\Feature\Organizations;

use App\Modules\Organizations\Models\Organization;
use App\Modules\Users\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrganizationLogoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);
        Storage::fake('local');
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('organization_admin');
        Organization::factory()->create(['name' => 'Organisation fictive']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge(['name' => 'Organisation fictive', 'default_locale' => 'fr'], $overrides);
    }

    public function test_png_logo_is_stored_privately_served_by_a_route_and_shown_in_headers(): void
    {
        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload(['logo' => UploadedFile::fake()->image('logo.png', 120, 40)]))
            ->assertRedirect(route('manage.organization.edit'))
            ->assertSessionHasNoErrors();

        $organization = Organization::query()->sole();
        $this->assertNotNull($organization->logo_path);
        $this->assertStringStartsWith('branding/', $organization->logo_path);
        Storage::disk('local')->assertExists($organization->logo_path);
        $this->assertFileDoesNotExist(public_path($organization->logo_path));

        $this->get(route('organization.logo'))->assertOk()->assertHeader('Cache-Control', 'max-age=3600, public');

        auth()->logout();
        $logoUrl = route('organization.logo', ['v' => $organization->updated_at->timestamp]);
        $this->get(route('public.home'))->assertInertia(fn (Assert $page) => $page->where('site.logoUrl', $logoUrl));
        $this->actingAs($this->admin)->get(route('manage.dashboard'))->assertInertia(fn (Assert $page) => $page->where('site.logoUrl', $logoUrl));
    }

    public function test_svg_and_disguised_files_are_refused(): void
    {
        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload(['logo' => $svg]))->assertSessionHasErrors('logo');

        // A real temporary file: the fake upload helper guesses the type from the name,
        // whereas the server checks the content signature, which is what we test here.
        $disguisedPath = tempnam(sys_get_temp_dir(), 'samm-logo');
        file_put_contents($disguisedPath, '<?php echo 1;');
        $disguised = new UploadedFile($disguisedPath, 'logo.png', 'image/png', null, true);
        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload(['logo' => $disguised]))->assertSessionHasErrors('logo');
        @unlink($disguisedPath);

        $tooBig = UploadedFile::fake()->image('logo.png')->size(2048);
        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload(['logo' => $tooBig]))->assertSessionHasErrors('logo');

        $this->assertNull(Organization::query()->sole()->logo_path);
        $this->get(route('organization.logo'))->assertNotFound();
    }

    public function test_logo_can_be_removed(): void
    {
        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload(['logo' => UploadedFile::fake()->image('logo.png')]));
        $path = Organization::query()->sole()->logo_path;

        $this->actingAs($this->admin)->put(route('manage.organization.update'), $this->payload(['remove_logo' => '1']))->assertSessionHasNoErrors();

        $this->assertNull(Organization::query()->sole()->logo_path);
        Storage::disk('local')->assertMissing($path);
        $this->get(route('organization.logo'))->assertNotFound();
    }
}
