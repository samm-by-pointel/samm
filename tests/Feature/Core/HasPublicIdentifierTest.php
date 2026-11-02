<?php

namespace Tests\Feature\Core;

use App\Modules\Core\Concerns\HasPublicIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class HasPublicIdentifierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('public_identifier_fixtures', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('label');
            $table->timestamps();
        });
    }

    public function test_uuid_is_generated_on_creation_and_internal_id_is_kept(): void
    {
        $model = PublicIdentifierFixture::create(['label' => 'premier']);

        $this->assertTrue(Str::isUuid($model->uuid));
        $this->assertIsInt($model->id);
        $this->assertSame(1, $model->id);
    }

    public function test_uuids_are_unique_across_records(): void
    {
        $first = PublicIdentifierFixture::create(['label' => 'premier']);
        $second = PublicIdentifierFixture::create(['label' => 'second']);

        $this->assertNotSame($first->uuid, $second->uuid);
    }

    public function test_route_key_is_the_uuid_not_the_internal_id(): void
    {
        $model = PublicIdentifierFixture::create(['label' => 'premier']);

        $this->assertSame('uuid', $model->getRouteKeyName());
        $this->assertSame($model->uuid, $model->getRouteKey());
        $this->assertTrue($model->is(PublicIdentifierFixture::query()->where('uuid', $model->uuid)->firstOrFail()));
    }
}

class PublicIdentifierFixture extends Model
{
    use HasPublicIdentifier;

    protected $table = 'public_identifier_fixtures';

    protected $guarded = [];
}
