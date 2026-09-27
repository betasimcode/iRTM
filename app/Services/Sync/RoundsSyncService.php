<?php

namespace App\Services\Sync;

use App\Models\Series;
use App\Models\SeriesRound;
use Illuminate\Support\Facades\DB;

class RoundsSyncService
{
    /**
     * Sincroniza el calendario completo de una serie.
     */
    public function sync(array $payload): int
    {
        $series = Series::query()
            ->where(
                'iracing_series_id',
                $payload['iracing_series_id']
            )
            ->where(
                'season_year',
                $payload['season_year']
            )
            ->where(
                'season_number',
                $payload['season_number']
            )
            ->firstOrFail();

        DB::transaction(function () use ($series, $payload) {

            SeriesRound::where(
                'series_id',
                $series->id
            )->delete();

            foreach ($payload['rounds'] as $round) {

                SeriesRound::create([

                    'series_id'   => $series->id,

                    'week'        => $round['week'],

                    'week_start'  => $round['week_start'],

                    'week_end'    => $round['week_end'],

                    'circuit_id'  => $round['circuit_id'],

                    'race_type'   => $round['race_type'],

                    'race_length' => $round['race_length']

                ]);
            }

        });

        return $series->id;
    }
}
