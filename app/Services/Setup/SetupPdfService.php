<?php

namespace App\Services\Setup;

use App\Models\Setup;
use App\Models\SetupItemDefinition;

class SetupPdfService
{
    public static function build(Setup $setup): array
    {
        $setup->load([
            'stint.laps',
            'stint.track',
            'stint.user.team',
            'stint.session',
        ]);

        $stint = $setup->stint;

        $telemetryEnd = $stint->tyreTelemetry()
            ->where('snapshot_type', 'end')
            ->first();

            $telemetry = [];

            if ($telemetryEnd) {

                $telemetry = [

                    'fl' => [
                        'outer'  => round($telemetryEnd->temp_fl_o, 1),
                        'middle' => round($telemetryEnd->temp_fl_m, 1),
                        'inner'  => round($telemetryEnd->temp_fl_i, 1),
                        'wear' => (int) round($telemetryEnd->wear_fl * 100),
                    ],

                    'fr' => [
                        'inner'  => round($telemetryEnd->temp_fr_i, 1),
                        'middle' => round($telemetryEnd->temp_fr_m, 1),
                        'outer'  => round($telemetryEnd->temp_fr_o, 1),
                        'wear' => (int) round($telemetryEnd->wear_fr * 100),
                    ],

                    'rl' => [
                        'outer'  => round($telemetryEnd->temp_rl_o, 1),
                        'middle' => round($telemetryEnd->temp_rl_m, 1),
                        'inner'  => round($telemetryEnd->temp_rl_i, 1),
                        'wear' => (int) round($telemetryEnd->wear_rl * 100),
                    ],

                    'rr' => [
                        'inner'  => round($telemetryEnd->temp_rr_i, 1),
                        'middle' => round($telemetryEnd->temp_rr_m, 1),
                        'outer'  => round($telemetryEnd->temp_rr_o, 1),
                        'wear' => (int) round($telemetryEnd->wear_rr * 100),
                    ],

                ];
            }

        $definitions = SetupItemDefinition::query()
            ->select('raw_key', 'label', 'zone')
            ->get()
            ->keyBy('raw_key');

        $pdfValues = [];

        foreach ($setup->values as $item) {

            $definition = $definitions[$item->key] ?? null;

            if (!$definition) {
                continue;
            }

            $zone = strtoupper($definition->zone);
            $label = strtoupper($definition->label);

            $value = trim((string) $item->value);

            // Ángulos
            if (str_contains($value, ' deg')) {

                $value = str_replace(
                    ['+', ' deg'],
                    ['', 'º'],
                    $value
                );
            }

            // Milímetros
            if (str_contains($value, 'mm')) {

                $value = str_replace(
                    [' mm', 'mm', ' mm '],
                    ['', '', ''],
                    $value
                );
            }

            if (str_contains($value, 'Turns')) {

                $value = str_replace(
                    [' Turns'],
                    [' T'],
                    $value
                );
            }

            $value = preg_replace('/\s+/', ' ', trim($value));

            $pdfValues[$zone][$label] = $value;
        }


        $bestLap = optional(
            $stint?->laps
                ->where('lap_time', '>', 0)
                ->sortBy('lap_time')
                ->first()
        );

        return [

        'setup' => $setup,

        'stint' => $stint,

        'telemetry' => $telemetry,

        'values' => $pdfValues,

        'summary' => [

            'air_temp' => '19,7 °C',

            'track_temp' => '22,1 °C',

            'surface' => 'DRY',

            'wind' => 'NE 15,2 Km/h',

            'avg_laptime' => '1:27.236',

            'avg_consumption' => '1,5 L/lap',

            'duration' => '4,5 min',

            'laps' => 3,

            'best_lap' => 2,

            'best_laptime' => '1:26.150',

            'setup_code' => '20260624_1544-TG-RBRGP-F3-T-DRY',

        ]

    ];
    }





}
