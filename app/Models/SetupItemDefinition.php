<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetupItemDefinition extends Model
{
    protected $fillable = [
        'raw_key',
        'label',
        'zone',
        'status'
    ];

    // 🔥 AÑADE ESTO
    public const ZONES = [
        'none',
        'front_aero',
        'fl_tyre','fl_susp',
        'front_dampers',
        'fl_dampers',
        'fr_dampers',
        'front_chassis',
        'fr_susp','fr_tyre',
        'main_chassis',
        'rl_tyre','rl_susp',
        'rear_chassis',
        'drive_train',
        'diff',
        'rear_dampers',
        'rl_dampers',
        'rr_dampers',
        'rr_susp','rr_tyre',
        'rear_aero',
        'data',
        'config',
        'telemetry_FL',
        'telemetry_FR',
        'telemetry_RL',
        'telemetry_RR',
        'telemetry_tyre_FL',
        'telemetry_tyre_FR',
        'telemetry_tyre_RL',
        'telemetry_tyre_RR'
    ];
}
