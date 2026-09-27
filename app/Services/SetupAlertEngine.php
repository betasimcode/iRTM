<?php

namespace App\Services;

class SetupAlertEngine
{
    public static function evaluate(array $values, array $context): array
    {
        $alerts = [];

        foreach (self::rules() as $rule) {

            // 🔥 FILTRO POR TRACK
            if (isset($rule['track']) && $rule['track'] !== $context['track']) {
                continue;
            }

            // 🔥 FILTRO POR TIPO DE CIRCUITO
            if (isset($rule['track_type']) && $rule['track_type'] !== $context['track_type']) {
                continue;
            }

            $score = 0;

            foreach ($rule['conditions'] as $cond) {

                $value = $values[$cond['key']] ?? null;

                if ($value === null) continue;

                if ($value >= $cond['min'] && $value <= $cond['max']) {
                    $score += $cond['weight'];
                }
            }

            if ($score >= $rule['threshold']) {
                $alerts[] = [
                    'name' => $rule['name'],
                    'type' => $rule['type'],
                    'score' => $score,
                ];
            }
        }

        return $alerts;
    }

    private static function rules(): array
    {
        return [

            [
                'name' => 'Too much downforce for Fuji',
                'type' => 'warning',
                'track' => 'fuji',

                'conditions' => [
                    [
                        'key' => 'rear_wing',
                        'min' => 10,
                        'max' => 20,
                        'weight' => 2
                    ]
                ],

                'threshold' => 2
            ],

            [
                'name' => 'Rear instability risk',
                'type' => 'danger',
                'track' => 'fuji',

                'conditions' => [
                    [
                        'key' => 'rear_arb',
                        'min' => 8,
                        'max' => 20,
                        'weight' => 1
                    ],
                    [
                        'key' => 'rear_ride_height',
                        'min' => 8,
                        'max' => 20,
                        'weight' => 1
                    ]
                ],

                'threshold' => 2
            ],

        ];
    }
}