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
     * Importa la clasificación de temporada.
     *
     * El contenido puede proceder actualmente de:
     *
     * - CSV oficial de iRacing.
     * - JSON oficial de iRacing.
     *
     * La fuente/descarga se mantiene fuera de este servicio.
     * Este servicio solamente normaliza e importa los datos.
     */
    public function importCsv(
        Series $series,
        int $iracingSeasonId,
        int $iracingSeriesId,
        string $csvContent,
        string $syncType = 'manual',
        ?string $sourceLastUpdated = null
    ): SeriesStandingSync {
        $sync = SeriesStandingSync::create([
            'series_id' => $series->id,
            'sync_type' => $syncType,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $rows = $this->parseStandingsContent($csvContent);

            if (empty($rows)) {
                throw new RuntimeException(
                    'La fuente de standings no contiene pilotos para importar.'
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
                $rows,
                $divisions,
                $sync,
                $sourceUpdatedAt,
                &$classificationsProcessed,
                &$driversImported
            ) {
                /*
                 * Clasificación general:
                 * Incluye todos los pilotos de la fuente.
                 *
                 * La posición oficial se conserva en rank.
                 */
                $overall = $this->getOrCreateStanding(
                    $series->id,
                    $iracingSeriesId,
                    $iracingSeasonId,
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
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Detecta automáticamente el formato recibido.
     *
     * Actualmente soportamos:
     *
     * - CSV
     * - JSON
     */
    private function parseStandingsContent(string $content): array
    {
        $trimmed = ltrim($content);

        if ($trimmed === '') {
            throw new RuntimeException(
                'La fuente de standings está vacía.'
            );
        }

        if (
            str_starts_with($trimmed, '[') ||
            str_starts_with($trimmed, '{')
        ) {
            return $this->parseJson($content);
        }

        return $this->parseCsv($content);
    }

    /**
     * Parsea el JSON oficial de standings de iRacing.
     *
     * La estructura esperada normalmente es un array
     * de pilotos.
     *
     * También admite arrays anidados para tolerar respuestas
     * envueltas por una capa adicional.
     *
     * Los datos anidados de license se normalizan al formato
     * interno utilizado por SeriesStandingDriver.
     */
    private function parseJson(string $jsonContent): array
    {
        try {
            $data = json_decode(
                $jsonContent,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            throw new RuntimeException(
                'El JSON de standings no es válido: ' .
                $e->getMessage(),
                previous: $e
            );
        }

        if (!is_array($data)) {
            throw new RuntimeException(
                'El JSON de standings debe contener un array de pilotos.'
            );
        }

        /*
         * Algunas fuentes pueden devolver la colección envuelta
         * en uno o varios arrays adicionales.
         *
         * Ejemplo:
         *
         * [
         *     [
         *         { piloto },
         *         { piloto }
         *     ]
         * ]
         *
         * Desenvolvemos arrays numéricos mientras el primer
         * elemento siga siendo otro array y no un piloto.
         */
        while (
            count($data) === 1 &&
            isset($data[0]) &&
            is_array($data[0]) &&
            !array_key_exists('cust_id', $data[0])
        ) {
            $data = $data[0];
        }

        /*
         * Permitimos también una respuesta envuelta en una
         * propiedad "drivers" por compatibilidad futura con
         * posibles respuestas de API.
         */
        if (
            isset($data['drivers']) &&
            is_array($data['drivers'])
        ) {
            $data = $data['drivers'];
        }

        $rows = [];

        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }

            /*
             * Si todavía encontramos una capa de array, la
             * ignoramos aquí. El formato final esperado es
             * un array de objetos de piloto.
             */
            if (!array_key_exists('cust_id', $row)) {
                continue;
            }

            $custId = $row['cust_id'] ?? null;

            if (
                $custId === null ||
                !is_numeric($custId)
            ) {
                continue;
            }

            $license = is_array($row['license'] ?? null)
                ? $row['license']
                : [];

            $helmet = is_array($row['helmet'] ?? null)
                ? $row['helmet']
                : null;

            $division = is_numeric($row['division'] ?? null)
                ? (int) $row['division']
                : null;

            $rows[] = [
                'cust_id' => (int) $custId,

                'rank' => max(
                    1,
                    (int) ($row['rank'] ?? 0)
                ),

                'division' => $division,

                'display_name' => trim(
                    (string) ($row['display_name'] ?? '')
                ),

                'country_code' => $this->nullableString(
                    $row['country_code'] ?? null
                ),

                'points' => $this->nullableFloat(
                    $row['points'] ?? null
                ) ?? 0,

                'raw_points' => $this->nullableFloat(
                    $row['raw_points'] ?? null
                ),

                'week_dropped' => $this->nullableInteger(
                    $row['week_dropped'] ?? null
                ),

                'weeks_counted' => max(
                    0,
                    (int) ($row['weeks_counted'] ?? 0)
                ),

                'starts' => max(
                    0,
                    (int) ($row['starts'] ?? 0)
                ),

                'wins' => max(
                    0,
                    (int) ($row['wins'] ?? 0)
                ),

                'top5' => max(
                    0,
                    (int) ($row['top5'] ?? 0)
                ),

                'top25_percent' => $this->nullableFloat(
                    $row['top25_percent'] ?? null
                ),

                'poles' => max(
                    0,
                    (int) ($row['poles'] ?? 0)
                ),

                'avg_start_position' => $this->nullableFloat(
                    $row['avg_start_position'] ?? null
                ),

                'avg_finish_position' => $this->nullableFloat(
                    $row['avg_finish_position'] ?? null
                ),

                'avg_field_size' => $this->nullableFloat(
                    $row['avg_field_size'] ?? null
                ),

                'laps' => max(
                    0,
                    (int) ($row['laps'] ?? 0)
                ),

                'laps_led' => max(
                    0,
                    (int) ($row['laps_led'] ?? 0)
                ),

                'incidents' => max(
                    0,
                    (int) ($row['incidents'] ?? 0)
                ),

                'irating' => is_numeric(
                    $license['irating'] ?? null
                )
                    ? max(
                        0,
                        (int) $license['irating']
                    )
                    : null,

                'license_category_id' => is_numeric(
                    $license['category_id'] ?? null
                )
                    ? (int) $license['category_id']
                    : null,

                'license_level' => $this->nullableInteger(
                    $license['license_level'] ?? null
                ),

                'safety_rating' => $this->nullableFloat(
                    $license['safety_rating'] ?? null
                ),

                'license_color' => $this->nullableString(
                    $license['color'] ?? null
                ),

                'helmet' => $helmet,
            ];
        }

        /*
         * Evita procesar dos veces un mismo piloto dentro
         * de una misma división.
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
     * Parsea el CSV oficial de standings.
     *
     * Se mantiene para compatibilidad con el importador existente.
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

        $headers = fgetcsv(
            $stream,
            null,
            ',',
            '"',
            '\\'
        );

        if (!$headers) {
            fclose($stream);

            throw new RuntimeException(
                'El CSV no contiene cabecera.'
            );
        }

        $headers = array_map(
            fn ($header) => strtolower(
                trim(
                    preg_replace(
                        '/^\xEF\xBB\xBF/',
                        '',
                        $header
                    )
                )
            ),
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

        $missing = array_diff(
            $required,
            $headers
        );

        if (!empty($missing)) {
            fclose($stream);

            throw new RuntimeException(
                'Faltan columnas obligatorias en el CSV: ' .
                implode(', ', $missing)
            );
        }

        $rows = [];

        while (
            ($values = fgetcsv(
                $stream,
                null,
                ',',
                '"',
                '\\'
            )) !== false
        ) {
            if (count($values) !== count($headers)) {
                continue;
            }

            $row = array_combine(
                $headers,
                $values
            );

            if (
                empty($row['custid']) ||
                !is_numeric($row['custid'])
            ) {
                continue;
            }

            $division = is_numeric(
                $row['division']
            )
                ? (int) $row['division']
                : null;

            $rows[] = [
                'cust_id' => (int) $row['custid'],

                'rank' => max(
                    1,
                    (int) $row['position']
                ),

                'division' => $division,

                'display_name' => trim(
                    $row['name']
                ),

                'country_code' => $this->nullableString(
                    $row['countrycode']
                ),

                'points' => $this->nullableFloat(
                    $row['points']
                ) ?? 0,

                'raw_points' => null,

                'week_dropped' => null,

                'weeks_counted' => max(
                    0,
                    (int) $row['weekscounted']
                ),

                'starts' => max(
                    0,
                    (int) $row['starts']
                ),

                'wins' => max(
                    0,
                    (int) $row['wins']
                ),

                'top5' => max(
                    0,
                    (int) $row['topfive']
                ),

                'top25_percent' => null,

                'poles' => max(
                    0,
                    (int) $row['poles']
                ),

                'avg_start_position' =>
                    $this->nullableFloat(
                        $row['avgstart']
                    ),

                'avg_finish_position' =>
                    $this->nullableFloat(
                        $row['avgfinish']
                    ),

                'avg_field_size' => null,

                'laps' => max(
                    0,
                    (int) $row['laps']
                ),

                'laps_led' => max(
                    0,
                    (int) $row['lapslead']
                ),

                'incidents' => max(
                    0,
                    (int) $row['incidents']
                ),

                'irating' => is_numeric(
                    $row['irating']
                )
                    ? max(
                        0,
                        (int) $row['irating']
                    )
                    : null,

                'license_category_id' => null,

                'license_level' => null,

                'safety_rating' => null,

                'license_color' => null,

                'helmet' => null,
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
        string $scope,
        int $divisionKey,
        ?int $division,
        $sourceUpdatedAt
    ): SeriesStanding {
        $standing = SeriesStanding::firstOrNew([
            'series_id' => $seriesId,
            'scope' => $scope,
            'division_key' => $divisionKey,
            'race_week_num' => -1,
        ]);

        $standing->iracing_series_id =
            $iracingSeriesId;

        $standing->iracing_season_id =
            $iracingSeasonId;

        $standing->division =
            $division;

        $standing->source_last_updated =
            $sourceUpdatedAt;

        $standing->last_synced_at =
            now();

        // Solo las clasificaciones nuevas comienzan pendientes.
        if (!$standing->exists) {
            $standing->status = 'pending';
        }

        $standing->save();

        return $standing;
    }

    /**
     * Actualiza pilotos y guarda un snapshot
     * de esta sincronización.
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
                'raw_points' => $row['raw_points'],
                'week_dropped' => $row['week_dropped'],
                'weeks_counted' => $row['weeks_counted'],
                'starts' => $row['starts'],
                'wins' => $row['wins'],
                'top5' => $row['top5'],
                'top25_percent' => $row['top25_percent'],
                'poles' => $row['poles'],
                'avg_start_position' =>
                    $row['avg_start_position'],
                'avg_finish_position' =>
                    $row['avg_finish_position'],
                'avg_field_size' =>
                    $row['avg_field_size'],
                'laps' => $row['laps'],
                'laps_led' => $row['laps_led'],
                'incidents' => $row['incidents'],
                'irating' => $row['irating'],
                'license_category_id' =>
                    $row['license_category_id'],
                'license_level' =>
                    $row['license_level'],
                'safety_rating' =>
                    $row['safety_rating'],
                'license_color' =>
                    $row['license_color'],
                'helmet' => $row['helmet'],
            ];

            SeriesStandingDriver::updateOrCreate(
                [
                    'series_standing_id' =>
                        $standing->id,
                    'cust_id' => $custId,
                ],
                $driverData
            );

            SeriesStandingSnapshot::create([
                'series_standing_sync_id' =>
                    $sync->id,

                'series_standing_id' =>
                    $standing->id,

                'cust_id' => $custId,

                'rank' => $row['rank'],

                'points' => $row['points'],

                'raw_points' =>
                    $row['raw_points'],

                'irating' =>
                    $row['irating'],

                'weeks_counted' =>
                    $row['weeks_counted'],

                'starts' =>
                    $row['starts'],

                'wins' =>
                    $row['wins'],

                'captured_at' =>
                    $now,
            ]);

            $imported++;
        }

        /*
         * Elimina pilotos que ya no aparecen en la
         * clasificación actual.
         *
         * Los snapshots históricos no se eliminan.
         */
        if (!empty($custIds)) {
            SeriesStandingDriver::where(
                'series_standing_id',
                $standing->id
            )
                ->whereNotIn(
                    'cust_id',
                    $custIds
                )
                ->delete();
        }

        $standing->update([
            'drivers_count' =>
                count($custIds),

            'last_synced_at' =>
                $now,

            'status' =>
                $standing->status === 'finalized'
                    ? 'finalized'
                    : 'active',
        ]);

        return $imported;
    }

    private function nullableString(
        ?string $value
    ): ?string {
        $value = trim(
            (string) $value
        );

        return $value === ''
            ? null
            : $value;
    }

    private function nullableFloat(
        $value
    ): ?float {
        if (
            $value === null ||
            trim((string) $value) === ''
        ) {
            return null;
        }

        return is_numeric($value)
            ? (float) $value
            : null;
    }

    private function nullableInteger(
        $value
    ): ?int {
        if (
            $value === null ||
            trim((string) $value) === ''
        ) {
            return null;
        }

        return is_numeric($value)
            ? (int) $value
            : null;
    }
}
