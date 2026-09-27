<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Models\SeriesEntry;
use App\Services\CompetitionStrategyService;

class CompetitionStrategyController extends Controller
{
    public function show(Series $series)
    {
        $workspace = workspace();

        $entry = SeriesEntry::query()
            ->with([
                'series.iracingSeries',
                'series.rounds.track',
                'competitionCar',
                'workspace',
            ])
            ->where('series_id', $series->id)
            ->where('workspace_id', $workspace->workspace()->id)
            ->where('status', 'active')
            ->firstOrFail();

        $currentRound = $entry->series->activeRound();

        $reportScope = request(
            'report_scope',
            'season'
        );

        $plan = app(
            CompetitionStrategyService::class
        )->build(
            $entry,
            $currentRound,
            $reportScope
        );

        return view(
            'competitions.strategy',
            compact(
                'series',
                'entry',
                'currentRound',
                'plan',
                'reportScope'
            )
        );
    }
}
