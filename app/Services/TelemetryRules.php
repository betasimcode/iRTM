<?php

namespace App\Services;

class TelemetryRules
{
    public static function rules()
    {
        return [

            // 🔥 TEMPERATURA NEUMÁTICO FL
            [
                'name' => 'FL Tyre temp imbalance',
                'type' => 'warning',
            
                'check' => function ($t) {
            
                    if (!isset($t['lasttempsomi_fl'])) {
                        return false;
                    }
            
                    // 🔥 parsear string "64, 72, 76"
                    $temps = explode(',', $t['lasttempsomi_fl']);
            
                    if (count($temps) < 3) return false;
            
                    $inner = (float) trim($temps[2]);
                    $outer = (float) trim($temps[0]);
            
                    return ($inner - $outer) > 8;
                },
            
                'message' => 'FL tyre inner hotter than outer',
                'suggestion' => 'Reduce camber or adjust pressure'
            ],

            [
                'name' => 'Excessive camber FL',
                'type' => 'warning',
            
                'check' => function ($t) {
                    return isset($t['temp_inner_fl'], $t['temp_outer_fl']) &&
                           ($t['temp_inner_fl'] - $t['temp_outer_fl']) > 10;
                },
            
                'message' => 'Inner tyre significantly hotter than outer (FL)',
                'suggestion' => 'Reduce camber'
            ],

            [
                'name' => 'Excessive camber FR',
                'type' => 'warning',
            
                'check' => function ($t) {
                    return isset($t['temp_inner_fr'], $t['temp_outer_fr']) &&
                           ($t['temp_inner_fr'] - $t['temp_outer_fr']) > 10;
                },
            
                'message' => 'Inner tyre significantly hotter than outer (FR)',
                'suggestion' => 'Reduce camber'
            ],
            [
                'name' => 'Excessive camber RL',
                'type' => 'warning',
            
                'check' => function ($t) {
                    return isset($t['temp_inner_rl'], $t['temp_outer_rl']) &&
                           ($t['temp_inner_rl'] - $t['temp_outer_rl']) > 10;
                },
            
                'message' => 'Inner tyre significantly hotter than outer (RL)',
                'suggestion' => 'Reduce camber'
            ],

            [
                'name' => 'Excessive camber RR',
                'type' => 'warning',
            
                'check' => function ($t) {
                    return isset($t['temp_inner_rr'], $t['temp_outer_rr']) &&
                           ($t['temp_inner_rr'] - $t['temp_outer_rr']) > 10;
                },
            
                'message' => 'Inner tyre significantly hotter than outer (RR)',
                'suggestion' => 'Reduce camber'
            ],
            
            

        ];
    }
}