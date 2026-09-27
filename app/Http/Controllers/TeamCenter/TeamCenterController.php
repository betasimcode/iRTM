<?php

namespace App\Http\Controllers\TeamCenter;

use App\Http\Controllers\Controller;
use App\Services\TeamCenter\TeamCenterContext;

class TeamCenterController extends Controller
{
    /**
     * TeamCenter dashboard.
     */
    public function dashboard(TeamCenterContext $context)
    {
        return view('teamcenter.dashboard', [
            'team' => $context->team,
        ]);
    }
}
