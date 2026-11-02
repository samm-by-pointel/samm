<?php

namespace App\Modules\Cases\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cases\Models\CaseFile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Entry page of the management area: a few counters, no content.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $canViewCases = $user->can('viewAny', CaseFile::class);

        return Inertia::render('manage/Dashboard', [
            'canViewCases' => $canViewCases,
            'urls' => [
                'mine' => route('manage.cases.index', ['assignment' => 'mine']),
                'unassigned' => route('manage.cases.index', ['assignment' => 'unassigned']),
                'all' => route('manage.cases.index'),
            ],
            'counters' => $canViewCases ? [
                'mine_open' => CaseFile::query()->open()->assignedTo($user)->count(),
                'unassigned_open' => CaseFile::query()->visibleTo($user)->open()->unassigned()->count(),
                'visible_total' => CaseFile::query()->visibleTo($user)->count(),
            ] : null,
        ]);
    }
}
