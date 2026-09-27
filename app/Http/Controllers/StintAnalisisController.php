<?php

namespace App\Http\Controllers;

use App\Models\Stint;
use App\Models\Series;

class StintAnalisisController extends Controller
{
    // 🔹 USO NORMAL
    public function show(Stint $stint)
    {
        return $this->buildAnalysis($stint);
    }

    // 🔹 USO DESDE SERIES
    public function showFromSeries(Series $series, Stint $stint)
    {
        // 🔒 opcional: seguridad

        if ($stint->user_id !== auth()->id()) {
            abort(403);
        }

        return $this->buildAnalysis($stint, $series);
    }

    // 🔥 CORE (REUTILIZABLE)
    private function buildAnalysis(Stint $stint, $series = null)
    {
        $stint->load('laps');

        $laps = $stint->laps
            ->where('lap_time', '>', 0)
            ->where('is_pit_lap', false)
            ->values();

        $bestLap = $laps->min('lap_time');

        $pace = null;

        if ($laps->count() >= 3) {
            $sorted = $laps->sortBy('lap_time')->values();
            $topCount = max(3, intval($laps->count() * 0.3));
            $pace = $sorted->take($topCount)->avg('lap_time');
        }

        $consistency = null;

        if ($pace && $laps->count() >= 5) {
            $variance = $laps->avg(fn($lap) =>
                pow($lap->lap_time - $pace, 2)
            );
            $consistency = sqrt($variance);
        }

        $competitiveEndLap = null;

        if ($pace) {
            $threshold = $pace * 1.03;

            foreach ($laps as $lap) {
                if ($lap->lap_time > $threshold) {
                    $competitiveEndLap = $lap->lap_number - 1;
                    break;
                }
            }

            if (!$competitiveEndLap && $laps->count()) {
                $competitiveEndLap = $laps->last()->lap_number;
            }
        }

        $telemetry = $stint->tyres;

        $start = $telemetry->firstWhere('snapshot_type', 'start');
        $end   = $telemetry->firstWhere('snapshot_type', 'end');

        // =========================
        // 🔴 WEAR
        // =========================
        $wear = null;

        if ($start && $end) {
            $wear = [
                'fl' => round(($start->wear_fl - $end->wear_fl) * 100, 1),
                'fr' => round(($start->wear_fr - $end->wear_fr) * 100, 1),
                'rl' => round(($start->wear_rl - $end->wear_rl) * 100, 1),
                'rr' => round(($start->wear_rr - $end->wear_rr) * 100, 1),
            ];
        }

        // =========================
        // 🔵 TEMPS
        // =========================
        $temps = null;

        if ($start && $end) {
            $temps = [
                'fl' => round($end->temp_fl, 1),
                'fr' => round($end->temp_fr, 1),
                'rl' => round($end->temp_rl, 1),
                'rr' => round($end->temp_rr, 1),
            ];

            $tempDelta = [
                'fl' => round($end->temp_fl - $start->temp_fl, 1),
                'fr' => round($end->temp_fr - $start->temp_fr, 1),
                'rl' => round($end->temp_rl - $start->temp_rl, 1),
                'rr' => round($end->temp_rr - $start->temp_rr, 1),
            ];
        }

            return view('series.stints.analysis', [
                'series' => $series,
                'stint' => $stint,
                'laps' => $laps,
                'pace' => $pace,
                'consistency' => $consistency,
                'competitiveEndLap' => $competitiveEndLap,

                // 🔥 CLAVE
                'wear' => $wear,
                'temps' => $temps,
                'tempDelta' => $tempDelta
            ]);
        }
}
