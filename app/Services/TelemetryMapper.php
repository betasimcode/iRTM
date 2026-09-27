<?php

namespace App\Services;

class TelemetryMapper
{
    public static function map($mappedValues)
    {
        $telemetry = [];

        foreach ($mappedValues as $key => $value) {

            if (!str_contains($key, 'lasttemps')) {
                continue;
            }
        
            if (!preg_match('/_(fl|fr|rl|rr)$/', $key, $match)) {
                continue;
            }
        
            $pos = $match[1];
        
            if (!is_string($value)) {
                continue;
            }
        
            // 🔥 limpiar unidades
            $clean = str_replace(['C', ' '], '', $value);
        
            $temps = explode(',', $clean);
        
            if (count($temps) < 3) continue;
        
            $a = (float) $temps[0];
            $b = (float) $temps[1];
            $c = (float) $temps[2];
        
            if (str_contains($key, 'omi')) {
                // left side
                $telemetry["temp_outer_$pos"] = $a;
                $telemetry["temp_middle_$pos"] = $b;
                $telemetry["temp_inner_$pos"] = $c;
            }
        
            if (str_contains($key, 'imo')) {
                // right side
                $telemetry["temp_inner_$pos"] = $a;
                $telemetry["temp_middle_$pos"] = $b;
                $telemetry["temp_outer_$pos"] = $c;
            }
        }


        return $telemetry;
        
    }







}