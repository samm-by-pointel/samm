<?php

namespace App\Modules\Cases\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cases\Models\CaseFile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Entry page of the management area: a few counters, no content.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $canViewCases = $user->can('viewAny', CaseFile::class);

        return view('manage.dashboard', [
            'canViewCases' => $canViewCases,
            'counters' => $canViewCases ? [
                'mine_open' => CaseFile::query()->open()->assignedTo($user)->count(),
                'unassigned_open' => CaseFile::query()->visibleTo($user)->open()->unassigned()->count(),
                'visible_total' => CaseFile::query()->visibleTo($user)->count(),
            ] : null,
        ]);
    }
}
