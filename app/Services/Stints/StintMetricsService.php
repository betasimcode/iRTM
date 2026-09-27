<?php

namespace App\Services\Stints;

use App\Models\Stint;

class StintMetricsService
{
    public static function build(Stint $stint): array
    {
        $laps = $stint->laps;

        $metrics = [

            // PERFORMANCE
            'avg_lap' => $laps->avg('lap_time'),
            'best_lap' => $laps->min('lap_time'),
            'avg_fuel' => $laps->avg('fuel_consumed'),

            // INCIDENTS
            'total_incidents' => $laps->sum('incident_count'),
            'total_offtracks' => $laps->sum('offtrack_count'),

            // WEATHER
            'avg_track_temp' => $laps->avg('track_temp'),
            'avg_air_temp' => $laps->avg('air_temp'),
            'avg_wind_speed' => $laps->avg('wind_speed'),

            // VALIDATION
            'valid_laps' => $laps->where('is_valid_lap', true)->count(),
            'invalid_laps' => $laps->where('is_valid_lap', false)->count(),

            // SKY
            'current_sky' => $laps->last()?->sky,
            'current_track_state' => $laps->last()?->track_state,
        ];

        // =========================
        // TELEMETRY
        // =========================

        $telemetry = $stint->tyres;

        $start = $telemetry->firstWhere('snapshot_type', 'start');
        $end = $telemetry->firstWhere('snapshot_type', 'end');

        $metrics['wear'] = null;
        $metrics['temps'] = null;

        if ($start && $end) {

            $metrics['wear'] = [
                'fl' => round(($start->wear_fl - $end->wear_fl) * 100, 1),
                'fr' => round(($start->wear_fr - $end->wear_fr) * 100, 1),
                'rl' => round(($start->wear_rl - $end->wear_rl) * 100, 1),
                'rr' => round(($start->wear_rr - $end->wear_rr) * 100, 1),
            ];

            $metrics['temps'] = [
                'fl' => $end->temp_fl,
                'fr' => $end->temp_fr,
                'rl' => $end->temp_rl,
                'rr' => $end->temp_rr,
            ];
        }

        return $metrics;
    }
}