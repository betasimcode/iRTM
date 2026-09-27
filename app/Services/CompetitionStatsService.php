<?php

namespace App\Services;

use App\Models\SeriesEntry;
use App\Models\Stint;
use Carbon\Carbon;

class CompetitionStatsService
{
    public function build(
        SeriesEntry $entry,
        $currentRound,
        string $mode = 'season'
    ): array {
        if (!$currentRound || !$currentRound->track) {
            return $this->emptyStats();
        }

        $car = $entry->competitionCar;

        if (!$car || !$car->iracing_car_id) {
            return $this->emptyStats();
        }

        $query = Stint::with('laps')
            ->where('user_id', $entry->workspace->owner_user_id)
            ->where('car_id', $car->iracing_car_id)
            ->where(
                'track_id',
                $currentRound->track->iracing_track_id
            );

        /*
         * SEMANA
         *
         * Solo stints realizados durante la week actual.
         */
        if ($mode === 'week') {

            $query->whereBetween('created_at', [
                $currentRound->week_start,
                $currentRound->week_end,
            ]);
        }

        /*
         * SEASON
         *
         * Mantenemos el comportamiento del sistema anterior:
         * desde el comienzo de la temporada.
         *
         * Como el coche + circuito ya están filtrados,
         * obtenemos todos los entrenamientos realizados
         * en este circuito durante la temporada.
         */
        if ($mode === 'season') {

            $seasonStart = $entry->series
                ->rounds
                ->min('week_start');

            if ($seasonStart) {
                $query->where(
                    'created_at',
                    '>=',
                    $seasonStart
                );
            }
        }

        /*
         * HISTORIC
         *
         * No aplicamos filtro temporal.
         * Conservamos todo el histórico del piloto
         * para esta combinación coche + circuito.
         */

        $stints = $query->get();

        if ($stints->isEmpty()) {
            return $this->emptyStats();
        }

        /*
         * TOTAL STINTS
         */
        $totalStints = $stints->count();

        /*
         * BEST LAP
         */
        $bestLap = $stints
            ->flatMap->laps
            ->where('lap_time', '>', 0)
            ->min('lap_time');

        /*
         * REPRESENTATIVE PACE / TARGET
         *
         * Mismo criterio que strategicStats():
         *
         * - mínimo 3 vueltas
         * - excluir vueltas > 200 s
         * - tomar el mejor tiempo del stint
         * - aceptar hasta el 107 %
         * - tomar el 30 % superior
         */
        $paceSamples = $stints
            ->map(function ($stint) {

                if (
                    !$stint->laps ||
                    $stint->laps->isEmpty()
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

                $stintBestLap = $laps->min();

                $threshold = $stintBestLap * 1.07;

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

        $representativePace = $paceSamples->isNotEmpty()
            ? $paceSamples->avg()
            : null;

        /*
         * AVERAGE FUEL
         *
         * Igual que strategicStats():
         * media de fuel_consumed de las vueltas válidas.
         */
        $avgFuel = $stints
            ->flatMap->laps
            ->where('fuel_consumed', '>', 0)
            ->avg('fuel_consumed');

        /*
         * BEST LONG RUN
         *
         * Número máximo de vueltas completadas
         * dentro de un stint.
         */
        $longRunBest = $stints
            ->map(fn ($stint) => $stint->laps->count())
            ->max();

        return [
            'total_stints' => $totalStints,
            'best_lap' => $bestLap,
            'representative_pace' => $representativePace,
            'avg_fuel' => $avgFuel,
            'long_run_best' => $longRunBest,
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
        ];
    }
}
