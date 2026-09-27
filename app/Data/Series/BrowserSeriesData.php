<?php

namespace App\Data\Series;

class BrowserSeriesData
{
    public function __construct(

        /**
         * Serie
         */
        public readonly int $id,

        public readonly string $name,

        public readonly ?string $logo,

        /**
         * Temporada
         */
        public readonly int $seasonYear,

        public readonly int $seasonQuarter,

        /**
         * Información deportiva
         */
        public readonly ?string $license,

        public readonly ?string $category,

        public readonly ?string $raceType,

        public readonly ?string $startType,

        public readonly ?string $raceLength,

        /**
         * Estado del Workspace
         */
        public readonly bool $registered,

        public readonly ?int $entryId,

        /**
         * Información del registro
         */
        public readonly ?string $car,

        public readonly int $drivers,

    ) {
    }

    public function toArray(): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'logo' => $this->logo,

            'season_year' => $this->seasonYear,

            'season_quarter' => $this->seasonQuarter,

            'license' => $this->license,

            'category' => $this->category,

            'race_type' => $this->raceType,

            'start_type' => $this->startType,

            'race_length' => $this->raceLength,

            'registered' => $this->registered,

            'entry_id' => $this->entryId,

            'car' => $this->car,

            'drivers' => $this->drivers,

        ];
    }
}
