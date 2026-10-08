<?php

namespace App\Services\Ibt;

use RuntimeException;

class IbtMetadataProcessor
{
    /**
     * Procesa la metadata contenida en un IBT.
     *
     * En esta primera fase no escribe en base de datos.
     * Únicamente devuelve los datos obtenidos del SessionInfo.
     */
    public function process(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException(
                "IBT no encontrado: {$filePath}"
            );
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new RuntimeException(
                "No se pudo leer el IBT: {$filePath}"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | WEEKEND INFO
        |--------------------------------------------------------------------------
        */

        $seriesId = $this->matchInt(
            $content,
            "/SeriesID:\\s*(\\d+)/"
        );

        $seasonId = $this->matchInt(
            $content,
            "/SeasonID:\\s*(\\d+)/"
        );

        $raceWeek = $this->matchInt(
            $content,
            "/RaceWeek:\\s*(\\d+)/"
        );

        $official = $this->matchInt(
            $content,
            "/Official:\\s*(\\d+)/"
        );

        $trackId = $this->matchInt(
            $content,
            "/TrackID:\\s*(\\d+)/"
        );

        $trackName = $this->matchString(
            $content,
            "/TrackName:\\s*([^\\r\\n]+)/"
        );

        $trackDisplayName = $this->matchString(
            $content,
            "/TrackDisplayName:\\s*([^\\r\\n]+)/"
        );

        $eventType = $this->matchString(
            $content,
            "/EventType:\\s*([^\\r\\n]+)/"
        );

        $subSessionId = $this->matchInt(
            $content,
            "/SubSessionID:\\s*(\\d+)/"
        );

        $sessionId = $this->matchInt(
            $content,
            "/SessionID:\\s*(\\d+)/"
        );

        /*
        |--------------------------------------------------------------------------
        | SETUP
        |--------------------------------------------------------------------------
        */

        $isFixedSetup = $this->matchInt(
            $content,
            "/IsFixedSetup:\\s*(\\d+)/"
        );

        /*
        |--------------------------------------------------------------------------
        | CURRENT SESSION
        |--------------------------------------------------------------------------
        */

        $currentSessionNum = $this->matchInt(
            $content,
            "/CurrentSessionNum:\\s*(\\d+)/"
        );

        $sessionType = $this->resolveCurrentSessionType(
            $content,
            $currentSessionNum
        );

        /*
        |--------------------------------------------------------------------------
        | TRACK RUBBER
        |--------------------------------------------------------------------------
        |
        | No buscamos "track_usage_pct".
        |
        | El dato RAW que nos interesa es:
        |
        | SessionTrackRubberState
        |
        */

        $trackUsageState = $this->resolveCurrentSessionValue(
            $content,
            $currentSessionNum,
            "SessionTrackRubberState"
        );

        /*
        |--------------------------------------------------------------------------
        | DRIVER
        |--------------------------------------------------------------------------
        */

        $driverCarIdx = $this->matchInt(
            $content,
            "/DriverCarIdx:\\s*(\\d+)/"
        );

        $driver = $this->resolveDriver(
            $content,
            $driverCarIdx
        );

        /*
        |--------------------------------------------------------------------------
        | RESULTADO
        |--------------------------------------------------------------------------
        */

        return [
            "track_id" => $trackId,

            "track_name" => $trackName,
            "track_display_name" => $trackDisplayName,

            "iracing_subsession_id" => $subSessionId,
            "iracing_session_id" => $sessionId,

            "series_id" => $seriesId,
            "season_id" => $seasonId,
            "race_week" => $raceWeek,

            "event_type" => $eventType,
            "official" => $official,

            "session_number" => $currentSessionNum,
            "session_type" => $sessionType,

            "car_id" => $driver["car_id"],
            "car_name" => $driver["car_name"],

            "driver_iracing_user_id" => $driver["user_id"],
            "driver_car_idx" => $driverCarIdx,

            "is_fixed_setup" => $isFixedSetup,

            "track_usage_state" => $trackUsageState,
        ];
    }

    /**
     * Extrae un entero mediante una expresión regular.
     */
    private function matchInt(
        string $content,
        string $pattern
    ): ?int {
        if (!preg_match($pattern, $content, $match)) {
            return null;
        }

        return (int) $match[1];
    }

    /**
     * Extrae una cadena mediante una expresión regular.
     */
    private function matchString(
        string $content,
        string $pattern
    ): ?string {
        if (!preg_match($pattern, $content, $match)) {
            return null;
        }

        return trim($match[1]);
    }

    /**
     * Obtiene el SessionType de la sesión actual.
     */
    private function resolveCurrentSessionType(
        string $content,
        ?int $currentSessionNum
    ): ?string {
        if ($currentSessionNum === null) {
            return null;
        }

        if (
            !preg_match(
                "/Sessions:(.*?)(?:\\n\\S|\\z)/s",
                $content,
                $blockMatch
            )
        ) {
            return null;
        }

        $sessionsBlock = $blockMatch[1];

        preg_match_all(
            "/SessionNum:\\s*(\\d+).*?SessionType:\\s*([^\\r\\n]+)/s",
            $sessionsBlock,
            $matches
        );

        foreach ($matches[1] ?? [] as $index => $num) {
            if ((int) $num === $currentSessionNum) {
                return trim($matches[2][$index]);
            }
        }

        return null;
    }

    /**
     * Obtiene un valor de la sesión actual.
     *
     * Actualmente se utiliza para SessionTrackRubberState.
     */
    private function resolveCurrentSessionValue(
        string $content,
        ?int $currentSessionNum,
        string $key
    ): ?string {
        if ($currentSessionNum === null) {
            return null;
        }

        if (
            !preg_match(
                "/Sessions:(.*?)(?:\\n\\S|\\z)/s",
                $content,
                $blockMatch
            )
        ) {
            return null;
        }

        $sessionsBlock = $blockMatch[1];

        $pattern =
            "/SessionNum:\\s*(\\d+).*?" .
            preg_quote($key, "/") .
            ":\\s*([^\\r\\n]+)/s";

        preg_match_all(
            $pattern,
            $sessionsBlock,
            $matches
        );

        foreach ($matches[1] ?? [] as $index => $num) {
            if ((int) $num === $currentSessionNum) {
                return trim($matches[2][$index]);
            }
        }

        return null;
    }

    /**
     * Obtiene el Driver correspondiente al DriverCarIdx actual.
     */
    private function resolveDriver(
        string $content,
        ?int $driverCarIdx
    ): array {
        $result = [
            "user_id" => null,
            "car_id" => null,
            "car_name" => null,
        ];

        if ($driverCarIdx === null) {
            return $result;
        }

        if (
            !preg_match(
                "/Drivers:\\s*(.*?)(?:\\n\\w|\\z)/s",
                $content,
                $driversMatch
            )
        ) {
            return $result;
        }

        $driversBlock = $driversMatch[1];

        /*
        |--------------------------------------------------------------------------
        | BUSCAR DRIVER POR CAR IDX
        |--------------------------------------------------------------------------
        */

        preg_match_all(
            "/CarIdx:\\s*(\\d+).*?(?=CarIdx:|\\z)/s",
            $driversBlock,
            $driverBlocks
        );

        foreach ($driverBlocks[1] ?? [] as $index => $carIdx) {
            if ((int) $carIdx !== $driverCarIdx) {
                continue;
            }

            $block = $driverBlocks[0][$index];

            $result["user_id"] = $this->matchInt(
                $block,
                "/UserID:\\s*(\\d+)/"
            );

            $result["car_id"] = $this->matchInt(
                $block,
                "/CarID:\\s*(\\d+)/"
            );

            $result["car_name"] = $this->matchString(
                $block,
                "/CarScreenName:\\s*([^\\r\\n]+)/"
            );

            break;
        }

        return $result;
    }
}
