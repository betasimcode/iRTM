<?php

namespace App\Data\SeriesEntry;

class RegisterSeriesEntryData
{
    public function __construct(

        public readonly int $workspaceId,

        public readonly int $seriesId,

        public readonly int $competitionCarId,

        /**
         * IDs de los pilotos seleccionados.
         */
        public readonly array $members,

        /**
         * Usuario que realiza la inscripción.
         */
        public readonly int $createdBy,

    ) {
    }

    /**
     * Construye el DTO desde un array.
     */
    public static function from(array $data): self
    {
        return new self(

            workspaceId: (int) $data['workspace_id'],

            seriesId: (int) $data['series_id'],

            competitionCarId: (int) $data['competition_car_id'],

            members: $data['members'] ?? [],

            createdBy: (int) $data['created_by'],

        );
    }
}
