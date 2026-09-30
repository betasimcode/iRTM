<?php

namespace App\Services;

use App\Models\Series;
use App\Models\SeriesStanding;
use App\Models\SeriesStandingDriver;
use App\Models\SeriesStandingSnapshot;
use App\Models\SeriesStandingSync;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

class SeriesStandingsSyncService
{
    /**
     * Importa la clasificación de temporada desde un CSV oficial.
     *
     * El CSV debe proceder de:
     * season-driver/.../season/{season_id}/{car_class_id}/all/
     *
     * El contenido se recibe ya descargado. La autenticación y
     * descarga del CSV se implementarán en una capa independiente.
     */
    public function importCsv(
        Series $series,
        int $iracingSeasonId,
        int $iracingSeriesId,
        int $carClassId,
        string $csvContent,
        string $syncType = 'manual',
        ?string $sourceLastUpdated = null
    ): SeriesStandingSync {
        $sync = SeriesStandingSync::create([
            'series_id' => $series->id,
            'car_class_id' => $carClassId,
            'sync_type' => $syncType,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $rows = $this->parseCsv($csvContent);

            if (empty($rows)) {
                throw new RuntimeException(
                    'El CSV no contiene pilotos para importar.'
                );
            }

            $sourceUpdatedAt = $sourceLastUpdated
                ? Carbon::parse($sourceLastUpdated)
                : null;

            $divisions = collect($rows)
                ->pluck('division')
                ->filter(fn ($division) => $division !== null)
                ->unique()
                ->sort()
                ->values();

            $classificationsProcessed = 0;
            $driversImported = 0;

            DB::transaction(function () use (
                $series,
                $iracingSeasonId,
                $iracingSeriesId,
                $carClassId,
                $rows,
                $divisions,
                $sync,
                $sourceUpdatedAt,
                &$classificationsProcessed,
                &$driversImported
            ) {
                /*
                 * Clasificación general:
                 * Incluye todos los pilotos del CSV.
                 *
                 * La posición oficial del CSV se conserva en rank.
                 * En all_div, position se reinicia por división.
                 */
                $overall = $this->getOrCreateStanding(
                    $series->id,
                    $iracingSeriesId,
                    $iracingSeasonId,
                    $carClassId,
                    'overall',
                    -1,
                    null,
                    $sourceUpdatedAt
                );

                $driversImported += $this->importDrivers(
                    $overall,
                    $rows,
                    $sync
                );

                $classificationsProcessed++;

                /*
                 * Clasificaciones individuales por división.
                 */
                foreach ($divisions as $division) {
                    $divisionRows = collect($rows)
                        ->where('division', (int) $division)
                        ->sortBy('rank')
                        ->values()
                        ->map(function ($row, $index) {
                            // Posición relativa dentro de esta división.
                            $row['rank'] = $index + 1;

                            return $row;
                        })
                        ->all();

                    if (empty($divisionRows)) {
                        continue;
                    }

                    $standing = $this->getOrCreateStanding(
                        $series->id,
                        $iracingSeriesId,
                        $iracingSeasonId,
                        $carClassId,
                        'division',
                        (int) $division,
                        (int) $division,
                        $sourceUpdatedAt
                    );

                    $driversImported += $this->importDrivers(
                        $standing,
                        $divisionRows,
                        $sync
                    );

                    $classificationsProcessed++;
                }

                $sync->update([
                    'classifications_processed' =>
                        $classificationsProcessed,
                    'drivers_imported' => $driversImported,
                    'source_last_updated' => $sourceUpdatedAt,
                    'status' => 'success',
                    'finished_at' => now(),
                ]);
            });

            return $sync->fresh();
        } catch (Throwable $e) {
            $sync->update([
                'status' => 'failed',
                'error_message' => mb_substr(
                    $e->getMessage(),
                    0,
                    65000
                ),
                'finished_at' => now(),
            ]);

            Log::error('Error importando standings de iRacing', [
                'sync_id' => $sync->id,
                'series_id' => $series->id,
                'car_class_id' => $carClassId,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Parsea el CSV y valida sus columnas obligatorias.
     */
    private function parseCsv(string $csvContent): array
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new RuntimeException(
                'No se pudo abrir el flujo temporal del CSV.'
            );
        }

        fwrite($stream, $csvContent);
        rewind($stream);

        $headers = fgetcsv($stream, null, ',', '"', '\\');

        if (!$headers) {
            fclose($stream);

            throw new RuntimeException(
                'El CSV no contiene cabecera.'
            );
        }

        $headers = array_map(
            fn ($header) => strtolower(trim(
                preg_replace('/^\xEF\xBB\xBF/', '', $header)
            )),
            $headers
        );

        $required = [
            'position',
            'name',
            'points',
            'countrycode',
            'irating',
            'avgfinish',
            'topfive',
            'starts',
            'lapslead',
            'wins',
            'incidents',
            'division',
            'weekscounted',
            'laps',
            'poles',
            'avgstart',
            'custid',
        ];

        $missing = array_diff($required, $headers);

        if (!empty($missing)) {
            fclose($stream);

            throw new RuntimeException(
                'Faltan columnas obligatorias en el CSV: ' .
                implode(', ', $missing)
            );
        }

        $rows = [];

        while (($values = fgetcsv(
            $stream,
            null,
            ',',
            '"',
            '\\'
        )) !== false) {
            if (count($values) !== count($headers)) {
                continue;
            }

            $row = array_combine($headers, $values);

            if (
                empty($row['custid']) ||
                !is_numeric($row['custid'])
            ) {
                continue;
            }

            $division = is_numeric($row['division'])
                ? (int) $row['division']
                : null;

            $rows[] = [
                'cust_id' => (int) $row['custid'],
                'rank' => max(1, (int) $row['position']),
                'division' => $division,
                'display_name' => trim($row['name']),
                'country_code' => $this->nullableString(
                    $row['countrycode']
                ),
                'points' => $this->nullableFloat(
                    $row['points']
                ) ?? 0,
                'weeks_counted' => max(
                    0,
                    (int) $row['weekscounted']
                ),
                'starts' => max(0, (int) $row['starts']),
                'wins' => max(0, (int) $row['wins']),
                'top5' => max(0, (int) $row['topfive']),
                'poles' => max(0, (int) $row['poles']),
                'avg_start_position' => $this->nullableFloat(
                    $row['avgstart']
                ),
                'avg_finish_position' => $this->nullableFloat(
                    $row['avgfinish']
                ),
                'laps' => max(0, (int) $row['laps']),
                'laps_led' => max(0, (int) $row['lapslead']),
                'incidents' => max(0, (int) $row['incidents']),
                'irating' => is_numeric($row['irating'])
                    ? max(0, (int) $row['irating'])
                    : null,
            ];
        }

        fclose($stream);

        /*
         * Evita procesar dos veces un mismo piloto dentro
         * de una misma división por un CSV defectuoso.
         */
        $unique = [];

        foreach ($rows as $row) {
            $key = ($row['division'] ?? 'null')
                . ':' . $row['cust_id'];

            $unique[$key] = $row;
        }

        return array_values($unique);
    }

    /**
     * Busca o crea la clasificación.
     */
    private function getOrCreateStanding(
        int $seriesId,
        int $iracingSeriesId,
        int $iracingSeasonId,
        int $carClassId,
        string $scope,
        int $divisionKey,
        ?int $division,
        $sourceUpdatedAt
    ): SeriesStanding {
        $standing = SeriesStanding::firstOrNew([
            'series_id' => $seriesId,
            'car_class_id' => $carClassId,
            'scope' => $scope,
            'division_key' => $divisionKey,
            'race_week_num' => -1,
        ]);

        $standing->iracing_series_id = $iracingSeriesId;
        $standing->iracing_season_id = $iracingSeasonId;
        $standing->division = $division;
        $standing->source_last_updated = $sourceUpdatedAt;
        $standing->last_synced_at = now();

        // Solo las clasificaciones nuevas comienzan pendientes.
        if (! $standing->exists) {
            $standing->status = 'pending';
        }

        $standing->save();

        return $standing;
    }

    /**
     * Actualiza pilotos y guarda un snapshot de esta sincronización.
     */
    private function importDrivers(
        SeriesStanding $standing,
        array $rows,
        SeriesStandingSync $sync
    ): int {
        $now = now();
        $imported = 0;
        $custIds = [];

        foreach ($rows as $row) {
            $custId = $row['cust_id'];
            $custIds[] = $custId;

            $driverData = [
                'rank' => $row['rank'],
                'division' => $row['division'],
                'display_name' => $row['display_name'],
                'country_code' => $row['country_code'],
                'points' => $row['points'],
                'weeks_counted' => $row['weeks_counted'],
                'starts' => $row['starts'],
                'wins' => $row['wins'],
                'top5' => $row['top5'],
                'poles' => $row['poles'],
                'avg_start_position' =>
                    $row['avg_start_position'],
                'avg_finish_position' =>
                    $row['avg_finish_position'],
                'laps' => $row['laps'],
                'laps_led' => $row['laps_led'],
                'incidents' => $row['incidents'],
                'irating' => $row['irating'],
            ];

            SeriesStandingDriver::updateOrCreate(
                [
                    'series_standing_id' => $standing->id,
                    'cust_id' => $custId,
                ],
                $driverData
            );

            SeriesStandingSnapshot::create([
                'series_standing_sync_id' => $sync->id,
                'series_standing_id' => $standing->id,
                'cust_id' => $custId,
                'rank' => $row['rank'],
                'points' => $row['points'],
                'raw_points' => null,
                'irating' => $row['irating'],
                'weeks_counted' => $row['weeks_counted'],
                'starts' => $row['starts'],
                'wins' => $row['wins'],
                'captured_at' => $now,
            ]);

            $imported++;
        }

        /*
         * Elimina pilotos que ya no aparecen en la clasificación
         * actual. Los snapshots históricos no se eliminan.
         */
        if (!empty($custIds)) {
            SeriesStandingDriver::where(
                'series_standing_id',
                $standing->id
            )
                ->whereNotIn('cust_id', $custIds)
                ->delete();
        }

        $standing->update([
            'drivers_count' => count($custIds),
            'last_synced_at' => $now,
            'status' => $standing->status === 'finalized'
                ? 'finalized'
                : 'active',
        ]);

        return $imported;
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableFloat($value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return is_numeric($value)
            ? (float) $value
            : null;
    }
}
