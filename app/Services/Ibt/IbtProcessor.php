<?php
/** UPDATE **/
namespace App\Services\Ibt;

use RuntimeException;

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
        ];

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

        $variables = ['SessionTime', 'Lap', 'LapDistPct'];

        foreach ($this->streamRecords($filePath, $variables) as $index => $record) {
            $lap = (int) ($record['Lap'] ?? 0);
            $dist = (float) ($record['LapDistPct'] ?? 0.0);
            $time = (float) ($record['SessionTime'] ?? 0.0);

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

        foreach ($lapNumbers as $lapNumber) {
            $lapSectors = [];


        for ($sector = 1; $sector <= $sectorCount; $sector++) {
            // Los cruces interiores están etiquetados con la vuelta siguiente.
            $crossingLap = (int) $lapNumber + 1;

            if ($sector === 1) {
                $start = $finishTimes[$lapNumber] ?? null;
                $end = $crossings[$crossingLap][1] ?? null;
            } elseif ($sector === $sectorCount) {
                $start = $crossings[$crossingLap][$sectorCount - 1] ?? null;
                $end = $finishTimes[$lapNumber + 1] ?? null;
            } else {
                $start = $crossings[$crossingLap][$sector - 1] ?? null;
                $end = $crossings[$crossingLap][$sector] ?? null;
            }

            $lapSectors['S' . $sector] =
                ($start !== null && $end !== null && $end > $start)
                    ? $end - $start
                    : null;
        }


            $result[(int) $lapNumber] = $lapSectors;
        }

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

            $lapPayload = [
                'lap' => $lapNumber,
                'lap_time' => (float) $lap['lap_time'],
                'candidate_lap' => $lap['candidate_lap'] ?? null,
                'sectors' => $sectors[$lapNumber] ?? [],
            ];

            while (
                isset($weather[$weatherIndex]) &&
                (int) $weather[$weatherIndex]['lap'] < $lapNumber
            ) {
                $weatherIndex++;
            }

            if (
                isset($weather[$weatherIndex]) &&
                (int) $weather[$weatherIndex]['lap'] === $lapNumber
            ) {
                $lapPayload['weather'] =
                    $weather[$weatherIndex]['weather'];

                $weatherIndex++;
            }

            if (isset($fuel[$lapNumber])) {
                $lapPayload['fuel_start'] =
                    $fuel[$lapNumber]['fuel_start'];

                $lapPayload['fuel_end'] =
                    $fuel[$lapNumber]['fuel_end'];

                $lapPayload['fuel_used'] =
                    $fuel[$lapNumber]['fuel_used'];
            }

            $lapPayload['incidents'] =
                $incidents[$lapNumber] ?? [];

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
                $this->readIntValue(
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
