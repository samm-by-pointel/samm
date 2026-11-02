<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Enums\ConfidentialityLevel;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Page d'accueil de l'espace public.
 */
class HomeController extends Controller
{
    /**
     * Photos facultatives déposées dans public/images/site : sans fichier,
     * la page s'en passe et garde sa mise en page.
     */
    private const IMAGES = [
        'hero' => 'images/site/accueil.jpg',
        'team' => 'images/site/equipe.jpg',
    ];

    public function __invoke(): Response
    {
        return Inertia::render('public/Home', [
            'levels' => collect(ConfidentialityLevel::cases())->map(fn (ConfidentialityLevel $level): array => [
                'value' => $level->value,
                'label' => $level->label(),
                'description' => $level->description(),
            ]),
            'images' => collect(self::IMAGES)->map(fn (string $path): ?string => file_exists(public_path($path)) ? asset($path) : null),
        ]);
    }
}
