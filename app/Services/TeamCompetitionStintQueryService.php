<?php

namespace App\Services;

use App\Models\SeriesEntry;
use App\Models\SeriesRound;
use App\Models\Stint;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class TeamCompetitionStintQueryService
{
    /**
     * Construye la consulta base de stints de una competición.
     *
     * Aplica:
     * - pilotos activos
     * - coche de competición
     * - circuito de referencia
     * - report_scope
     */
    public function query(
        SeriesEntry $entry,
        ?SeriesRound $currentRound,
        string $reportScope = 'season'
    ): ?Builder {
        $query = Stint::query();

        return $this->applyCompetitionFilters(
            $query,
            $entry,
            $currentRound,
            $reportScope
        );
    }

    /**
     * Aplica los filtros de competición sobre cualquier Builder de Stint.
     *
     * Este método permite reutilizar exactamente los mismos filtros
     * desde consultas de Stint y desde relaciones como IrSession::stints().
     */
    public function applyCompetitionFilters(
        Builder $query,
        SeriesEntry $entry,
        ?SeriesRound $currentRound,
        string $reportScope = 'season'
    ): ?Builder {
        if (! in_array(
            $reportScope,
            ['week', 'season', 'all'],
            true
        )) {
            $reportScope = 'season';
        }

        $displayRound = $entry->series->displayRound();

        /*
         * WEEK
         *
         * Para semana necesitamos obligatoriamente el round actual
         * y su circuito.
         */
        if (
            $reportScope === 'week'
            && ! $currentRound?->track
        ) {
            return null;
        }

        /*
         * WEEK  -> currentRound
         * SEASON/ALL -> displayRound
         */
        $referenceRound = $reportScope === 'week'
            ? $currentRound
            : $displayRound;

        if (! $referenceRound?->track) {
            return null;
        }

        /*
         * Pilotos activos de la participación.
         */
        $driverIds = $entry->members
            ->where('status', 'active')
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        if ($driverIds->isEmpty()) {
            return null;
        }

        /*
         * Coche de competición.
         */
        $carId = $entry->competitionCar?->iracing_car_id;

        /*
         * Circuito de referencia.
         */
        $trackId = $referenceRound
            ->track
            ->iracing_track_id;

        if (! $carId || ! $trackId) {
            return null;
        }

        /*
         * FILTRO BASE
         */
        $query
            ->whereIn('user_id', $driverIds)
            ->where('car_id', $carId)
            ->where('track_id', $trackId);

        /*
         * FILTRO TEMPORAL
         */
        if ($reportScope === 'week') {
            $weekStart = Carbon::parse(
                $currentRound->week_start
            )->startOfDay();

            $weekEnd = Carbon::parse(
                $currentRound->week_end
            )->startOfDay();

            $query
                ->where(
                    'ir_stints.created_at',
                    '>=',
                    $weekStart
                )
                ->where(
                    'ir_stints.created_at',
                    '<',
                    $weekEnd
                );
        }

        if ($reportScope === 'season') {
            $seasonStart = $entry
                ->series
                ->rounds
                ->min('week_start');

            if ($seasonStart) {
                $seasonPreparationStart = Carbon::parse(
                    $seasonStart
                )
                    ->subDays(7)
                    ->startOfDay();

                $query->where(
                    'ir_stints.created_at',
                    '>=',
                    $seasonPreparationStart
                );
            }
        }

        /*
         * ALL
         *
         * No añadimos filtro temporal.
         */

        return $query;
    }
}
