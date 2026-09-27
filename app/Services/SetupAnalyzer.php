<?php

namespace App\Services;

class SetupAnalyzer
{
    public static function analyze($values)
    {
        $alerts = [];

        $map = [];

        // 🔹 indexar valores
        foreach ($values as $item) {
            $map[$item->key] = $item->value;
        }

        // =========================
        // 🛞 EJEMPLO: PRESIONES
        // =========================

        $fl = $map['carsetup.tires.leftfront.startingpressure'] ?? null;
        $fr = $map['carsetup.tires.rightfront.startingpressure'] ?? null;

        if ($fl && $fr && $fl !== $fr) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Front tyre pressure asymmetry (FL vs FR)',
                'keys' => [
                    'carsetup.tires.leftfront.startingpressure',
                    'carsetup.tires.rightfront.startingpressure'
                ]
            ];
        }

        // =========================
        // 🔧 EJEMPLO: BRAKE BIAS
        // =========================

        $bb = $map['carsetup.chassis.front.brakepressurebias'] ?? null;

        if ($bb) {
            $value = floatval($bb);

            if ($value < 45 || $value > 60) {
                $alerts[] = [
                    'type' => 'danger',
                    'message' => 'Brake bias out of optimal range',
                    'keys' => ['carsetup.chassis.front.brakepressurebias']
                ];
            }
        }

        return $alerts;
    }
}
