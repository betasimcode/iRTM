<?php

namespace App\Data\Series;

class BrowserSeriesData
{
    public function __construct(

        public readonly int $id,

        public readonly string $title,

        public readonly string $season,

        public readonly ?int $currentWeek,

        public readonly int $weeks,

        public readonly bool $registered,

        public readonly ?string $competitionCar,

        public readonly ?string $category,

    ) {
    }

    /**
     * Convierte el DTO en array.
     */
    public function toArray(): array
    {
        return [

            'id' => $this->id,

            'title' => $this->title,

            'season' => $this->season,

            'current_week' => $this->currentWeek,

            'weeks' => $this->weeks,

            'registered' => $this->registered,

            'competition_car' => $this->competitionCar,

            'category' => $this->category,

        ];
    }
}
