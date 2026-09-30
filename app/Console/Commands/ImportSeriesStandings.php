<?php

namespace App\Console\Commands;

use App\Models\Series;
use App\Services\SeriesStandingsSyncService;
use Illuminate\Console\Command;

class ImportSeriesStandings extends Command
{
    protected $signature = 'irteam:standings-import
        {series_id}
        {csv}
        {--season=6507}
        {--iracing-series=65}
        {--class=22}';

    protected $description =
        'Importa una clasificación de temporada desde CSV';

    public function handle(
        SeriesStandingsSyncService $service
    ): int {
        $series = Series::find($this->argument('series_id'));

        if (!$series) {
            $this->error('No existe esa serie local.');

            return self::FAILURE;
        }

        $iracingSeasonId = (int) $this->option('season');
        $iracingSeriesId = (int) $this->option('iracing-series');
        $carClassId = (int) $this->option('class');

        // Comprobar que la temporada oficial coincide con la local.
        if ((int) $series->iracing_season_id !== $iracingSeasonId) {
            $this->error(
                'La temporada oficial indicada no coincide con la temporada local.'
            );

            $this->line(
                'Temporada local: ' . $series->iracing_season_id
            );

            $this->line(
                'Temporada recibida: ' . $iracingSeasonId
            );

            return self::FAILURE;
        }

        // Comprobar que la serie oficial coincide con el catálogo local.
        $iracingSerie = $series->iracingSerie;

        if (
            ! $iracingSerie ||
            (int) $iracingSerie->iracing_series_id !== $iracingSeriesId
        ) {
            $this->error(
                'La serie oficial indicada no coincide con la serie local.'
            );

            return self::FAILURE;
        }


        $path = $this->argument('csv');

        if (!is_file($path)) {
            $this->error('No existe el archivo CSV: ' . $path);

            return self::FAILURE;
        }

        $csvContent = file_get_contents($path);

        if ($csvContent === false) {
            $this->error('No se pudo leer el CSV.');

            return self::FAILURE;
        }

        $sync = $service->importCsv(
            series: $series,
            iracingSeasonId: $iracingSeasonId,
            iracingSeriesId: $iracingSeriesId,
            carClassId: $carClassId,
            csvContent: $csvContent,
            syncType: 'manual'
        );

        $this->info('Importación completada.');
        $this->info('Sync ID: ' . $sync->id);
        $this->info(
            'Clasificaciones: ' .
            $sync->classifications_processed
        );
        $this->info(
            'Registros de pilotos: ' .
            $sync->drivers_imported
        );

        return self::SUCCESS;
    }
}
