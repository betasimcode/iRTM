<?php

namespace App\Services;

use App\Models\SeriesEntry;
use App\Models\SeriesRound;
use App\Services\TeamCompetitionStintQueryService;

use Illuminate\Support\Collection;

class TeamCompetitionStatsService
{
    public function build(
        SeriesEntry $entry,
        ?SeriesRound $currentRound,
        string $reportScope = 'season'
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Round context
        |--------------------------------------------------------------------------
        |
        | currentRound = ronda que está sucediendo ahora.
        | displayRound = ronda actual, próxima o última.
        |
        */

        $displayRound = $entry->series->displayRound();

        /*
        |--------------------------------------------------------------------------
        | Week requires an active round
        |--------------------------------------------------------------------------
        */

        if ($reportScope === 'week' && ! $currentRound?->track) {
            return $this->emptyStats();
        }

        /*
        |--------------------------------------------------------------------------
        | Competition track context
        |--------------------------------------------------------------------------
        */

        $referenceRound = $reportScope === 'week'
            ? $currentRound
            : $displayRound;

        if (! $referenceRound?->track) {
            return $this->emptyStats();
        }

        /*
        |--------------------------------------------------------------------------
        | Team competition members
        |--------------------------------------------------------------------------
        |
        | Solo pilotos activos en ESTA SeriesEntry.
        |
        */

        $driverIds = $entry->members
            ->where('status', 'active')
            ->pluck('user_id')
            ->filter()
            ->values();

        if ($driverIds->isEmpty()) {
            return $this->emptyStats();
        }

        /*
        |--------------------------------------------------------------------------
        | Competition context
        |--------------------------------------------------------------------------
        */

        $carId = $entry->competitionCar?->iracing_car_id;

        $trackId = $referenceRound
            ->track
            ->iracing_track_id;

        if (! $carId || ! $trackId) {
            return $this->emptyStats();
        }

        /*
        |--------------------------------------------------------------------------
        | Base Team Stint query
        |--------------------------------------------------------------------------
        |
        | Los datos pertenecen a los usuarios que participan
        | activamente en esta SeriesEntry.
        |
        */

        /*
|--------------------------------------------------------------------------
| Competition stint query
|--------------------------------------------------------------------------
|
| The competition query is shared with the Sessions and
| Stints controllers.
|
*/

$query = app(
    TeamCompetitionStintQueryService::class
)->query(
    $entry,
    $currentRound,
    $reportScope
);

if (! $query) {
    return $this->emptyStats();
}

/*
|--------------------------------------------------------------------------
| Load stints
|--------------------------------------------------------------------------
*/

$stints = $query
    ->with('laps')
    ->get();

if ($stints->isEmpty()) {
    return $this->emptyStats();
}

        /*
        |--------------------------------------------------------------------------
        | TOTAL STINTS
        |--------------------------------------------------------------------------
        */

        $totalStints = $stints->count();

        /*
        |--------------------------------------------------------------------------
        | BEST LAP
        |--------------------------------------------------------------------------
        */

        $bestLap = $stints
            ->flatMap->laps
            ->where('lap_time', '>', 0)
            ->min('lap_time');

        /*
        |--------------------------------------------------------------------------
        | REPRESENTATIVE PACE
        |--------------------------------------------------------------------------
        |
        | Same 107% methodology used by Driver.
        | Calculated per stint, then averaged across Team stints.
        |
        */

        $paceSamples = $stints
            ->map(function ($stint) {

                if (
                    ! $stint->laps
                    || $stint->laps->isEmpty()
                ) {
                    return null;
                }

                $laps = $stint->laps
                    ->pluck('lap_time')
                    ->filter(function ($lapTime) {

                        return $lapTime > 0
                            && $lapTime < 200;
                    })
                    ->values();

                if ($laps->count() < 3) {
                    return null;
                }

                $bestLap = $laps->min();

                $threshold = $bestLap * 1.07;

                $filtered = $laps
                    ->filter(function ($lapTime) use ($threshold) {

                        return $lapTime <= $threshold;
                    })
                    ->values();

                if ($filtered->count() < 3) {
                    return null;
                }

                $sorted = $filtered
                    ->sort()
                    ->values();

                $topCount = max(
                    3,
                    ceil($sorted->count() * 0.3)
                );

                return $sorted
                    ->take($topCount)
                    ->avg();

            })
            ->filter();

        $representativePace =
            $paceSamples->isNotEmpty()
                ? $paceSamples->avg()
                : null;

        /*
        |--------------------------------------------------------------------------
        | FUEL
        |--------------------------------------------------------------------------
        */

        $avgFuel = $stints
            ->flatMap->laps
            ->where('fuel_consumed', '>', 0)
            ->avg('fuel_consumed');

        /*
        |--------------------------------------------------------------------------
        | BEST STINT
        |--------------------------------------------------------------------------
        */

        $longRunBest = $stints
            ->map(
                fn ($stint) =>
                    $stint->laps->count()
            )
            ->max();

        /*
        |--------------------------------------------------------------------------
        | BASIC ALERTS
        |--------------------------------------------------------------------------
        */

        $alerts = [];

        if ($totalStints < 1) {

            $alerts[] = [
                'type' => 'warning',
                'message' =>
                    'Pocos stints registrados para análisis fiable',
            ];
        }

        if ($avgFuel && $avgFuel > 4) {

            $alerts[] = [
                'type' => 'warning',
                'message' =>
                    'Consumo elevado detectado',
            ];
        }

        return [
            'total_stints' => $totalStints,
            'best_lap' => $bestLap,
            'representative_pace' => $representativePace,
            'avg_fuel' => $avgFuel,
            'long_run_best' => $longRunBest,
            'alerts' => $alerts,
        ];
    }

    private function emptyStats(): array
    {
        return [
            'total_stints' => 0,
            'best_lap' => null,
            'representative_pace' => null,
            'avg_fuel' => null,
            'long_run_best' => null,
            'alerts' => [],
        ];
    }
}
