<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IrTelemetryTyre extends Model
{
    protected $table = 'ir_telemetry_tyres';

    protected $fillable = [
        'stint_id',
        'lap_number',
        'snapshot_type',

        'temp_fl','temp_fr','temp_rl','temp_rr',

        'temp_fl_o','temp_fl_m','temp_fl_i',
        'temp_fr_o','temp_fr_m','temp_fr_i',
        'temp_rl_o','temp_rl_m','temp_rl_i',
        'temp_rr_o','temp_rr_m','temp_rr_i',

        'wear_fl','wear_fr','wear_rl','wear_rr',
    ];

    public function stint()
    {
        return $this->belongsTo(\App\Models\Stint::class, 'stint_id');
    }
}
