<?php

namespace App\Services\Iracing;

interface IracingStandingsClient
{
    /**
     * Obtiene los metadatos y todos los pilotos
     * de una clasificación oficial.
     *
     * @return array{
     *   metadata: array,
     *   drivers: array
     * }
     */
    public function getStandings(
        int $seasonId,
        int $carClassId,
        int $division
    ): array;

    /**
     * Obtiene las divisiones disponibles
     * para la temporada y clase.
     */
    public function getAvailableDivisions(
        int $seasonId,
        int $carClassId
    ): array;

    /**
     * Comprueba si iRacing ha finalizado
     * oficialmente la temporada.
     */
    public function isSeasonFinalized(
        int $seasonId
    ): bool;
}
