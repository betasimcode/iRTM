<?php

namespace App\Services\Sync;

use App\Models\IracingSerie;
use App\Models\Series;

class SeriesSyncService
{
    /**
     * Sincroniza una serie oficial de iRacing.
     */
    public function sync(array $data): Series
    {
        $iracingSeries = IracingSerie::findOrFail(
            $data['iracing_series_id']
        );

        return Series::updateOrCreate(

            [
                'iracing_series_id' => $iracingSeries->id,
                'season_year'       => $data['season_year'],
                'season_number'     => $data['season_number'],
            ],

            [
                'name'              => $iracingSeries->name,
                'status'            => $data['status'],
            ]

        );
    }
}
