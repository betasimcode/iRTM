<?php

namespace App\Http\Controllers;

use App\Services\Racing\RaceTimelineService;

class DashboardController extends Controller
{
    public function index()
    {
        return view(
            'dashboard'

        );
    }
}
