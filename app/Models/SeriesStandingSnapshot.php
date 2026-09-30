<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesStandingSnapshot extends Model
{
    protected $fillable = [
        'series_standing_sync_id',
        'series_standing_id',
        'cust_id',
        'rank',
        'points',
        'raw_points',
        'irating',
        'weeks_counted',
        'starts',
        'wins',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:3',
            'raw_points' => 'decimal:3',
            'captured_at' => 'datetime',
        ];
    }

    public function sync(): BelongsTo
    {
        return $this->belongsTo(
            SeriesStandingSync::class,
            'series_standing_sync_id'
        );
    }

    public function standing(): BelongsTo
    {
        return $this->belongsTo(
            SeriesStanding::class,
            'series_standing_id'
        );
    }
}
