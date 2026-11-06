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
    public function __invoke(): Response
    {
        return Inertia::render('public/Home', [
            'levels' => collect(ConfidentialityLevel::cases())->map(fn (ConfidentialityLevel $level): array => [
                'value' => $level->value,
                'label' => $level->label(),
                'description' => $level->description(),
            ]),
        ]);
    }
}
