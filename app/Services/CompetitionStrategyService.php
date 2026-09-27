<?php

namespace App\Services;

use App\Models\SeriesEntry;

class CompetitionStrategyService
{
    public function build(
        SeriesEntry $entry,
        $currentRound,
        string $reportScope = 'season',
        ?array $stats = null,
        ?array $config = null
    ): ?array {
        if (!$currentRound || !$currentRound->track) {
            return null;
        }

        $car = $entry->competitionCar;

        if (!$car || !$car->iracing_car_id) {
            return null;
        }

        /*
         * Los datos de ritmo y consumo proceden del nuevo
         * sistema de estadísticas de Competition.
         */
        $stats ??= app(
            CompetitionStatsService::class
        )->build(
            $entry,
            $currentRound,
            $reportScope
        );

        if (
            !$stats ||
            !$stats['representative_pace'] ||
            !$stats['avg_fuel']
        ) {
            return null;
        }

        $series = $entry->series;
        $seriesConfig = $series->iracingSeries;

        $pace = $stats['representative_pace'];
        $consumption = $stats['avg_fuel'];

        /*
         * CONFIGURACIÓN
         *
         * Conservamos el concepto existente de strategy_config.
         */
        $config ??= session('strategy_config', []);

        $mode = $config['mode'] ?? 'auto';

        $forcedStops = $config['forced_stops'] ?? null;
        $tankLimit = $config['tank_limit'] ?? null;

        $mandatoryPit = $config['mandatory_pit']
            ?? ($seriesConfig->mandatory_pit ?? false);

        $refuelAllowed = $config['refuel_allowed']
            ?? ($seriesConfig->refuel_allowed ?? true);

        /*
        *         CÁLCULO DE VUELTAS
        */

        if ($currentRound->race_type === 'time') {

            $raceSeconds =
                $currentRound->race_length * 60;

            $laps = ceil(
                $raceSeconds / $pace
            );

        } else {

            $laps = $currentRound->race_length;
        }

        /*
         * AJUSTE REAL DE CARRERA
         *
         * Conservamos la lógica del racePlan() antiguo.
         */
        // $extraLaps = 0;

        // if ($currentRound->race_type === 'time') {

        //     // Vuelta adicional por cruce del tiempo límite.
        //     $extraLaps += 1;

        //     // Vuelta adicional por salida lanzada.
        //     if ($currentRound->start_type === 'rolling') {
        //         $extraLaps += 1;
        //     }
        // }

        // $baseLaps = $laps + $extraLaps;

        $baseLaps = $laps;

        $conservativeLaps = $baseLaps;
        $safetyLaps = $baseLaps;
        $aggressiveLaps = $baseLaps;

        /*
         * TANQUE
         */
        $baseTank = $car->tank_capacity;

        if (!empty($tankLimit)) {

            $tank = $tankLimit;

        } elseif (!empty($seriesConfig->fuel_limit)) {

            $tank = $baseTank *
                ($seriesConfig->fuel_limit / 100);

        } else {

            $tank = $baseTank;
        }

        /*
         * MARGEN
         */
        if ($laps < 20) {

            $margin = 1;

        } elseif ($laps <= 40) {

            $margin = 1;

        } else {

            $margin = 1;
        }

        /*
         * COMBUSTIBLE
         */
        $marginLaps =
            $config['margin_laps'] ?? 0;

        $extraFuel =
            $config['extra_fuel'] ?? 0;

        $contingencyFuel =
            ($marginLaps * $consumption) +
            $extraFuel;

        $fuelBase =
            $baseLaps *
            $consumption *
            $margin;

        $fuelBase += $contingencyFuel;

        $fuelConservative =
            $conservativeLaps *
            $consumption *
            ($margin * 1.03);

        $fuelAggressive =
            $aggressiveLaps *
            $consumption;

        $fuelSafety =
            $safetyLaps *
            $consumption *
            ($margin * 1.02);

        /*
         * BUFFER / RIESGO
         */
        $bufferLiters =
            $fuelBase -
            ($baseLaps * $consumption);

        $riskLevel = match (true) {

            $bufferLiters < 0.5 =>
                'Alto',

            $bufferLiters < 1.0 =>
                'Medio',

            default =>
                'Bajo',
        };

        /*
         * STINTS
         */
        $baseStints = max(
            1,
            ceil($fuelBase / $tank)
        );

        $conservativeStints = max(
            1,
            ceil($fuelConservative / $tank)
        );

        $aggressiveStints = max(
            1,
            ceil($fuelAggressive / $tank)
        );

        $safetyStints = max(
            1,
            ceil($fuelSafety / $tank)
        );

        /*
         * CONFIGURACIÓN MANUAL
         */
        if ($mode === 'manual') {

            if ($forcedStops !== null) {

                $manualStints =
                    $forcedStops + 1;

                $baseStints =
                    $manualStints;

                $conservativeStints =
                    $manualStints;

                $aggressiveStints =
                    $manualStints;

                $safetyStints =
                    $manualStints;
            }

            if (
                $mandatoryPit &&
                $baseStints < 2
            ) {
                $baseStints = 2;
            }

            if (!$refuelAllowed) {
                $baseStints = 1;
            }
        }

        /*
         * VALIDACIÓN FINAL
         */
        $fuelPerStint =
            $fuelBase / $baseStints;

        if ($fuelPerStint > $tank) {

            $baseStints = ceil(
                $fuelBase / $tank
            );
        }

        return [

            'estimated_laps' =>
                $laps,

            'real_laps' =>
                $baseLaps,

            'tank_capacity' =>
                $tank,

            'margin_percent' =>
                round(
                    ($margin - 1) * 100,
                    1
                ),

            'buffer' =>
                round(
                    $bufferLiters,
                    2
                ),

            'risk' =>
                $riskLevel,

            'base' => [

                'fuel' =>
                    round(
                        $fuelBase,
                        2
                    ),

                'stints' =>
                    $baseStints,
            ],

            'conservative' => [

                'fuel' =>
                    round(
                        $fuelConservative,
                        2
                    ),

                'stints' =>
                    $conservativeStints,
            ],

            'aggressive' => [

                'fuel' =>
                    round(
                        $fuelAggressive,
                        2
                    ),

                'stints' =>
                    $aggressiveStints,
            ],

            'safety_car' => [

                'fuel' =>
                    round(
                        $fuelSafety,
                        2
                    ),

                'stints' =>
                    $safetyStints,
            ],
        ];
    }
}
