<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class TranslationKeysTest extends TestCase
{
    /**
     * Every translation key used in a Blade view must exist in lang/fr (CLAUDE.md, rule « Multilingue »).
     */
    public function test_every_translation_key_used_in_views_exists_in_french(): void
    {
        $missing = [];

        foreach (File::allFiles(resource_path('views')) as $view) {
            // Clés littérales seulement : une clé suivie d'une concaténation (« 'audit.x.'.$y ») est construite dynamiquement.
            preg_match_all("/(?:__|@lang|trans)\\(\\s*'([a-z0-9_.-]+)'\\s*[,)]/i", $view->getContents(), $matches);

            foreach ($matches[1] as $key) {
                if (! Lang::has($key, 'fr', false)) {
                    $missing[] = $view->getRelativePathname().' : '.$key;
                }
            }
        }

        $this->assertSame([], $missing, "Clés de traduction manquantes dans lang/fr :\n".implode("\n", $missing));
    }

    /**
     * Every file published in lang/en must have a French counterpart with exactly the same keys.
     */
    public function test_every_english_language_file_has_a_complete_french_counterpart(): void
    {
        $differences = [];

        foreach (File::files(lang_path('en')) as $file) {
            $group = $file->getFilenameWithoutExtension();
            $english = array_keys(Arr::dot(Lang::get($group, [], 'en')));
            $french = array_keys(Arr::dot(Lang::get($group, [], 'fr')));

            foreach (array_diff($english, $french) as $key) {
                $differences[] = "{$group}.{$key} : absente en français";
            }

            foreach (array_diff($french, $english) as $key) {
                $differences[] = "{$group}.{$key} : absente en anglais";
            }
        }

        $this->assertSame([], $differences, "Fichiers de langue en/fr désynchronisés :\n".implode("\n", $differences));
    }

    public function test_every_french_public_key_has_an_english_translation(): void
    {
        $french = array_keys(Arr::dot(Lang::get('public', [], 'fr')));
        $english = array_keys(Arr::dot(Lang::get('public', [], 'en')));

        $this->assertSame([], array_values(array_diff($french, $english)), 'Clés présentes en français mais absentes en anglais.');
    }
}
