<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackSector extends Model
{
    protected $fillable = [
        'track_id',
        'sector_number',
        'start_pct'
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}
