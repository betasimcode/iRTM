<?php

namespace App\Data\Series;

class BrowserResponseData
{
    public function __construct(

        /**
         * Workspace activo.
         */
        public readonly array $workspace,

        /**
         * Temporada seleccionada.
         */
        public readonly array $season,

        /**
         * Resumen del Browser.
         */
        public readonly array $summary,

        /**
         * Opciones disponibles
         * para los filtros.
         */
        public readonly array $filters,

        /**
         * Series disponibles.
         *
         * @var BrowserSeriesData[]
         */
        public readonly array $series,

    ) {
    }

    /**
     * Convierte el DTO en array.
     */
    public function toArray(): array
    {
        return [

            'workspace' => $this->workspace,

            'season' => $this->season,

            'summary' => $this->summary,

            'filters' => $this->filters,

            'series' => array_map(

                fn (BrowserSeriesData $series) => $series->toArray(),

                $this->series

            ),

        ];
    }
}
