<?php

namespace App\Services\Iracing;

use App\Models\Series;
use App\Models\SeriesStanding;
use App\Models\SeriesStandingConfig;
use App\Models\SeriesStandingDriver;
use App\Models\SeriesStandingSnapshot;
use App\Models\SeriesStandingSync;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class SeriesStandingsSyncService
{
    public function __construct(
        protected IracingStandingsClient $client
    ) {}

    /**
     * Sincroniza todas las clases habilitadas
     * de una temporada local.
     */
    public function syncSeason(
        Series $series,
        string $syncType = 'weekly'
    ): void {
        $lockKey = "series-standings:{$series->id}";

        $lock = Cache::lock($lockKey, 1800);

        if (! $lock->get()) {
            Log::warning(
                'Standings sync skipped: lock already held.',
                ['series_id' => $series->id]
            );

            return;
        }

        try {
            $configs = SeriesStandingConfig::query()
                ->where('series_id', $series->id)
                ->where('enabled', true)
                ->get();

            foreach ($configs as $config) {
                $this->syncClass(
                    $series,
                    $config,
                    $syncType
                );
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * Sincroniza Overall y todas las divisiones
     * descubiertas para una clase.
     */
    protected function syncClass(
        Series $series,
        SeriesStandingConfig $config,
        string $syncType
    ): void {
        $sync = SeriesStandingSync::create([
            'series_id' => $series->id,
            'car_class_id' => $config->car_class_id,
            'sync_type' => $syncType,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $seasonId = (int) $series->iracing_season_id;

            if (! $seasonId) {
                throw new RuntimeException(
                    'La temporada no tiene iracing_season_id.'
                );
            }

            $divisions = $this->client
                ->getAvailableDivisions(
                    $seasonId,
                    $config->car_class_id
                );

            $divisions = collect($divisions)
                ->map(fn ($division) => (int) $division)
                ->unique()
                ->sort()
                ->values();

            /*
             * Overall se procesa primero.
             * Su valor de división es NULL.
             */
            $classifications = collect([
                [
                    'scope' => 'overall',
                    'division' => null,
                    'division_key' => -1,
                ],
            ]);

            foreach ($divisions as $division) {
                $classifications->push([
                    'scope' => 'division',
                    'division' => $division,
                    'division_key' => $division,
                ]);
            }

            $processed = 0;
            $imported = 0;
            $sourceLastUpdated = null;

            foreach ($classifications as $classification) {
                $standing = $this->getOrCreateStanding(
                    $series,
                    $config,
                    $classification
                );

                /*
                 * Una clasificación finalizada no se
                 * modifica en sincronizaciones ordinarias.
                 */
                if (
                    $standing->isFinalized()
                    && $syncType !== 'manual'
                ) {
                    continue;
                }

                $division = $classification['division'];

                /*
                 * El cliente debe interpretar correctamente
                 * la consulta de Overall según el BFF real.
                 */
                $response = $this->client->getStandings(
                    $seasonId,
                    $config->car_class_id,
                    $division ?? -1
                );

                $metadata = $response['metadata'] ?? [];
                $drivers = $response['drivers'] ?? [];

                $this->validateResponse(
                    $metadata,
                    $drivers
                );

                $capturedAt = now();

                $result = $this->importClassification(
                    $standing,
                    $sync,
                    $drivers,
                    $capturedAt
                );

                $imported += $result['imported'];
                $processed++;

                $standing->update([
                    'status' => 'active',
                    'drivers_count' => count($drivers),
                    'last_synced_at' => $capturedAt,
                    'source_last_updated' =>
                        $metadata['last_updated'] ?? null,
                ]);

                if (! empty($metadata['last_updated'])) {
                    $sourceLastUpdated = $metadata['last_updated'];
                }
            }

            $sync->update([
                'status' => 'success',
                'classifications_processed' => $processed,
                'drivers_imported' => $imported,
                'source_last_updated' => $sourceLastUpdated,
                'finished_at' => now(),
            ]);

            /*
             * Solo se congela después de comprobar
             * la finalización oficial y completar
             * una importación final válida.
             */
            if (
                $syncType === 'final'
                && $this->client->isSeasonFinalized($seasonId)
            ) {
                $this->finalizeClass(
                    $series,
                    $config
                );
            }
        } catch (Throwable $e) {
            $sync->update([
                'status' => 'failed',
                'error_message' => mb_substr(
                    $e->getMessage(),
                    0,
                    60000
                ),
                'finished_at' => now(),
            ]);

            Log::error(
                'Series standings sync failed.',
                [
                    'series_id' => $series->id,
                    'car_class_id' => $config->car_class_id,
                    'sync_id' => $sync->id,
                    'exception' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    protected function getOrCreateStanding(
        Series $series,
        SeriesStandingConfig $config,
        array $classification
    ): SeriesStanding {
        return SeriesStanding::firstOrCreate(
            [
                'series_id' => $series->id,
                'car_class_id' => $config->car_class_id,
                'scope' => $classification['scope'],
                'division_key' => $classification['division_key'],
                'race_week_num' => -1,
            ],
            [
                'iracing_series_id' =>
                    $series->iracingSeries->iracing_series_id,

                'iracing_season_id' =>
                    $series->iracing_season_id,

                'division' => $classification['division'],

                'status' => 'pending',
            ]
        );
    }

    protected function validateResponse(
        array $metadata,
        array $drivers
    ): void {
        if (! array_key_exists('rows', $metadata)) {
            throw new RuntimeException(
                'Respuesta sin metadatos rows.'
            );
        }

        if (! is_array($drivers)) {
            throw new RuntimeException(
                'La clasificación no contiene una lista de pilotos.'
            );
        }

        if ((int) $metadata['rows'] !== count($drivers)) {
            throw new RuntimeException(
                'La cantidad de pilotos no coincide con los metadatos.'
            );
        }

        foreach ($drivers as $driver) {
            if (
                ! isset($driver['cust_id'])
                || ! isset($driver['rank'])
            ) {
                throw new RuntimeException(
                    'Piloto sin cust_id o rank.'
                );
            }
        }
    }

    /**
     * Actualiza la clasificación vigente y crea
     * un snapshot inmutable de la importación.
     */
    protected function importClassification(
        SeriesStanding $standing,
        SeriesStandingSync $sync,
        array $drivers,
        $capturedAt
    ): array {
        $imported = 0;

        DB::transaction(function () use (
            $standing,
            $sync,
            $drivers,
            $capturedAt,
            &$imported
        ) {
            foreach ($drivers as $driver) {
                $attributes = $this->mapDriver($driver);

                $standingDriver =
                    SeriesStandingDriver::updateOrCreate(
                        [
                            'series_standing_id' => $standing->id,
                            'cust_id' => $attributes['cust_id'],
                        ],
                        $attributes
                    );

                $snapshot = [
                    'series_standing_sync_id' => $sync->id,
                    'series_standing_id' => $standing->id,
                    'cust_id' => $attributes['cust_id'],
                    'rank' => $attributes['rank'],
                    'points' => $attributes['points'],
                    'raw_points' => $attributes['raw_points'],
                    'irating' => $attributes['irating'],
                    'weeks_counted' => $attributes['weeks_counted'],
                    'starts' => $attributes['starts'],
                    'wins' => $attributes['wins'],
                    'captured_at' => $capturedAt,
                ];

                SeriesStandingSnapshot::create($snapshot);

                $imported++;
            }

            /*
             * No eliminamos pilotos ausentes aquí.
             *
             * El cliente debe certificar que la respuesta
             * contiene todas las filas antes de llamar
             * a este método. La eliminación de ausentes,
             * si procede, debe implementarse tras esa
             * certificación y dentro de esta transacción.
             */
        });

        return [
            'imported' => $imported,
        ];
    }

    protected function mapDriver(array $driver): array
    {
        $license = $driver['license'] ?? [];

        return [
            'cust_id' => (int) $driver['cust_id'],
            'rank' => (int) $driver['rank'],

            'division' => isset($driver['division'])
                ? (int) $driver['division']
                : null,

            'display_name' => $driver['display_name'] ?? 'Unknown',
            'country_code' => $driver['country_code'] ?? null,

            'points' => $driver['points'] ?? 0,
            'raw_points' => $driver['raw_points'] ?? null,

            'week_dropped' => $driver['week_dropped'] ?? null,
            'weeks_counted' => $driver['weeks_counted'] ?? 0,

            'starts' => $driver['starts'] ?? 0,
            'wins' => $driver['wins'] ?? 0,
            'top5' => $driver['top5'] ?? 0,
            'top25_percent' => $driver['top25_percent'] ?? 0,
            'poles' => $driver['poles'] ?? 0,

            'avg_start_position' =>
                $driver['avg_start_position'] ?? null,

            'avg_finish_position' =>
                $driver['avg_finish_position'] ?? null,

            'avg_field_size' =>
                $driver['avg_field_size'] ?? null,

            'laps' => $driver['laps'] ?? 0,
            'laps_led' => $driver['laps_led'] ?? 0,
            'incidents' => $driver['incidents'] ?? 0,

            'irating' => $license['irating'] ?? null,

            'license_category_id' =>
                $license['category_id'] ?? null,

            'license_level' =>
                $license['license_level'] ?? null,

            'safety_rating' =>
                $license['safety_rating'] ?? null,

            'license_color' =>
                $license['color'] ?? null,

            'helmet' => $driver['helmet'] ?? null,
        ];
    }

    protected function finalizeClass(
        Series $series,
        SeriesStandingConfig $config
    ): void {
        SeriesStanding::query()
            ->where('series_id', $series->id)
            ->where('car_class_id', $config->car_class_id)
            ->where('status', '!=', 'finalized')
            ->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);
    }
}
