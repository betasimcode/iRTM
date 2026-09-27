<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IrSector extends Model
{
    protected $table = 'ir_sectors';

    protected $fillable = [
        'ir_lap_id',
        'sector_number',
        'sector_time',
        'start_pct'
    ];

    public function lap()
    {
        return $this->belongsTo(IrLap::class, 'ir_lap_id');
    }
}
