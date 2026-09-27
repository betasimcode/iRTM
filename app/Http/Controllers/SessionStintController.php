<?php

namespace App\Http\Controllers;

use App\Models\IrSession;
use App\Models\Series;
use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\IracingSerie;
use App\Models\Track;
use App\Models\User;
use App\Models\Stint;
use App\Models\TeamCarSerie;
use App\Models\SeriesEntry;
use App\Models\SeriesRound;
use Carbon\Carbon;

class SessionStintController extends Controller
{

    public function index(IrSession $session)
    {
        $stints = $session->stints()
            ->with('laps')
            ->latest()
            ->get();

        return view('sessions.stints.index', compact('session','stints'));
    }


    public function show(IrSession $session, Stint $stint)
    {
        $stint->load(['laps']);

        return view('sessions.stints.show', compact('session','stint'));
    }













}
