<?php

namespace App\Mappers\Series;

use App\Data\Series\BrowserSeriesData;
use App\Models\IracingSerie;
use App\Models\SeriesEntry;
use App\Models\Workspace;

class BrowserSeriesMapper
{
    /**
     * Convierte una Serie en una Card del Browser.
     */
    /**
 * Convierte una Serie en una Card del Browser.
 */
    public function map(

        IracingSerie $series,

        ?SeriesEntry $entry = null

    ): BrowserSeriesData {

        return new BrowserSeriesData(

            id: $series->id,

            name: $series->name,

            logo: $series->logo_path,

            seasonYear: now()->year,

            seasonQuarter: ceil(now()->month / 3),

            license: $series->license,

            category: $series->category,

            raceType: $series->race_type,

            startType: $series->start_type,

            raceLength: (string) $series->race_length,

            registered: $entry !== null,

            entryId: $entry?->id,

            car: null,

            drivers: $entry?->members->count() ?? 0,

        );
    }
}
