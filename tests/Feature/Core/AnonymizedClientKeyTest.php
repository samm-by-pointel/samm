<?php

namespace Tests\Feature\Core;

use App\Modules\Core\Support\AnonymizedClientKey;
use Illuminate\Http\Request;
use Tests\TestCase;

class AnonymizedClientKeyTest extends TestCase
{
    private function requestFrom(string $ip): Request
    {
        return Request::create('/', 'GET', server: ['REMOTE_ADDR' => $ip]);
    }

    public function test_key_is_stable_for_one_address_and_differs_between_addresses(): void
    {
        $this->assertSame(AnonymizedClientKey::for($this->requestFrom('203.0.113.10')), AnonymizedClientKey::for($this->requestFrom('203.0.113.10')));
        $this->assertNotSame(AnonymizedClientKey::for($this->requestFrom('203.0.113.10')), AnonymizedClientKey::for($this->requestFrom('203.0.113.11')));
    }

    public function test_key_does_not_reveal_the_address_and_depends_on_the_scope(): void
    {
        $key = AnonymizedClientKey::for($this->requestFrom('203.0.113.10'), 'form');

        $this->assertStringNotContainsString('203', $key);
        $this->assertStringNotContainsString('113', $key);
        $this->assertSame(32, strlen($key));
        $this->assertNotSame($key, AnonymizedClientKey::for($this->requestFrom('203.0.113.10'), 'submission'));
    }

    public function test_key_changes_with_the_application_key(): void
    {
        $before = AnonymizedClientKey::for($this->requestFrom('203.0.113.10'));

        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

        $this->assertNotSame($before, AnonymizedClientKey::for($this->requestFrom('203.0.113.10')));
    }
}
