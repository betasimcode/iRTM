<?php

namespace App\Repositories;

use App\Data\Series\BrowserResponseData;
use App\Data\Series\BrowserSeriesData;
use App\Mappers\Series\BrowserSeriesMapper;
use App\Models\IracingSerie;
use App\Models\SeriesEntry;
use App\Models\Workspace;

class SeriesBrowserRepository
{
    public function __construct(
        protected BrowserSeriesMapper $mapper
    ) {
    }

    /**
     * Obtiene el estado completo del Browser de Series.
     */
    public function browse(
        Workspace $workspace,
        array $filters = []
    ): BrowserResponseData {

        /*
        |--------------------------------------------------------------------------
        | Series oficiales
        |--------------------------------------------------------------------------
        */

        $series = IracingSerie::query()

            ->orderBy('name')

            ->get();

        /*
        |--------------------------------------------------------------------------
        | Inscripciones del Workspace
        |--------------------------------------------------------------------------
        */

        $entries = SeriesEntry::query()

            ->where('workspace_id', $workspace->id)

            ->with('members')

            ->get()

            ->keyBy('series_id');

        /*
        |--------------------------------------------------------------------------
        | Browser Cards
        |--------------------------------------------------------------------------
        */

        $cards = [];

        foreach ($series as $serie) {

            $cards[] = $this->mapper->map(

                $serie,

                $entries->get($serie->id)

            );

        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return new BrowserResponseData(

            workspace: [

                'id' => $workspace->id,

                'name' => $workspace->name,

                'type' => $workspace->type,

            ],

            season: [

                // TODO:
                // Obtener temporada activa desde iRacing.

                'year' => now()->year,

                'quarter' => (int) ceil(now()->month / 3),

            ],

            summary: [

                'total_series' => count($cards),

                'registered' => $entries->count(),

                'available' => count($cards) - $entries->count(),

            ],

            filters: [

                // TODO:
                // Generar dinámicamente.

                'categories' => [],

                'licenses' => [],

            ],

            series: $cards,

        );
    }
}
