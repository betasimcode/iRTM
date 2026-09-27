<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Services\Racing\RaceTimelineService;

class SeriesReferenceController extends Controller
{
    public function show(Series $series)
    {
        $series->load([
            'rounds.track',
            'car',
            'iracingSeries',
        ]);

        $raceTimeline = app(RaceTimelineService::class)
            ->build(
                $series->id,
                false
            );

        $workspace = workspace();

        $user = $workspace->user();

        $stats = $series->strategicStats(
            $user->id,
            request('mode', 'season')
        );

        $plan = $series->racePlan(
            $user->id
        );

        $activeRound = $series->activeRound();

        return view('series.show', compact(
            'series',
            'stats',
            'plan',
            'raceTimeline',
            'activeRound'
        ));
    }
}
