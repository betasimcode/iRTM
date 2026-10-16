<?php
/** UPDATE 1741**/
namespace App\Services\Ibt;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;

class IbtProcessor
{
    /**
     * Tamaño de la cabecera principal del IBT.
     */
    private const HEADER_SIZE = 112;

    /**
     * Offset del diskSubHeader dentro del IBT.
     */
    private const DISK_SUB_HEADER_OFFSET = 112;

    /**
     * Tamaño del diskSubHeader.
     */
    private const DISK_SUB_HEADER_SIZE = 32;

    private const VAR_HEADER_SIZE = 144;

    private const VAR_TYPE_CHAR = 0;
    private const VAR_TYPE_BOOL = 1;
    private const VAR_TYPE_INT = 2;
    private const VAR_TYPE_BITFIELD = 3;
    private const VAR_TYPE_FLOAT = 4;
    private const VAR_TYPE_DOUBLE = 5;

    /**
     * Lee únicamente la cabecera binaria del IBT.
     *
     * No carga el archivo completo en memoria.
     */
    public function readHeader(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException(
                "IBT no encontrado: {$filePath}"
            );
        }

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException(
                "No se pudo abrir el IBT: {$filePath}"
            );
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | HEADER PRINCIPAL
            |--------------------------------------------------------------------------
            */

            $header = $this->readBytes(
                $handle,
                0,
                self::HEADER_SIZE
            );

            $version = $this->unpackInt32(
                $header,
                0
            );

            $status = $this->unpackInt32(
                $header,
                4
            );

            $tickRate = $this->unpackInt32(
                $header,
                8
            );

            $sessionInfoUpdate = $this->unpackInt32(
                $header,
                12
            );

            $sessionInfoLength = $this->unpackInt32(
                $header,
                16
            );

            $sessionInfoOffset = $this->unpackInt32(
                $header,
                20
            );

            $numVariables = $this->unpackInt32(
                $header,
                24
            );

            $varHeaderOffset = $this->unpackInt32(
                $header,
                28
            );

            $numBuffers = $this->unpackInt32(
                $header,
                32
            );

            $bufferLength = $this->unpackInt32(
                $header,
                36
            );

            /*
            |--------------------------------------------------------------------------
            | DISK SUB HEADER
            |--------------------------------------------------------------------------
            |
            | En un IBT aparece inmediatamente después del header
            | principal de 112 bytes.
            |
            */

            $diskSubHeader = $this->readBytes(
                $handle,
                self::DISK_SUB_HEADER_OFFSET,
                self::DISK_SUB_HEADER_SIZE
            );

            /*
            |--------------------------------------------------------------------------
            | sessionStartDate
            |--------------------------------------------------------------------------
            |
            | En Windows/IRSDK el time_t ocupa 8 bytes.
            | De momento lo conservamos RAW.
            |
            */

            $sessionStartDate = $this->unpackInt64(
                $diskSubHeader,
                0
            );

            /*
            |--------------------------------------------------------------------------
            | TIEMPOS DE SESIÓN
            |--------------------------------------------------------------------------
            */

            $sessionStartTime = $this->unpackDouble(
                $diskSubHeader,
                8
            );

            $sessionEndTime = $this->unpackDouble(
                $diskSubHeader,
                16
            );

            /*
            |--------------------------------------------------------------------------
            | LAPS / RECORDS
            |--------------------------------------------------------------------------
            */

            $sessionLapCount = $this->unpackInt32(
                $diskSubHeader,
                24
            );

            $sessionRecordCount = $this->unpackInt32(
                $diskSubHeader,
                28
            );

            /*
            |--------------------------------------------------------------------------
            | RESULTADO
            |--------------------------------------------------------------------------
            */

            return [
                'version' => $version,
                'status' => $status,

                'tick_rate' => $tickRate,

                'session_info_update' =>
                    $sessionInfoUpdate,

                'session_info_length' =>
                    $sessionInfoLength,

                'session_info_offset' =>
                    $sessionInfoOffset,

                'num_variables' =>
                    $numVariables,

                'var_header_offset' =>
                    $varHeaderOffset,

                'num_buffers' =>
                    $numBuffers,

                'buffer_length' =>
                    $bufferLength,

                'session_start_date' =>
                    $sessionStartDate,

                'session_start_time' =>
                    $sessionStartTime,

                'session_end_time' =>
                    $sessionEndTime,

                'session_lap_count' =>
                    $sessionLapCount,

                'session_record_count' =>
                    $sessionRecordCount,
            ];
        } finally {
            fclose($handle);
        }
    }



    public function readVariables(string $filePath): array
    {
        $header = $this->readHeader($filePath);

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException(
                "No se pudo abrir el IBT: {$filePath}"
            );
        }

        try {
            $variables = [];

            for (
                $index = 0;
                $index < $header['num_variables'];
                $index++
            ) {
                $offset =
                    $header['var_header_offset'] +
                    ($index * self::VAR_HEADER_SIZE);

                $data = $this->readBytes(
                    $handle,
                    $offset,
                    self::VAR_HEADER_SIZE
                );

                $type = $this->unpackInt32(
                    $data,
                    0
                );

                $offsetInRecord = $this->unpackInt32(
                    $data,
                    4
                );

                $count = $this->unpackInt32(
                    $data,
                    8
                );

                $countAsTime = $this->unpackInt32(
                    $data,
                    12
                );

                $name = $this->readCString(
                    $data,
                    16,
                    32
                );

                $description = $this->readCString(
                    $data,
                    48,
                    64
                );

                $unit = $this->readCString(
                    $data,
                    112,
                    32
                );

                $variables[] = [
                    'index' => $index,
                    'type' => $type,
                    'type_name' => $this->variableTypeName($type),
                    'offset' => $offsetInRecord,
                    'count' => $count,
                    'count_as_time' => $countAsTime,
                    'name' => $name,
                    'description' => $description,
                    'unit' => $unit,
                ];
            }

            return $variables;
        } finally {
            fclose($handle);
        }
    }


    public function readRecord(
        string $filePath,
        int $recordIndex
    ): array {
        $header = $this->readHeader($filePath);

        $variables = $this->readVariables($filePath);

        $recordOffset =
            $header['session_info_offset'] +
            $header['session_info_length'] +
            ($recordIndex * $header['buffer_length']);

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException(
                "No se pudo abrir el IBT: {$filePath}"
            );
        }

        try {
            $record = $this->readBytes(
                $handle,
                $recordOffset,
                $header['buffer_length']
            );

            $values = [];

            foreach ($variables as $variable) {
                $values[$variable['name']] =
                    $this->readVariableValue(
                        $record,
                        $variable
                    );
            }

            return $values;
        } finally {
            fclose($handle);
        }
    }

    public function streamRecords(
        string $filePath,
        ?array $variableNames = null
    ): \Generator {
        $header = $this->readHeader($filePath);
        $variables = $this->readVariables($filePath);

        /*
        * Si no se especifican variables, usamos todas.
        * Durante el procesamiento real podremos pasar únicamente
        * las variables necesarias.
        */
        if ($variableNames !== null) {
            $wanted = array_flip($variableNames);

            $variables = array_values(
                array_filter(
                    $variables,
                    fn (array $variable) =>
                        isset($wanted[$variable['name']])
                )
            );
        }

        $recordStart =
            $header['session_info_offset'] +
            $header['session_info_length'];

        $recordCount = $header['session_record_count'];
        $bufferLength = $header['buffer_length'];

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new \RuntimeException(
                "No se pudo abrir el IBT: {$filePath}"
            );
        }

        try {
            fseek($handle, $recordStart);

            for ($recordIndex = 0; $recordIndex < $recordCount; $recordIndex++) {

                $buffer = fread($handle, $bufferLength);

                if ($buffer === false || strlen($buffer) !== $bufferLength) {
                    throw new \RuntimeException(
                        "No se pudo leer el registro {$recordIndex} del IBT."
                    );
                }

                $record = [];

                foreach ($variables as $variable) {
                    $record[$variable['name']] =
                        $this->readVariableValue(
                            $buffer,
                            $variable
                        );
                }

                yield $recordIndex => $record;
            }
        } finally {
            fclose($handle);
        }
    }


    public function streamRecordsRange(
        string $filePath,
        int $startRecord,
        int $endRecord,
        ?array $variableNames = null
    ): \Generator {
        $header = $this->readHeader($filePath);
        $variables = $this->readVariables($filePath);

        if ($startRecord < 0) {
            $startRecord = 0;
        }

        if ($endRecord < $startRecord) {
            throw new \InvalidArgumentException(
                'El registro final no puede ser menor que el inicial.'
            );
        }

        $recordCount = $header['session_record_count'];

        if ($startRecord >= $recordCount) {
            return;
        }

        $endRecord = min(
            $endRecord,
            $recordCount - 1
        );

        if ($variableNames !== null) {
            $wanted = array_flip($variableNames);

            $variables = array_values(
                array_filter(
                    $variables,
                    fn (array $variable) =>
                        isset($wanted[$variable['name']])
                )
            );
        }

        $recordStart =
            $header['session_info_offset'] +
            $header['session_info_length'];

        $bufferLength = $header['buffer_length'];

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new \RuntimeException(
                "No se pudo abrir el IBT: {$filePath}"
            );
        }

        try {
            $offset =
                $recordStart +
                ($startRecord * $bufferLength);

            if (fseek($handle, $offset) !== 0) {
                throw new \RuntimeException(
                    "No se pudo posicionar en el registro {$startRecord}."
                );
            }

            for (
                $recordIndex = $startRecord;
                $recordIndex <= $endRecord;
                $recordIndex++
            ) {
                $buffer = fread(
                    $handle,
                    $bufferLength
                );

                if (
                    $buffer === false ||
                    strlen($buffer) !== $bufferLength
                ) {
                    throw new \RuntimeException(
                        "No se pudo leer el registro {$recordIndex} del IBT."
                    );
                }

                $record = [];

                foreach ($variables as $variable) {
                    $record[$variable['name']] =
                        $this->readVariableValue(
                            $buffer,
                            $variable
                        );
                }

                yield $recordIndex => $record;
            }
        } finally {
            fclose($handle);
        }
    }


    public function extractLaps(
        string $filePath
    ): array {
        $laps = [];

        $variables = [
            'SessionTime',
            'Lap',
            'LapCompleted',
            'LapLastLapTime',
            'LapBestLapTime',
            'LapBestLap',
            'OnPitRoad',
            'Lat',
            'Lon',
            'Alt',
            'Yaw',
        ];

        // Marcador identificativo: números de vuelta con muestras en pit road.
        $pitLapNumbers = [];

        $previousCompleted = null;
        $previousLastLapTime = null;

        /*
        * Vueltas consumidas que todavía no
        * tienen un tiempo asociado.
        *
        * Se conserva el orden nativo de iRacing.
        */
        $pendingLaps = [];

        /*
        * Detectamos estados transitorios como:
        *
        *   Lap 4 -> 0 -> 4
        *   Completed 3 -> 0 -> 3
        *
        * observados tanto con ESC como entrando
        * físicamente a boxes.
        */
        $resetActive = false;
        $resetBaseCompleted = null;

        foreach (
            $this->streamRecords(
                $filePath,
                $variables
            ) as $index => $record
        ) {
            $sessionTime =
                (float) ($record['SessionTime'] ?? 0);

            $lap =
                (int) ($record['Lap'] ?? 0);

            $completed =
                (int) ($record['LapCompleted'] ?? 0);

            $onPitRoad = (bool) ($record['OnPitRoad'] ?? false);

            if ($onPitRoad && $lap > 0) {
                $pitLapNumbers[$lap] = true;
            }

            if ($completed === 4294967295) {
                $completed = -1;
            }

            $lastLapTime =
                (float) ($record['LapLastLapTime'] ?? -1);

            $bestLapTime =
                (float) ($record['LapBestLapTime'] ?? -1);

            /*
            * ==================================================
            * DETECTAR CONSUMO DE NUEVA VUELTA
            * ==================================================
            */
            if (
                $previousCompleted !== null &&
                $completed > $previousCompleted
            ) {
                for (
                    $lapNumber = $previousCompleted + 1;
                    $lapNumber <= $completed;
                    $lapNumber++
                ) {
                    if ($lapNumber > 0) {
                        $pendingLaps[] = $lapNumber;
                    }
                }
            }

            /*
            * ==================================================
            * DETECTAR RESET TRANSITORIO
            * ==================================================
            */
            if (
                $previousCompleted !== null &&
                $completed < $previousCompleted
            ) {
                $resetActive = true;

                $resetBaseCompleted =
                    $previousCompleted;
            }

            /*
            * Restauración del contador después
            * del estado transitorio.
            */
            if (
                $resetActive &&
                $resetBaseCompleted !== null &&
                $completed === $resetBaseCompleted
            ) {
                /*
                * No hacemos nada todavía.
                *
                * El siguiente cambio de LapLastLapTime
                * será el que determine si iRacing
                * acaba de publicar el tiempo.
                */
            }

            /*
            * ==================================================
            * NUEVO TIEMPO PUBLICADO POR IRACING
            * ==================================================
            */
            $newLapTime =
                $lastLapTime > 0 &&
                (
                    $previousLastLapTime === null ||
                    $previousLastLapTime <= 0 ||
                    abs(
                        $lastLapTime -
                        $previousLastLapTime
                    ) > 0.000001
                );



            if ($newLapTime) {
                logger()->debug('IBT lap time association decision', [
                    'index' => $index,
                    'session_time' => $sessionTime,
                    'lap' => $lap,
                    'completed' => $completed,
                    'previous_completed' => $previousCompleted,
                    'last_lap_time' => $lastLapTime,
                    'previous_last_lap_time' => $previousLastLapTime,
                    'reset_active' => $resetActive,
                    'reset_base_completed' => $resetBaseCompleted,
                    'pending_laps' => $pendingLaps,
                ]);

                $associatedLap = null;

                /*
                * --------------------------------------------------
                * CASO NORMAL
                * --------------------------------------------------
                *
                * En una tanda limpia hemos observado:
                *
                *   Completed 2
                *   LastLapTime = Lap 1
                *
                *   Completed 3
                *   LastLapTime = Lap 2
                *
                * Por tanto, cuando no existe transición de reset,
                * el tiempo se asigna a la primera vuelta pendiente.
                */
                if (
                    !$resetActive &&
                    !empty($pendingLaps)
                ) {
                    $associatedLap =
                        array_shift($pendingLaps);
                }

                /*
                * --------------------------------------------------
                * CASO RESET / BOX / ESC
                * --------------------------------------------------
                *
                * Hemos observado:
                *
                *   Completed 3
                *   ...
                *   Lap 4 -> 0 -> 4
                *   LastLapTime = tiempo de Lap 3
                *
                * En este caso el tiempo corresponde a la
                * vuelta más recientemente consumida.
                */
                if (
                    $resetActive &&
                    !empty($pendingLaps)
                ) {
                    $associatedLap =
                        array_pop($pendingLaps);

                    $resetActive = false;
                    $resetBaseCompleted = null;
                }

                if ($associatedLap !== null) {
                    $laps[] = [
                        'lap' =>
                            $associatedLap,

                        'lap_time' =>
                            $lastLapTime,

                        'completed' =>
                            $associatedLap,

                        'completion_index' =>
                            $index,

                        'time_index' =>
                            $index,

                        'clean_sequence' =>
                            count($laps) + 1,

                        'record_index' =>
                            $index,

                        'session_time' =>
                            $sessionTime,

                        'source_lap' =>
                            $lap,

                        'candidate_lap' =>
                            $completed,

                        'source_completed' =>
                            $completed,

                        'best_lap' =>
                            (int) ($record['LapBestLap'] ?? 0),

                        'best_lap_time' =>
                            (float) ($record['LapBestLapTime'] ?? 0),
                    ];
                }
            }

            $previousCompleted =
                $completed;

            if ($lastLapTime > 0) {
                $previousLastLapTime =
                    $lastLapTime;
            }
        }

        // Clasificar al final para incluir muestras de pit road que aparezcan
        // después de que la vuelta cronometrada haya sido detectada.
        foreach ($laps as &$lapData) {
            $lapNumber = (int) ($lapData['lap'] ?? 0);
            $lapData['is_pit_lap'] = isset($pitLapNumbers[$lapNumber]);
        }
        unset($lapData);

        return $laps;
    }



    /**
     * Lee el bloque SessionInfo del IBT como texto YAML.
     *
     * No interpreta ni transforma los valores del documento.
     */
    public function readSessionInfoText(string $filePath): string
    {
        $header = $this->readHeader($filePath);

        $length = (int) $header['session_info_length'];
        $offset = (int) $header['session_info_offset'];

        if ($length <= 0) {
            throw new RuntimeException(
                'El IBT no contiene SessionInfo.'
            );
        }

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException(
                "No se pudo abrir el IBT: {$filePath}"
            );
        }

        try {
            return $this->readBytes(
                $handle,
                $offset,
                $length
            );
        } finally {
            fclose($handle);
        }
    }

    public function exportSessionInfoText(string $filePath): string
    {
        return $this->readSessionInfoText($filePath);
    }


    public function writeSessionInfoDebugJson(
        string $filePath,
        string $outputPath
    ): string {
        $sessionInfo = $this->readSessionInfoText($filePath);

        $directory = dirname($outputPath);

        if (
            !is_dir($directory) &&
            !mkdir($directory, 0775, true) &&
            !is_dir($directory)
        ) {
            throw new RuntimeException(
                "No se pudo crear el directorio: {$directory}"
            );
        }

        $written = file_put_contents(
            $outputPath,
            $sessionInfo,
            LOCK_EX
        );

        if ($written === false) {
            throw new RuntimeException(
                "No se pudo escribir SessionInfo: {$outputPath}"
            );
        }

        return $outputPath;
    }


    /**
     * Genera session.json con los metadatos de sesión extraídos del IBT.
     *
     * No modifica ni interviene en la extracción de vueltas o sectores.
     */
    public function writeSessionJson(
        string $filePath,
        string $outputPath
    ): array {
        $sessionInfoText = $this->readSessionInfoText($filePath);

        // SessionInfo puede contener caracteres ISO-8859-1.
        if (!mb_check_encoding($sessionInfoText, 'UTF-8')) {
            $sessionInfoText = mb_convert_encoding(
                $sessionInfoText,
                'UTF-8',
                'ISO-8859-1'
            );
        }

        try {
            $data = Yaml::parse($sessionInfoText);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'No se pudo interpretar el SessionInfo del IBT: '
                . $e->getMessage(),
                0,
                $e
            );
        }

        if (!is_array($data)) {
            throw new RuntimeException(
                'El SessionInfo del IBT no contiene una estructura YAML válida.'
            );
        }

        $weekend = $data['WeekendInfo'] ?? [];
        $sessionInfo = $data['SessionInfo'] ?? [];
        $driverInfo = $data['DriverInfo'] ?? [];
        $carSetup = $data['CarSetup'] ?? [];

        /*
        * Normaliza cadenas vacías.
        */
        $text = static function (mixed $value): ?string {
            if (!is_string($value) && !is_numeric($value)) {
                return null;
            }

            $value = trim((string) $value);

            return $value === '' ? null : $value;
        };

        /*
        * Extrae el componente numérico de valores como:
        * "4.8615 km", "26.16 C", "29.5 L" o "93 %".
        *
        * No realiza conversiones de unidades.
        */
        $number = static function (mixed $value): int|float|null {
            if (is_int($value) || is_float($value)) {
                return $value;
            }

            if (!is_string($value)) {
                return null;
            }

            if (preg_match(
                '/^\s*([+-]?(?:\d+(?:\.\d*)?|\.\d+))/u',
                trim($value),
                $matches
            )) {
                return str_contains($matches[1], '.')
                    ? (float) $matches[1]
                    : (int) $matches[1];
            }

            return null;
        };

        $integer = static function (mixed $value) use ($number): ?int {
            $value = $number($value);

            return $value === null ? null : (int) $value;
        };

        $boolean = static function (mixed $value): ?bool {
            if ($value === null || $value === '') {
                return null;
            }

            return (bool) ((int) $value);
        };

        /*
        * Selecciona la sesión activa del IBT.
        */
        $sessions = $sessionInfo['Sessions'] ?? [];
        $currentSessionNum = $integer(
            $sessionInfo['CurrentSessionNum'] ?? null
        );

        $currentSession = [];

        if (is_array($sessions)) {
            if (
                $currentSessionNum !== null
                && isset($sessions[$currentSessionNum])
                && is_array($sessions[$currentSessionNum])
            ) {
                $currentSession = $sessions[$currentSessionNum];
            } elseif ($sessions !== []) {
                $firstSession = reset($sessions);

                if (is_array($firstSession)) {
                    $currentSession = $firstSession;
                }
            }
        }

        /*
        * Identifica al piloto propio mediante DriverCarIdx.
        * No presupone que sea el primer piloto de la lista.
        */
        $driverCarIdx = $integer(
            $driverInfo['DriverCarIdx'] ?? null
        );

        $driverUserId = $integer(
            $driverInfo['DriverUserID'] ?? null
        );

        $driver = null;
        $drivers = $driverInfo['Drivers'] ?? [];

        if (is_array($drivers)) {
            foreach ($drivers as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }

                $candidateCarIdx = $integer(
                    $candidate['CarIdx'] ?? null
                );

                if (
                    $driverCarIdx === null
                    || $candidateCarIdx !== $driverCarIdx
                ) {
                    continue;
                }

                $candidateUserId = $integer(
                    $candidate['UserID'] ?? null
                );

                if (
                    $driverUserId !== null
                    && $candidateUserId !== null
                    && $candidateUserId !== $driverUserId
                ) {
                    continue;
                }

                $driver = $candidate;
                break;
            }
        }

        /*
        * Busca el resultado del piloto en la sesión activa.
        */
        $result = null;
        $positions = $currentSession['ResultsPositions'] ?? [];

        if ($driverCarIdx !== null && is_array($positions)) {
            foreach ($positions as $position) {
                if (!is_array($position)) {
                    continue;
                }

                if (
                    $integer($position['CarIdx'] ?? null)
                    !== $driverCarIdx
                ) {
                    continue;
                }

                $result = [
                    'position' => $integer(
                        $position['Position'] ?? null
                    ),
                    'fastest_time_seconds' => $number(
                        $position['FastestTime'] ?? null
                    ),
                    'laps_complete' => $integer(
                        $position['LapsComplete'] ?? null
                    ),
                ];

                break;
            }
        }

        /*
        * Combustible inicial indicado en CarSetup.
        */
        $rearSetup = $carSetup['Rear'] ?? [];

        $setupFuel = is_array($rearSetup)
            ? $number($rearSetup['FuelLevel'] ?? null)
            : null;

        /*
         * --------------------------------------------------------------
         * LÍMITES ESTÁTICOS DE SECTOR
         *
         * Se conservan los porcentajes normalizados del IBT.
         * Si el IBT no contiene límites válidos, la sesión sigue
         * pudiendo procesarse y la vista mostrará que no hay datos.
         * --------------------------------------------------------------
         */
        try {
            $sectorBoundaries = $this->extractSectorBoundaries(
                $filePath
            );
        } catch (\RuntimeException $e) {
            $sectorBoundaries = [];
        }

        $sectorSegments = [];

        for (
            $i = 0;
            $i < count($sectorBoundaries) - 1;
            $i++
        ) {
            $sectorSegments[] = [
                'number' => $i + 1,
                'label' => 'S' . ($i + 1),
                'start_pct' => $sectorBoundaries[$i],
                'end_pct' => $sectorBoundaries[$i + 1],
            ];
        }


        /*
        * Construcción del JSON normalizado.
        */
        $payload = [
            'format_version' => 1,

            'source' => [
                'filename' => basename($filePath),
            ],

            'track' => [
                'id' => $integer($weekend['TrackID'] ?? null),
                'name' => $text($weekend['TrackName'] ?? null),
                'display_name' => $text(
                    $weekend['TrackDisplayName'] ?? null
                ),
                'display_short_name' => $text(
                    $weekend['TrackDisplayShortName'] ?? null
                ),
                'config_name' => $text(
                    $weekend['TrackConfigName'] ?? null
                ),
                'length_km' => $number(
                    $weekend['TrackLength'] ?? null
                ),
                'length_official_km' => $number(
                    $weekend['TrackLengthOfficial'] ?? null
                ),
                'city' => $text($weekend['TrackCity'] ?? null),
                'state' => $text($weekend['TrackState'] ?? null),
                'country' => $text($weekend['TrackCountry'] ?? null),
                'altitude_m' => $number(
                    $weekend['TrackAltitude'] ?? null
                ),
                'latitude' => $number(
                    $weekend['TrackLatitude'] ?? null
                ),
                'longitude' => $number(
                    $weekend['TrackLongitude'] ?? null
                ),
                'turns' => $integer(
                    $weekend['TrackNumTurns'] ?? null
                ),
                'pit_speed_limit_kph' => $number(
                    $weekend['TrackPitSpeedLimit'] ?? null
                ),
                'track_type' => $text(
                    $weekend['TrackType'] ?? null
                ),
                'direction' => $text(
                    $weekend['TrackDirection'] ?? null
                ),
            ],

            'sectors' => [
                'boundaries_pct' => $sectorBoundaries,
                'segments' => $sectorSegments,
                'count' => count($sectorSegments),
            ],

            'session' => [
                'id' => $integer($weekend['SessionID'] ?? null),
                'subsession_id' => $integer(
                    $weekend['SubSessionID'] ?? null
                ),
                'series_id' => $integer(
                    $weekend['SeriesID'] ?? null
                ),
                'season_id' => $integer(
                    $weekend['SeasonID'] ?? null
                ),
                'event_type' => $text(
                    $weekend['EventType'] ?? null
                ),
                'type' => $text(
                    $currentSession['SessionType'] ?? null
                ),
                'name' => $text(
                    $currentSession['SessionName'] ?? null
                ),
                'duration_seconds' => $number(
                    $currentSession['SessionTime'] ?? null
                ),
                'track_rubber_state' => $text(
                    $currentSession['SessionTrackRubberState'] ?? null
                ),
                'official' => $boolean(
                    $weekend['Official'] ?? null
                ),
                'race_week' => $integer(
                    $weekend['RaceWeek'] ?? null
                ),
                'track_dynamic' => $boolean(
                    $weekend['TrackDynamicTrack'] ?? null
                ),
                'current_session_num' => $currentSessionNum,
            ],

            'driver' => [
                'iracing_user_id' => $driverUserId,
                'car_idx' => $driverCarIdx,
                'name' => $text($driver['UserName'] ?? null),
                'car_number' => $text($driver['CarNumber'] ?? null),
                'irating' => $integer($driver['IRating'] ?? null),
                'incident_count' => $integer(
                    $driverInfo['DriverIncidentCount'] ?? null
                ),
                'setup_name' => $text(
                    $driverInfo['DriverSetupName'] ?? null
                ),
                'setup_modified' => $boolean(
                    $driverInfo['DriverSetupIsModified'] ?? null
                ),
                'setup_passed_tech' => $boolean(
                    $driverInfo['DriverSetupPassedTech'] ?? null
                ),
            ],

            'car' => [
                'id' => $integer($driver['CarID'] ?? null),
                'path' => $text($driver['CarPath'] ?? null),
                'name' => $text($driver['CarScreenName'] ?? null),
                'short_name' => $text(
                    $driver['CarScreenNameShort'] ?? null
                ),
                'class_name' => $text(
                    $driver['CarClassShortName'] ?? null
                ),
                'max_fuel_liters' => $number(
                    $driverInfo['DriverCarFuelMaxLtr'] ?? null
                ),
                'forward_gears' => $integer(
                    $driverInfo['DriverCarGearNumForward'] ?? null
                ),
                'gearbox_type' => $text(
                    $driverInfo['DriverGearboxType'] ?? null
                ),
            ],

            'conditions' => [
                'weather_type' => $text(
                    $weekend['TrackWeatherType'] ?? null
                ),
                'skies' => $text($weekend['TrackSkies'] ?? null),
                'track_surface_temp_c' => $number(
                    $weekend['TrackSurfaceTemp'] ?? null
                ),
                'track_surface_temp_crew_c' => $number(
                    $weekend['TrackSurfaceTempCrew'] ?? null
                ),
                'track_air_temp_c' => $number(
                    $weekend['TrackAirTemp'] ?? null
                ),
                'track_air_pressure_hg' => $number(
                    $weekend['TrackAirPressure'] ?? null
                ),
                'track_air_density_kg_m3' => $number(
                    $weekend['TrackAirDensity'] ?? null
                ),
                'track_wind_speed_m_s' => $number(
                    $weekend['TrackWindVel'] ?? null
                ),
                'track_wind_direction_rad' => $number(
                    $weekend['TrackWindDir'] ?? null
                ),
                'track_relative_humidity_pct' => $number(
                    $weekend['TrackRelativeHumidity'] ?? null
                ),
                'track_fog_pct' => $number(
                    $weekend['TrackFogLevel'] ?? null
                ),
                'track_precipitation_pct' => $number(
                    $weekend['TrackPrecipitation'] ?? null
                ),
            ],

            'setup' => [
                'update_count' => $integer(
                    $carSetup['UpdateCount'] ?? null
                ),
                'fuel_level_liters' => $setupFuel,
            ],

            'result' => $result,
        ];

        $directory = dirname($outputPath);

        if (
            !is_dir($directory)
            && !mkdir($directory, 0775, true)
            && !is_dir($directory)
        ) {
            throw new RuntimeException(
                "No se pudo crear el directorio: {$directory}"
            );
        }

        try {
            $json = json_encode(
                $payload,
                JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            throw new RuntimeException(
                'No se pudo codificar session.json: ' . $e->getMessage(),
                0,
                $e
            );
        }

        if (file_put_contents($outputPath, $json . PHP_EOL, LOCK_EX) === false) {
            throw new RuntimeException(
                "No se pudo escribir session.json: {$outputPath}"
            );
        }

        return $payload;
    }




    /**
     * Extrae los límites de sector desde SplitTimeInfo del texto
     * SessionInfo incluido en la cabecera del IBT.
     *
     * SectorStartPct contiene el inicio de cada sector. El límite
     * final 1.0 se añade aquí para cerrar el último sector.
     */
    public function extractSectorBoundaries(string $filePath): array
    {

        $sessionInfo = $this->readSessionInfoText($filePath);


        $splitStart = strpos($sessionInfo, 'SplitTimeInfo:');

        if ($splitStart === false) {
            throw new RuntimeException(
                'No se encontró SplitTimeInfo en el SessionInfo del IBT.'
            );
        }

        $splitInfo = substr($sessionInfo, $splitStart);

        // SessionInfo es texto YAML. El siguiente bloque de nivel raíz
        // termina SplitTimeInfo; así evitamos leer sectores de otra sección.
        if (preg_match('/\n[^ \t\r\n][^\r\n]*:/', $splitInfo, $nextSection, PREG_OFFSET_CAPTURE)) {
            $splitInfo = substr($splitInfo, 0, $nextSection[0][1]);
        }

        if (!preg_match('/Sectors:\s*(.*)/s', $splitInfo, $sectorSection)) {
            throw new RuntimeException(
                'No se encontró la lista Sectors dentro de SplitTimeInfo.'
            );
        }

        $sectorText = $sectorSection[1];
        preg_match_all(
            '/SectorNum:\s*(\d+)\s*[\r\n]+\s*SectorStartPct:\s*([+-]?(?:\d+(?:\.\d*)?|\.\d+))/i',
            $sectorText,
            $matches,
            PREG_SET_ORDER
        );

        if (count($matches) < 2) {
            throw new RuntimeException(
                'No se pudieron extraer al menos dos límites de sector del IBT.'
            );
        }

        $sectors = [];

        foreach ($matches as $match) {
            $sectors[(int) $match[1]] = (float) $match[2];
        }

        ksort($sectors, SORT_NUMERIC);
        $boundaries = array_values($sectors);

        // Validar y normalizar la secuencia obtenida del IBT.
        if (abs($boundaries[0]) > 0.000001) {
            array_unshift($boundaries, 0.0);
        } else {
            $boundaries[0] = 0.0;
        }

        $uniqueBoundaries = [];
        foreach ($boundaries as $boundary) {
            if ($boundary < 0.0 || $boundary >= 1.0) {
                continue;
            }

            if (
                empty($uniqueBoundaries) ||
                abs($boundary - $uniqueBoundaries[count($uniqueBoundaries) - 1]) > 0.000001
            ) {
                $uniqueBoundaries[] = $boundary;
            }
        }

        if (count($uniqueBoundaries) < 2) {
            throw new RuntimeException(
                'Los límites de sector del IBT no forman una secuencia válida.'
            );
        }

        $uniqueBoundaries[] = 1.0;

        return $uniqueBoundaries;
    }

    /**
     * Reconstruye los tiempos de sector a partir de LapDistPct y
     * SessionTime. Los cruces se interpolan entre muestras consecutivas.
     * Devuelve sectores nulos cuando falta alguno de sus cruces.
     */
    public function extractLapSectors(string $filePath): array
    {
        $boundaries = $this->extractSectorBoundaries($filePath);
        $sectorCount = count($boundaries) - 1;
        $firstInteriorBoundary = $boundaries[1];
        $lastInteriorBoundary = $boundaries[count($boundaries) - 2];

        $previous = null;
        $crossings = [];
        $finishTimes = [];
        $firstLapStartTime = null;
        $variables = ['SessionTime', 'Lap', 'LapDistPct'];

        foreach ($this->streamRecords($filePath, $variables) as $index => $record) {
            $lap = (int) ($record['Lap'] ?? 0);
            $dist = (float) ($record['LapDistPct'] ?? 0.0);
            $time = (float) ($record['SessionTime'] ?? 0.0);

            if (
                $firstLapStartTime === null
                && $lap === 1
                && $dist >= 0.0
                && $dist <= $firstInteriorBoundary
            ) {
                $firstLapStartTime = $time;
            }

            if ($previous === null) {
                $previous = ['lap' => $lap, 'dist' => $dist, 'time' => $time];
                continue;
            }

            $prevLap = $previous['lap'];
            $prevDist = $previous['dist'];
            $prevTime = $previous['time'];
            $timeDelta = $time - $prevTime;

            $finishResetCandidate =
                $prevDist >= $lastInteriorBoundary &&
                $dist <= $firstInteriorBoundary &&
                $dist < $prevDist;

            if (
                $timeDelta <= 0.0 ||
                (!$finishResetCandidate && (
                    $dist < 0.0 || $dist > 1.0 ||
                    $prevDist < 0.0 || $prevDist > 1.0
                ))
            ) {
                $previous = ['lap' => $lap, 'dist' => $dist, 'time' => $time];
                continue;
            }

            // Cruces de los límites interiores: se atribuyen a la vuelta actual.
            if ($lap > 0 && $lap === $prevLap && $dist >= $prevDist) {
                for ($boundaryIndex = 1; $boundaryIndex < $sectorCount; $boundaryIndex++) {
                    $boundary = $boundaries[$boundaryIndex];

                    if (
                        $prevDist < $boundary &&
                        $dist >= $boundary &&
                        !isset($crossings[$lap][$boundaryIndex])
                    ) {
                        $distanceDelta = $dist - $prevDist;
                        if ($distanceDelta > 0.0) {
                            $ratio = ($boundary - $prevDist) / $distanceDelta;
                            $crossings[$lap][$boundaryIndex] =
                                $prevTime + ($timeDelta * $ratio);
                        }
                    }
                }
            }

            // Cruce de meta: el instante corresponde al final de la vuelta anterior.
            if ($prevLap > 0 && $finishResetCandidate) {
                    logger()->debug('IBT lap boundary', [
                        'prevLap' => $prevLap,
                        'lap' => $lap,
                        'prevDist' => $prevDist,
                        'dist' => $dist,
                        'prevTime' => $prevTime,
                        'time' => $time,
                        'finishTimeKey' => $prevLap,
                    ]);
                }

            if ($prevLap > 0 && $finishResetCandidate) {
                $distanceDelta = (1.0 - $prevDist) + $dist;

                if ($distanceDelta > 0.0 && !isset($finishTimes[$prevLap])) {
                    $ratio = (1.0 - $prevDist) / $distanceDelta;
                    $finishTimes[$prevLap] = $prevTime + ($timeDelta * $ratio);
                }
            }

            $previous = ['lap' => $lap, 'dist' => $dist, 'time' => $time];
        }

        $lapNumbers = array_unique(array_merge(
            array_keys($crossings),
            array_keys($finishTimes)
        ));
        sort($lapNumbers, SORT_NUMERIC);

        $result = [];

        $firstAvailableLap = $lapNumbers[0] ?? null;

        foreach ($lapNumbers as $lapNumber) {
            $lapSectors = [];

            $isFirstAvailableLap = (int) $lapNumber === (int) $firstAvailableLap;

            for ($sector = 1; $sector <= $sectorCount; $sector++) {
            if ($lapNumber === 1) {
                // Primera vuelta: comienza en la primera muestra disponible.
                if ($sector === 1) {
                    $start = $firstLapStartTime;
                    $end = $crossings[1][1] ?? null;
                } elseif ($sector === $sectorCount) {
                    $start = $crossings[1][$sectorCount - 1] ?? null;
                    $end = $finishTimes[1] ?? null;
                } else {
                    $start = $crossings[1][$sector - 1] ?? null;
                    $end = $crossings[1][$sector] ?? null;
                }
            } else {
                // Vueltas posteriores: usar los cruces de esa misma vuelta.
                if ($sector === 1) {
                    $start = $finishTimes[$lapNumber - 1] ?? null;
                    $end = $crossings[$lapNumber][1] ?? null;
                } elseif ($sector === $sectorCount) {
                    $start = $crossings[$lapNumber][$sectorCount - 1] ?? null;
                    $end = $finishTimes[$lapNumber] ?? null;
                } else {
                    $start = $crossings[$lapNumber][$sector - 1] ?? null;
                    $end = $crossings[$lapNumber][$sector] ?? null;
                }
            }

            $lapSectors['S' . $sector] =
                ($start !== null && $end !== null && $end > $start)
                    ? $end - $start
                    : null;
        }


            $result[(int) $lapNumber] = $lapSectors;
        }

        logger()->debug('IBT sector extraction keys', [
            'crossings' => array_keys($crossings),
            'finishTimes' => array_keys($finishTimes),
            'result' => array_keys($result),
            'crossings_lap_4' => $crossings[4] ?? null,
            'crossings_lap_5' => $crossings[5] ?? null,
            'finish_time_4' => $finishTimes[4] ?? null,
            'finish_time_5' => $finishTimes[5] ?? null,
        ]);


        return $result;
    }


        /**
     * Extrae las vueltas detectadas del IBT y las escribe
     * en un archivo JSON procesado.
     *
     * El JSON contiene únicamente la información necesaria
     * para la capa base de vueltas y su estado meteorológico
     * representativo.
     *
     * No modifica el IBT original.
     */
    public function writeLapsJson(
        string $filePath,
        string $outputPath
    ): array {
        if (!is_file($filePath)) {
            throw new RuntimeException(
                "IBT no encontrado: {$filePath}"
            );
        }

        $laps = $this->extractLaps($filePath);
        $sectors = $this->extractLapSectors($filePath);
        $weather = $this->extractLapWeather($filePath);
        $fuel = $this->extractLapFuel($filePath);
        $incidents = $this->extractLapIncidents($filePath);

        $directory = dirname($outputPath);

        if (
            !is_dir($directory) &&
            !mkdir($directory, 0775, true) &&
            !is_dir($directory)
        ) {
            throw new RuntimeException(
                "No se pudo crear el directorio de salida: {$directory}"
            );
        }

        /*
        * --------------------------------------------------------------
        * Asociamos la meteorología a las vueltas cronometradas
        * conservando el orden nativo del IBT.
        *
        * No exponemos índices de registros ni tiempos internos
        * en el JSON final.
        * --------------------------------------------------------------
        */
        $weatherIndex = 0;
        $processedLaps = [];

        foreach ($laps as $lap) {
            $lapNumber = (int) $lap['lap'];

            $associationLapNumber = (int) (
                $lap['candidate_lap'] ?? $lapNumber
            );

            // extractLapSectors() indexa los sectores con el valor nativo
            // de Lap; tras ciertos resets, este coincide con candidate_lap,
            // no con el índice interno usado para la vuelta cronometrada.
            $sectorLapNumber = (int) (
                $lap['candidate_lap'] ?? $lapNumber
            );

            $lapPayload = [
                'lap' => $lapNumber,
                'lap_time' => (float) $lap['lap_time'],
                'candidate_lap' => $lap['candidate_lap'] ?? null,
                'is_pit_lap' => (bool) ($lap['is_pit_lap'] ?? false),
                'sectors' => $sectors[$sectorLapNumber] ?? [],
            ];

            while (
                    isset($weather[$weatherIndex]) &&
                    (int) $weather[$weatherIndex]['lap'] < $associationLapNumber
                ) {
                    $weatherIndex++;
                }

                if (
                    isset($weather[$weatherIndex]) &&
                    (int) $weather[$weatherIndex]['lap'] === $associationLapNumber
                ) {
                    $lapPayload['weather'] =
                        $weather[$weatherIndex]['weather'];

                    $weatherIndex++;
                }

            if (isset($fuel[$associationLapNumber])) {
                    $lapPayload['fuel_start'] =
                        $fuel[$associationLapNumber]['fuel_start'];

                    $lapPayload['fuel_end'] =
                        $fuel[$associationLapNumber]['fuel_end'];

                    $lapPayload['fuel_used'] =
                        $fuel[$associationLapNumber]['fuel_used'];
                }

            logger()->debug('IBT lap-sector association', [
                'lap' => $lapNumber,
                'candidate_lap' => $lap['candidate_lap'] ?? null,
                'sector_lap' => $sectorLapNumber,
                'lap_time' => $lap['lap_time'] ?? null,
                'sectors' => $sectors[$sectorLapNumber] ?? null,
                'previous_sectors' => $sectors[$sectorLapNumber - 1] ?? null,
                'next_sectors' => $sectors[$sectorLapNumber + 1] ?? null,
            ]);

            $lapPayload['incidents'] =
                $incidents[$associationLapNumber] ?? [];

            $processedLaps[] = $lapPayload;
        }

        $fuelPerLapValues = [];

        foreach ($fuel as $fuelLap) {
            if (
                isset($fuelLap['fuel_used']) &&
                is_numeric($fuelLap['fuel_used'])
            ) {
                $fuelPerLapValues[] =
                    (float) $fuelLap['fuel_used'];
            }
        }

        $fuelPerLap = null;

        if (!empty($fuelPerLapValues)) {
            $fuelPerLap =
                array_sum($fuelPerLapValues) /
                count($fuelPerLapValues);
        }

        $incidentCount = 0;

        foreach ($processedLaps as $lapPayload) {
            foreach ($lapPayload['incidents'] ?? [] as $incident) {
                $incidentCount +=
                    (int) ($incident['count'] ?? 0);
            }
        }



        $payload = [
            'format_version' => 1,

            'source' => [
                'filename' => basename($filePath),
                'filesize' => (int) filesize($filePath),
            ],

            'summary' => [
                'timed_laps' => count($processedLaps),
                'fuel_per_lap' => $fuelPerLap,
                'incidents' => $incidentCount,
            ],

            'laps' => $processedLaps,
        ];

        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new RuntimeException(
                'No se pudo serializar laps.json: ' .
                json_last_error_msg()
            );
        }

        $written = file_put_contents(
            $outputPath,
            $json . PHP_EOL,
            LOCK_EX
        );

        if ($written === false) {
            throw new RuntimeException(
                "No se pudo escribir el archivo JSON: {$outputPath}"
            );
        }

        return $payload;
    }


    /**
     * Extrae los incidentes del piloto y los relaciona
     * con su vuelta nativa de iRacing.
     *
     * La fuente del contador es PlayerCarDriverIncidentCount.
     * PlayerIncidents no se utiliza como contador acumulativo.
     *
     * Cada incremento del contador genera un evento con:
     * - count
     * - total
     * - session_time
     * - lap_dist_pct
     */
    private function extractLapIncidents(
        string $filePath
    ): array {
        $variables = [
            'SessionTime',
            'Lap',
            'LapDistPct',
            'PlayerCarDriverIncidentCount',
        ];

        $result = [];

        $previousIncidentCount = null;

        foreach (
            $this->streamRecords(
                $filePath,
                $variables
            ) as $record
        ) {
            $incidentCount =
                (int) ($record['PlayerCarDriverIncidentCount'] ?? 0);

            if ($previousIncidentCount === null) {
                $previousIncidentCount =
                    $incidentCount;

                continue;
            }

            $delta =
                $incidentCount -
                $previousIncidentCount;

            if ($delta > 0) {
                $lap =
                    (int) ($record['Lap'] ?? 0);

                if ($lap > 0) {
                    $result[$lap][] = [
                        'count' => $delta,

                        'total' =>
                            $incidentCount,

                        'session_time' =>
                            (float) ($record['SessionTime'] ?? 0),

                        'lap_dist_pct' =>
                            (float) ($record['LapDistPct'] ?? 0),
                    ];
                }
            }

            $previousIncidentCount =
                $incidentCount;
        }

        return $result;
    }


    /**
     * Extrae el consumo de combustible de cada vuelta positiva
     * del IBT.
     *
     * FuelLevel se conserva en litros RAW. Para cada vuelta se
     * toma la primera muestra como fuel_start y la última como
     * fuel_end. fuel_used se calcula como la diferencia entre
     * ambas.
     *
     * No se aplica redondeo ni reglas de validez de vuelta.
     */
    private function extractLapFuel(
        string $filePath
    ): array {
        $variables = [
            'Lap',
            'FuelLevel',
        ];

        $ranges = [];
        $currentLap = null;
        $currentStart = null;
        $lastIndex = null;

        foreach (
            $this->streamRecords(
                $filePath,
                ['Lap']
            ) as $index => $record
        ) {
            $lastIndex = $index;
            $lap = (int) ($record['Lap'] ?? 0);

            if ($currentLap === null) {
                $currentLap = $lap;
                $currentStart = $index;
                continue;
            }

            if ($lap !== $currentLap) {
                if (
                    $currentLap > 0 &&
                    $currentStart !== null
                ) {
                    $ranges[] = [
                        'lap' => $currentLap,
                        'start' => $currentStart,
                        'end' => $index - 1,
                    ];
                }

                $currentLap = $lap;
                $currentStart = $index;
            }
        }

        if (
            $currentLap !== null &&
            $currentLap > 0 &&
            $currentStart !== null
        ) {
            $ranges[] = [
                'lap' => $currentLap,
                'start' => $currentStart,
                'end' => $lastIndex ?? $currentStart,
            ];
        }

        $result = [];

        foreach ($ranges as $range) {
            $fuelStart = null;
            $fuelEnd = null;

            foreach (
                $this->streamRecordsRange(
                    $filePath,
                    $range['start'],
                    $range['end'],
                    $variables
                ) as $record
            ) {
                if (
                    isset($record['FuelLevel']) &&
                    is_numeric($record['FuelLevel'])
                ) {
                    $fuel = (float) $record['FuelLevel'];

                    if ($fuelStart === null) {
                        $fuelStart = $fuel;
                    }

                    $fuelEnd = $fuel;
                }
            }

            if (
                $fuelStart === null ||
                $fuelEnd === null
            ) {
                continue;
            }

            $fuelUsed = $fuelStart - $fuelEnd;

            $result[$range['lap']] = [
                'fuel_start' => $fuelStart,
                'fuel_end' => $fuelEnd,
                'fuel_used' => $fuelUsed,
            ];
        }

        return $result;
    }


    /**
     * Extrae un estado meteorológico representativo para cada
     * tramo de vuelta presente en el IBT.
     *
     * Los límites se obtienen directamente de los cambios de
     * la variable nativa Lap. Los estados transitorios Lap 0
     * quedan fuera de las vueltas positivas.
     *
     * WindVel, WindDir y RelativeHumidity se promedian sobre
     * las muestras de cada vuelta.
     *
     * TrackWetness, Skies, Precipitation y WeatherDeclaredWet
     * conservan el valor RAW de la primera muestra de la vuelta.
     */
    private function extractLapWeather(
        string $filePath
    ): array {
        $variables = [
            'Lap',
            'TrackTemp',
            'TrackWetness',
            'Skies',
            'WindVel',
            'WindDir',
            'RelativeHumidity',
            'Precipitation',
            'WeatherDeclaredWet',
        ];

        $ranges = [];
        $currentLap = null;
        $currentStart = null;

        foreach (
            $this->streamRecords(
                $filePath,
                ['Lap']
            ) as $index => $record
        ) {
            $lap = (int) ($record['Lap'] ?? 0);

            if ($currentLap === null) {
                $currentLap = $lap;
                $currentStart = $index;
                continue;
            }

            if ($lap !== $currentLap) {
                if (
                    $currentLap > 0 &&
                    $currentStart !== null
                ) {
                    $ranges[] = [
                        'lap' => $currentLap,
                        'start' => $currentStart,
                        'end' => $index - 1,
                    ];
                }

                $currentLap = $lap;
                $currentStart = $index;
            }
        }

        if (
            $currentLap !== null &&
            $currentLap > 0 &&
            $currentStart !== null
        ) {
            $ranges[] = [
                'lap' => $currentLap,
                'start' => $currentStart,
                'end' => $index ?? $currentStart,
            ];
        }

        $result = [];

        foreach ($ranges as $range) {
            $windVelSum = 0.0;
            $windVelCount = 0;

            $windDirSum = 0.0;
            $windDirCount = 0;

            $humiditySum = 0.0;
            $humidityCount = 0;

            $stable = [
                'TrackTemp' => null,
                'TrackWetness' => null,
                'Skies' => null,
                'Precipitation' => null,
                'WeatherDeclaredWet' => null,
            ];

            foreach (
                $this->streamRecordsRange(
                    $filePath,
                    $range['start'],
                    $range['end'],
                    $variables
                ) as $record
            ) {
                if (
                    $stable['TrackTemp'] === null &&
                    isset($record['TrackTemp']) &&
                    is_numeric($record['TrackTemp'])
                ) {
                    $stable['TrackTemp'] = (float) $record['TrackTemp'];
                }
                if (
                    $stable['TrackWetness'] === null &&
                    isset($record['TrackWetness'])
                ) {
                    $stable['TrackWetness'] =
                        $record['TrackWetness'];
                }

                if (
                    $stable['Skies'] === null &&
                    isset($record['Skies'])
                ) {
                    $stable['Skies'] =
                        $record['Skies'];
                }

                if (
                    $stable['Precipitation'] === null &&
                    isset($record['Precipitation'])
                ) {
                    $stable['Precipitation'] =
                        $record['Precipitation'];
                }

                if (
                    $stable['WeatherDeclaredWet'] === null &&
                    isset($record['WeatherDeclaredWet'])
                ) {
                    $stable['WeatherDeclaredWet'] =
                        $record['WeatherDeclaredWet'];
                }

                if (
                    isset($record['WindVel']) &&
                    is_numeric($record['WindVel'])
                ) {
                    $windVelSum +=
                        (float) $record['WindVel'];

                    $windVelCount++;
                }

                if (
                    isset($record['WindDir']) &&
                    is_numeric($record['WindDir'])
                ) {
                    $windDirSum +=
                        (float) $record['WindDir'];

                    $windDirCount++;
                }

                if (
                    isset($record['RelativeHumidity']) &&
                    is_numeric($record['RelativeHumidity'])
                ) {
                    $humiditySum +=
                        (float) $record['RelativeHumidity'];

                    $humidityCount++;
                }
            }

            $windVel = null;
            $windDir = null;
            $humidity = null;

            if ($windVelCount > 0) {
                $windVel =
                    $windVelSum / $windVelCount;
            }

            if ($windDirCount > 0) {
                $windDir =
                    $windDirSum / $windDirCount;
            }

            if ($humidityCount > 0) {
                $humidity =
                    $humiditySum / $humidityCount;
            }

            $result[] = [
                'lap' => $range['lap'],

                'weather' => [

                    'TrackTemp' =>
                        $stable['TrackTemp'],

                    'TrackWetness' =>
                        $stable['TrackWetness'],

                    'Skies' =>
                        $stable['Skies'],

                    'WindVel' =>
                        $windVel,

                    'WindDir' =>
                        $windDir,

                    'RelativeHumidity' =>
                        $humidity,

                    'Precipitation' =>
                        $stable['Precipitation'],

                    'WeatherDeclaredWet' =>
                        $stable['WeatherDeclaredWet'],
                ],
            ];
        }

        return $result;
    }


    /**
     * Extrae las muestras de velocidad y marcha de una vuelta identificada
     * por candidate_lap, usando los cruces físicos de meta como límites.
     *
     * Esto mantiene la misma asociación de vueltas que writeLapTelemetryJson().
     * Speed se convierte de m/s a km/h.
     */
    public function extractLapTelemetry(
        string $filePath,
        int $lapNumber
    ): array {
        if ($lapNumber < 1 || !is_file($filePath)) {
            return [];
        }

        // Localizar el registro de vuelta por la numeración oficial de iRacing.
        $targetLap = null;

        foreach ($this->extractLaps($filePath) as $lap) {
            $candidateLap = (int) ($lap['candidate_lap'] ?? 0);

            if ($candidateLap === $lapNumber) {
                $targetLap = $lap;
                break;
            }
        }

        if (!is_array($targetLap) || !is_numeric($targetLap['record_index'] ?? null)) {
            return [];
        }

        $publicationIndex = (int) $targetLap['record_index'];
        $finishCrossings = [];
        $previousDistance = null;
        $previousEligible = false;

        foreach (
            $this->streamRecords(
                $filePath,
                ['LapDistPct', 'IsOnTrack', 'OnPitRoad', 'SessionTime']
            ) as $recordIndex => $record
        ) {
            $distance = $record['LapDistPct'] ?? null;
            $eligible =
                (bool) ($record['IsOnTrack'] ?? false) &&
                !(bool) ($record['OnPitRoad'] ?? false) &&
                is_numeric($distance) &&
                is_finite((float) $distance) &&
                (float) $distance >= 0.0 &&
                (float) $distance <= 1.0;

            if (!$eligible) {
                $previousDistance = null;
                $previousEligible = false;
                continue;
            }

            $distance = (float) $distance;

            if (
                $previousEligible &&
                $previousDistance !== null &&
                $previousDistance >= 0.90 &&
                $distance <= 0.10
            ) {
                $finishCrossings[] = (int) $recordIndex;
            }

            $previousDistance = $distance;
            $previousEligible = true;
        }

        // El tiempo de vuelta se publica después del cruce físico de meta.
        $currentCrossingPosition = null;

        foreach ($finishCrossings as $position => $crossingIndex) {
            if ($crossingIndex <= $publicationIndex) {
                $currentCrossingPosition = $position;
                continue;
            }

            break;
        }

        if ($currentCrossingPosition === null || $currentCrossingPosition < 1) {
            return [];
        }

        $startIndex = $finishCrossings[$currentCrossingPosition - 1];
        $finishIndex = $finishCrossings[$currentCrossingPosition];

        if ($finishIndex <= $startIndex) {
            return [];
        }

        $samples = [];

        foreach (
            $this->streamRecordsRange(
                $filePath,
                $startIndex,
                $finishIndex,
                [
                    'SessionTime',
                    'LapDistPct',
                    'Speed',
                    'Gear',
                    'Throttle',
                    'Brake',
                    'Clutch',
                    'IsOnTrack',
                    'OnPitRoad',
                    'Lat',
                    'Lon',
                    'Alt',
                    'Yaw',
                    'SteeringWheelAngle',
                ]
            ) as $record
        ) {
            if (
                !(bool) ($record['IsOnTrack'] ?? false) ||
                (bool) ($record['OnPitRoad'] ?? false)
            ) {
                continue;
            }

            $distance = $record['LapDistPct'] ?? null;
            $sessionTime = $record['SessionTime'] ?? null;
            $speed = $record['Speed'] ?? null;
            $gear = $record['Gear'] ?? null;

            $throttle = $record['Throttle'] ?? null;
            $brake = $record['Brake'] ?? null;
            $clutch = $record['Clutch'] ?? null;

            $lat = $record['Lat'] ?? null;
            $lon = $record['Lon'] ?? null;
            $alt = $record['Alt'] ?? null;
            $yaw = $record['Yaw'] ?? null;
            $steeringAngle = $record['SteeringWheelAngle'] ?? null;

            if (
                !is_numeric($distance) ||
                !is_numeric($sessionTime) ||
                !is_numeric($speed) ||
                !is_numeric($gear)
            ) {
                continue;
            }

            $distance = (float) $distance;
            $sessionTime = (float) $sessionTime;
            $speed = (float) $speed;

            if (
                !is_finite($distance) ||
                !is_finite($sessionTime) ||
                !is_finite($speed) ||
                $distance < 0.0 ||
                $distance > 1.0 ||
                $speed < 0.0
            ) {
                continue;
            }

            $samples[] = [
                'lap_dist_pct' => $distance,
                'session_time' => $sessionTime,
                'speed_kph' => round($speed * 3.6, 2),
                'gear' => (int) $gear,

                'throttle' => is_numeric($throttle) && is_finite((float) $throttle)
                    ? (float) $throttle
                    : null,

                'brake' => is_numeric($brake) && is_finite((float) $brake)
                    ? (float) $brake
                    : null,

                'clutch' => is_numeric($clutch) && is_finite((float) $clutch)
                    ? (float) $clutch
                    : null,

                'position' => [
                    'lat' => is_numeric($lat) && is_finite((float) $lat)
                        ? (float) $lat
                        : null,

                    'lon' => is_numeric($lon) && is_finite((float) $lon)
                        ? (float) $lon
                        : null,

                    'alt' => is_numeric($alt) && is_finite((float) $alt)
                        ? (float) $alt
                        : null,
                ],

                'yaw' => is_numeric($yaw) && is_finite((float) $yaw)
                    ? (float) $yaw
                    : null,
                'steering_angle' => is_numeric($steeringAngle)
                    && is_finite((float) $steeringAngle)
                        ? (float) $steeringAngle
                        : null,

            ];
        }

        return $samples;
    }



     /**
     * Exporta la telemetría RAW agrupada por vuelta en un único JSON.
     *
     * No exige cruces de meta ni valida que las vueltas estén completas.
     * Mantiene en memoria únicamente las muestras de la vuelta actual.
     */
    public function writeRawLapTelemetryJson(
        string $filePath,
        string $outputPath
    ): array {
        if (!is_file($filePath)) {
            throw new \RuntimeException("IBT no encontrado: {$filePath}");
        }

        $directory = dirname($outputPath);

        if (
            !is_dir($directory) &&
            !mkdir($directory, 0775, true) &&
            !is_dir($directory)
        ) {
            throw new \RuntimeException(
                "No se pudo crear el directorio: {$directory}"
            );
        }

        $temporaryPath = $outputPath . '.partial';
        $handle = fopen($temporaryPath, 'wb');

        if ($handle === false) {
            throw new \RuntimeException(
                "No se pudo crear el JSON RAW: {$temporaryPath}"
            );
        }

        $recordCount = 0;
        $lapCount = 0;
        $currentCandidateLap = null;
        $currentLapCompleted = null;
        $samples = [];
        $isPitLap = false;

        $write = static function ($handle, string $content): void {
            $length = strlen($content);
            $written = 0;

            while ($written < $length) {
                $result = fwrite($handle, substr($content, $written));
                if ($result === false || $result === 0) {
                    throw new \RuntimeException(
                        'Error escribiendo el JSON RAW.'
                    );
                }
                $written += $result;
            }
        };

        $flushLap = function () use (
            &$samples,
            &$currentCandidateLap,
            &$currentLapCompleted,
            &$isPitLap,
            &$lapCount,
            $handle,
            $write
        ): void {
            if ($currentCandidateLap === null || empty($samples)) {
                return;
            }

            $speeds = array_column($samples, 'speed_kph');
            $gearsObserved = array_values(array_unique(
                array_column($samples, 'gear')
            ));
            sort($gearsObserved, SORT_NUMERIC);

            $movingSpeeds = array_values(array_filter(
                $speeds,
                static fn ($speed) => $speed >= 5
            ));
            $gearsUsed = array_values(array_filter(
                $gearsObserved,
                static fn ($gear) => $gear > 0
            ));

            $payload = [
                'format_version' => 2,
                'lap' => $currentLapCompleted,
                'candidate_lap' => $currentCandidateLap,
                'lap_time' => null,
                'is_pit_lap' => $isPitLap,
                'sample_count' => count($samples),
                'metrics' => [
                    'speed_min_kph' => min($speeds),
                    'speed_max_kph' => max($speeds),
                    'speed_min_moving_kph' => empty($movingSpeeds)
                        ? null
                        : min($movingSpeeds),
                    'gears_used' => $gearsUsed,
                    'gears_observed' => $gearsObserved,
                ],
                'samples' => $samples,
            ];

            $json = json_encode(
                $payload,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            );

            if ($lapCount > 0) {
                $write($handle, ',');
            }

            $write($handle, $json);
            $lapCount++;
            $samples = [];
        };

        try {
            $write($handle, "{\"format_version\":2,\"mode\":\"raw\",\"laps\":[");

            $variables = [
                'Lap', 'LapCompleted',
                'LapDistPct', 'SessionTime', 'Speed', 'Gear',
                'Lat', 'Lon', 'Alt', 'Yaw', 'OnPitRoad',
                'SteeringWheelAngle',
            ];

            foreach ($this->streamRecords($filePath, $variables) as $record) {
                $candidateLap = isset($record['Lap'])
                    ? (int) $record['Lap']
                    : 0;
                $lapCompleted = isset($record['LapCompleted'])
                    ? (int) $record['LapCompleted']
                    : null;

                if (
                    $currentCandidateLap !== null
                    && $candidateLap !== $currentCandidateLap
                ) {
                    $flushLap();
                    $currentCandidateLap = null;
                    $currentLapCompleted = null;
                    $isPitLap = false;
                }

                if ($currentCandidateLap === null) {
                    $currentCandidateLap = $candidateLap;
                    $currentLapCompleted = $lapCompleted;
                }

                $distance = $record['LapDistPct'] ?? null;
                $sessionTime = $record['SessionTime'] ?? null;
                $speed = $record['Speed'] ?? null;
                $gear = $record['Gear'] ?? null;
                $lat = $record['Lat'] ?? null;
                $lon = $record['Lon'] ?? null;
                $alt = $record['Alt'] ?? null;
                $yaw = $record['Yaw'] ?? null;
                $steeringAngle = $record['SteeringWheelAngle'] ?? null;

                $samples[] = [
                    'lap_dist_pct' => is_numeric($distance)
                        ? (float) $distance : null,
                    'session_time' => is_numeric($sessionTime)
                        ? (float) $sessionTime : null,
                    'speed_kph' => is_numeric($speed)
                        ? round((float) $speed * 3.6, 2) : null,
                    'gear' => is_numeric($gear) ? (int) $gear : null,
                    'position' => [
                        'lat' => is_numeric($lat) ? (float) $lat : null,
                        'lon' => is_numeric($lon) ? (float) $lon : null,
                        'alt' => is_numeric($alt) ? (float) $alt : null,
                    ],
                    'yaw' => is_numeric($yaw) ? (float) $yaw : null,
                    'steering_angle' => is_numeric($steeringAngle)
                        && is_finite((float) $steeringAngle)
                            ? (float) $steeringAngle
                            : null,
                ];

                if ((bool) ($record['OnPitRoad'] ?? false)) {
                    $isPitLap = true;
                }


                $recordCount++;
            }

            $flushLap();
            $write($handle, "]}\n");

            if (!fflush($handle)) {
                throw new \RuntimeException(
                    'No se pudo vaciar el buffer del JSON RAW.'
                );
            }
        } catch (\Throwable $e) {
            fclose($handle);
            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
            throw $e;
        }

        fclose($handle);

        if (!rename($temporaryPath, $outputPath)) {
            @unlink($temporaryPath);
            throw new \RuntimeException(
                'No se pudo finalizar el JSON RAW.'
            );
        }

        return [
            'path' => $outputPath,
            'records' => $recordCount,
            'laps' => $lapCount,
            'bytes' => filesize($outputPath),
            'sha256' => hash_file('sha256', $outputPath),
        ];
    }






    /**
     * Genera un JSON independiente de telemetría por vuelta.
     *
     * El IBT sigue siendo la fuente de verdad.
     * Los JSON son artefactos derivados y regenerables.
     */

    public function writeLapTelemetryJson(
        string $filePath,
        string $outputDirectory,
        array $laps
    ): array {
        if (!is_file($filePath)) {
            throw new \RuntimeException(
                "IBT no encontrado: {$filePath}"
            );
        }

        /*
        * Detectar cruces físicos de meta mediante LapDistPct.
        * El tiempo oficial se publica algunos registros después
        * del cruce, por lo que se asocia usando record_index.
        */
        $finishCrossings = [];
        $previousDistance = null;
        $previousEligible = false;

        foreach (
            $this->streamRecords(
                $filePath,
                [
                    'LapDistPct',
                    'IsOnTrack',
                    'OnPitRoad',
                    'SessionTime',
                ]
            ) as $recordIndex => $record
        ) {
            $distance = $record['LapDistPct'] ?? null;

            $eligible =
                (bool) ($record['IsOnTrack'] ?? false) &&
                !(bool) ($record['OnPitRoad'] ?? false) &&
                is_numeric($distance) &&
                is_finite((float) $distance);

            // $eligible =
            //     (bool) ($record['IsOnTrack'] ?? false) &&
            //     !(bool) ($record['OnPitRoad'] ?? false) &&
            //     is_numeric($distance) &&
            //     is_finite((float) $distance) &&
            //     (float) $distance >= 0.0 &&
            //     (float) $distance <= 1.0;

            if (!$eligible) {
                $previousDistance = null;
                $previousEligible = false;
                continue;
            }

            $distance = (float) $distance;

            if (
                $previousEligible &&
                $previousDistance !== null &&
                $previousDistance >= 0.90 &&
                $distance <= 0.10
            ) {
                $finishCrossings[] = [
                    'record_index' => (int) $recordIndex,
                    'session_time' => (float) (
                        $record['SessionTime'] ?? 0
                    ),
                ];
            }

            $previousDistance = $distance;
            $previousEligible = true;
        }

        /*
        * Preparar las vueltas que tienen un tiempo publicado
        * y un record_index válido.
        */
        $timedLaps = [];

        foreach ($laps as $lap) {
            $recordIndex = $lap['record_index'] ?? null;
            $candidateLap = (int) ($lap['candidate_lap'] ?? 0);

            if (
                $candidateLap > 0 &&
                is_numeric($recordIndex) &&
                (int) $recordIndex >= 0
            ) {
                $lap['_record_index'] = (int) $recordIndex;
                $timedLaps[] = $lap;
            }
        }

        usort(
            $timedLaps,
            fn (array $a, array $b) =>
                $a['_record_index'] <=> $b['_record_index']
        );

        $generatedLaps = [];
        $skippedLaps = [];

        if (
            empty($timedLaps) ||
            count($finishCrossings) < 2
        ) {
            foreach ($timedLaps as $lap) {
                $skippedLaps[] = [
                    'candidate_lap' => (int) $lap['candidate_lap'],
                    'reason' => 'insufficient_finish_crossings',
                ];
            }

            return [
                'generated' => 0,
                'generated_laps' => [],
                'skipped_laps' => $skippedLaps,
                'directory' => $outputDirectory,
            ];
        }

        if (
            !is_dir($outputDirectory) &&
            !mkdir($outputDirectory, 0775, true) &&
            !is_dir($outputDirectory)
        ) {
            throw new \RuntimeException(
                "No se pudo crear el directorio: {$outputDirectory}"
            );
        }

        $generated = 0;
        $lastUsedCrossingPosition = -1;

        foreach ($timedLaps as $lap) {
            $lapNumber = (int) ($lap['candidate_lap'] ?? 0);
            $internalLap = (int) ($lap['lap'] ?? 0);
            $publicationIndex = (int) $lap['_record_index'];

            /*
            * Buscar el último cruce de meta anterior
            * o coincidente con la publicación del tiempo.
            */
            $currentCrossingPosition = null;

            foreach ($finishCrossings as $position => $crossing) {
                if ($crossing['record_index'] <= $publicationIndex) {
                    $currentCrossingPosition = $position;
                    continue;
                }

                break;
            }

            if (
                $currentCrossingPosition === null ||
                $currentCrossingPosition < 1 ||
                $currentCrossingPosition <= $lastUsedCrossingPosition
            ) {
                $skippedLaps[] = [
                    'candidate_lap' => $lapNumber,
                    'reason' => 'no_matching_crossing',
                    'publication_index' => $publicationIndex,
                    'crossing_position' => $currentCrossingPosition,
                ];

                continue;
            }

            $startIndex = (int) (
                $finishCrossings[
                    $currentCrossingPosition - 1
                ]['record_index']
            );

            $finishIndex = (int) (
                $finishCrossings[
                    $currentCrossingPosition
                ]['record_index']
            );

            if ($finishIndex <= $startIndex) {
                $skippedLaps[] = [
                    'candidate_lap' => $lapNumber,
                    'reason' => 'invalid_crossing_interval',
                    'start_index' => $startIndex,
                    'finish_index' => $finishIndex,
                ];

                continue;
            }

            /*
            * Recopilar muestras de la vuelta.
            * No reservar el cruce hasta comprobar que las muestras
            * son válidas; así una vuelta descartada no bloquea
            * la asociación de las siguientes.
            */
            $samples = [];

            foreach (
                $this->streamRecordsRange(
                    $filePath,
                    $startIndex,
                    $finishIndex,
                    [
                        'SessionTime',
                        'LapDistPct',
                        'Speed',
                        'Gear',
                        'Throttle',
                        'Brake',
                        'Clutch',
                        'IsOnTrack',
                        'OnPitRoad',
                        'Lat',
                        'Lon',
                        'Alt',
                        'Yaw',
                        'SteeringWheelAngle',
                    ]
                ) as $record
            ) {
                if (
                    !(bool) ($record['IsOnTrack'] ?? false) ||
                    (bool) ($record['OnPitRoad'] ?? false)
                ) {
                    continue;
                }

                $distance = $record['LapDistPct'] ?? null;
                $sessionTime = $record['SessionTime'] ?? null;
                $speed = $record['Speed'] ?? null;
                $gear = $record['Gear'] ?? null;

                if (
                    !is_numeric($distance) ||
                    !is_numeric($sessionTime) ||
                    !is_numeric($speed) ||
                    !is_numeric($gear)
                ) {
                    continue;
                }

                $distance = (float) $distance;
                $sessionTime = (float) $sessionTime;
                $speed = (float) $speed;

                if (
                    !is_finite($distance) ||
                    !is_finite($sessionTime) ||
                    !is_finite($speed) ||
                    $distance < 0.0 ||
                    $distance > 1.0 ||
                    $speed < 0.0
                ) {
                    continue;
                }

                $lat = $record['Lat'] ?? null;
                $lon = $record['Lon'] ?? null;
                $alt = $record['Alt'] ?? null;
                $yaw = $record['Yaw'] ?? null;
                $steeringAngle = $record['SteeringWheelAngle'] ?? null;
                $throttle = $record['Throttle'] ?? null;
                $brake = $record['Brake'] ?? null;
                $clutch = $record['Clutch'] ?? null;

                $samples[] = [
                    'lap_dist_pct' => $distance,
                    'session_time' => round($sessionTime, 6),
                    'speed_kph' => round($speed * 3.6, 2),
                    'gear' => (int) $gear,
                    'throttle' => is_numeric($throttle)
                        ? (float) $throttle
                        : null,

                    'brake' => is_numeric($brake)
                        ? (float) $brake
                        : null,

                    'clutch' => is_numeric($clutch)
                        ? (float) $clutch
                        : null,
                    'position' => [
                        'lat' => is_numeric($lat) && is_finite((float) $lat)
                            ? (float) $lat
                            : null,
                        'lon' => is_numeric($lon) && is_finite((float) $lon)
                            ? (float) $lon
                            : null,
                        'alt' => is_numeric($alt) && is_finite((float) $alt)
                            ? (float) $alt
                            : null,
                    ],
                    'yaw' => is_numeric($yaw) && is_finite((float) $yaw)
                        ? (float) $yaw
                        : null,

                    'steering_angle' => is_numeric($steeringAngle)
                        && is_finite((float) $steeringAngle)
                            ? (float) $steeringAngle
                            : null,

                ];
            }

            if (empty($samples)) {
                $skippedLaps[] = [
                    'candidate_lap' => $lapNumber,
                    'reason' => 'no_valid_samples',
                    'start_index' => $startIndex,
                    'finish_index' => $finishIndex,
                ];

                continue;
            }

            // Reservar el cruce únicamente tras generar muestras válidas.
            $lastUsedCrossingPosition = $currentCrossingPosition;

            $speeds = array_column($samples, 'speed_kph');

            $gearsObserved = array_values(array_unique(
                array_column($samples, 'gear')
            ));

            sort($gearsObserved, SORT_NUMERIC);

            $gearsUsed = array_values(array_filter(
                $gearsObserved,
                fn (int $gear) => $gear > 0
            ));

            $movingSpeeds = array_values(array_filter(
                $speeds,
                fn (float $speed) => $speed >= 5
            ));

            $payload = [
                'format_version' => 2,
                'lap' => $internalLap,
                'candidate_lap' => $lapNumber,
                'lap_time' => $lap['lap_time'] ?? null,
                'is_pit_lap' => (bool) (
                    $lap['is_pit_lap'] ?? false
                ),
                'sample_count' => count($samples),
                'metrics' => [
                    'speed_min_kph' => min($speeds),
                    'speed_max_kph' => max($speeds),
                    'speed_min_moving_kph' => empty($movingSpeeds)
                        ? null
                        : min($movingSpeeds),
                    'gears_used' => $gearsUsed,
                    'gears_observed' => $gearsObserved,
                ],
                'samples' => $samples,
            ];

            $outputPath = rtrim(
                $outputDirectory,
                DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR . sprintf(
                'lap_%03d.json',
                $lapNumber
            );

            $json = json_encode(
                $payload,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES |
                JSON_THROW_ON_ERROR
            );

            if (
                file_put_contents(
                    $outputPath,
                    $json . PHP_EOL,
                    LOCK_EX
                ) === false
            ) {
                throw new \RuntimeException(
                    "No se pudo escribir: {$outputPath}"
                );
            }

            $generated++;
            $generatedLaps[] = $lapNumber;
        }

        return [
            'generated' => $generated,
            'generated_laps' => $generatedLaps,
            'skipped_laps' => $skippedLaps,
            'directory' => $outputDirectory,
        ];
    }


    private function readVariableValue(
        string $record,
        array $variable
    ): mixed {
        $offset = $variable['offset'];
        $count = $variable['count'];

        return match ($variable['type']) {
            self::VAR_TYPE_INT =>
                $this->readIntValue(
                    $record,
                    $offset,
                    $count
                ),

            self::VAR_TYPE_BITFIELD =>
                $this->readIntValue(
                    $record,
                    $offset,
                    $count
                ),

            self::VAR_TYPE_FLOAT =>
                $this->readFloatValue(
                    $record,
                    $offset,
                    $count
                ),

            self::VAR_TYPE_DOUBLE =>
                $this->readDoubleValue(
                    $record,
                    $offset,
                    $count
                ),

            self::VAR_TYPE_BOOL =>
                $this->readBoolValue(
                    $record,
                    $offset,
                    $count
                ),

            self::VAR_TYPE_CHAR =>
                $this->readCharValue(
                    $record,
                    $offset,
                    $count
                ),

            default => null,
        };
    }


    private function readBoolValue(
        string $record,
        int $offset,
        int $count
    ): bool|array {
        $values = [];

        for ($i = 0; $i < $count; $i++) {
            $byte = unpack('C', substr($record, $offset + $i, 1));
            $values[] = ($byte[1] ?? 0) !== 0;
        }

        return $count === 1 ? $values[0] : $values;
    }

    private function readIntValue(
        string $record,
        int $offset,
        int $count
    ): int|array {
        $values = [];

        for ($i = 0; $i < $count; $i++) {
            $values[] = $this->unpackInt32(
                $record,
                $offset + ($i * 4)
            );
        }

        return $count === 1
            ? $values[0]
            : $values;
    }

    private function readFloatValue(
        string $record,
        int $offset,
        int $count
    ): float|array {
        $values = [];

        for ($i = 0; $i < $count; $i++) {
            $value = unpack(
                'g',
                substr(
                    $record,
                    $offset + ($i * 4),
                    4
                )
            );

            $values[] = (float) $value[1];
        }

        return $count === 1
            ? $values[0]
            : $values;
    }

    private function readDoubleValue(
        string $record,
        int $offset,
        int $count
    ): float|array {
        $values = [];

        for ($i = 0; $i < $count; $i++) {
            $value = unpack(
                'e',
                substr(
                    $record,
                    $offset + ($i * 8),
                    8
                )
            );

            $values[] = (float) $value[1];
        }

        return $count === 1
            ? $values[0]
            : $values;
    }

    private function readCharValue(
        string $record,
        int $offset,
        int $count
    ): string {
        return rtrim(
            substr(
                $record,
                $offset,
                $count
            ),
            "\0"
        );
    }




    /**
     * Lee exactamente $length bytes desde $offset.
     */
    private function readBytes(
        $handle,
        int $offset,
        int $length
    ): string {
        if (fseek($handle, $offset) !== 0) {
            throw new RuntimeException(
                "No se pudo posicionar el IBT en {$offset}."
            );
        }

        $data = fread(
            $handle,
            $length
        );

        if (
            $data === false ||
            strlen($data) !== $length
        ) {
            throw new RuntimeException(
                "No se pudieron leer {$length} bytes del IBT."
            );
        }

        return $data;
    }



    private function readCString(
        string $data,
        int $offset,
        int $length
    ): string {
        $value = substr(
            $data,
            $offset,
            $length
        );

        $nullPosition = strpos(
            $value,
            "\0"
        );

        if ($nullPosition !== false) {
            $value = substr(
                $value,
                0,
                $nullPosition
            );
        }

        return trim($value);
    }

    private function variableTypeName(int $type): string
    {
        return match ($type) {
            self::VAR_TYPE_INT => 'int',
            self::VAR_TYPE_BITFIELD => 'bitfield',
            self::VAR_TYPE_FLOAT => 'float',
            self::VAR_TYPE_DOUBLE => 'double',
            self::VAR_TYPE_BOOL => 'bool',
            self::VAR_TYPE_CHAR => 'char',
            default => 'unknown',
        };
    }


    /**
     * Lee un entero signed 32-bit little-endian.
     */
    private function unpackInt32(
        string $data,
        int $offset
    ): int {
        $value = unpack(
            'V',
            substr($data, $offset, 4)
        );

        return (int) $value[1];
    }

    /**
     * Lee un entero signed 64-bit little-endian.
     */
    private function unpackInt64(
        string $data,
        int $offset
    ): int {
        $value = unpack(
            'q',
            substr($data, $offset, 8)
        );

        return (int) $value[1];
    }

    /**
     * Lee un double IEEE-754 little-endian.
     */
    private function unpackDouble(
        string $data,
        int $offset
    ): float {
        $value = unpack(
            'e',
            substr($data, $offset, 8)
        );

        return (float) $value[1];
    }
}
