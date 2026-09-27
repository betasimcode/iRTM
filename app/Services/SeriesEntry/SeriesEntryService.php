<?php

namespace App\Services\SeriesEntry;

use App\Data\SeriesEntry\RegisterSeriesEntryData;
use App\Models\SeriesEntry;
use App\Repositories\SeriesEntryRepository;

class SeriesEntryService
{
    public function __construct(
        protected SeriesEntryRepository $repository
    ) {
    }

    /**
     * Registra un Workspace en una Serie.
     */
    public function register(
        RegisterSeriesEntryData $data
    ): SeriesEntry {

        /*
        |--------------------------------------------------------------------------
        | Ya existe una inscripción
        |--------------------------------------------------------------------------
        */

        $exists = $this->repository
            ->findByWorkspaceAndSeries(
                $data->workspaceId,
                $data->seriesId
            );

        if ($exists) {

            throw new \DomainException(
                'El Workspace ya está inscrito en esta Serie.'
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Registro
        |--------------------------------------------------------------------------
        */

        return $this->repository->register(
            $data
        );

    }
}
