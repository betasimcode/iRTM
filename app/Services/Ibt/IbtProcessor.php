<?php

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
