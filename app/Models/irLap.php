<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IrLap extends Model {
    protected $table = 'ir_laps';
    protected $guarded = [];

    public function stint()
    {
        return $this->belongsTo(Stint::class, 'ir_stint_id');
    }

    public function sectors()
    {
        return $this->hasMany(\App\Models\IrSector::class, 'ir_lap_id')
                    ->orderBy('sector_number');
    }

}
