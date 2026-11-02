<?php

namespace Tests\Feature\Core;

use App\Modules\Core\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class HasTranslationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.fallback_locale' => 'fr']);
    }

    public function test_current_locale_is_used_first(): void
    {
        $model = new TranslationFixture(['name' => ['fr' => 'Bonjour', 'en' => 'Hello']]);

        app()->setLocale('en');

        $this->assertSame('Hello', $model->translate('name'));
        $this->assertSame('Bonjour', $model->translate('name', 'fr'));
    }

    public function test_missing_translation_falls_back_to_the_default_locale(): void
    {
        $model = new TranslationFixture(['name' => ['fr' => 'Bonjour', 'en' => 'Hello']]);

        app()->setLocale('wo');

        $this->assertSame('Bonjour', $model->translate('name'));
    }

    public function test_models_may_add_their_own_fallback_locale_before_the_default(): void
    {
        $model = new EnglishFirstTranslationFixture(['name' => ['fr' => 'Bonjour', 'en' => 'Hello']]);

        app()->setLocale('wo');

        $this->assertSame('Hello', $model->translate('name'));
    }

    public function test_first_available_value_is_used_when_no_fallback_matches(): void
    {
        $model = new TranslationFixture(['name' => ['en' => 'Hello', 'fr' => '']]);

        app()->setLocale('wo');

        $this->assertSame('Hello', $model->translate('name'));
        $this->assertNull((new TranslationFixture(['name' => []]))->translate('name'));
        $this->assertNull((new TranslationFixture)->translate('name'));
    }

    public function test_plain_string_attributes_are_returned_as_is(): void
    {
        $this->assertSame('Texte', (new TranslationFixture(['name' => 'Texte']))->translate('name'));
    }
}

class TranslationFixture extends Model
{
    use HasTranslations;

    protected $guarded = [];

    protected $casts = ['name' => 'array'];
}

class EnglishFirstTranslationFixture extends TranslationFixture
{
    protected function translationFallbackLocales(): array
    {
        return ['en', ...parent::translationFallbackLocales()];
    }
}
