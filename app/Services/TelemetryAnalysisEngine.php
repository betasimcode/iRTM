<?php

namespace App\Services;

class TelemetryAnalysisEngine
{
    public static function analyze($telemetry)
    {
        $alerts = [];

        foreach (TelemetryRules::rules() as $rule) {

            if ($rule['check']($telemetry)) {
                $alerts[] = [
                    'name' => $rule['name'],
                    'type' => $rule['type'],
                    'message' => $rule['message'],
                    'suggestion' => $rule['suggestion'] ?? null,
                ];
            }
        }

        return $alerts;
    }
}