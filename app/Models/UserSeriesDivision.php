<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSeriesDivision extends Model
{
    protected $fillable = [

        'user_id',

        'serie_id',

        'division_id',

        'division_name',

        'irating_avg',

        'first_seen_at',

        'last_seen_at',
    ];

    protected $casts = [

        'first_seen_at' => 'datetime',

        'last_seen_at' => 'datetime',
    ];

    // ============================================
    // USER
    // ============================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============================================
    // SERIES
    // ============================================

    public function series()
    {
        return $this->belongsTo(IracingSerie::class);
    }
}