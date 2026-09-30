<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeriesStanding extends Model
{
    protected $fillable = [
        'series_id',
        'iracing_series_id',
        'iracing_season_id',
        'car_class_id',
        'scope',
        'division_key',
        'division',
        'race_week_num',
        'status',
        'drivers_count',
        'source_last_updated',
        'last_synced_at',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'division' => 'integer',
            'division_key' => 'integer',
            'car_class_id' => 'integer',
            'iracing_season_id' => 'integer',
            'iracing_series_id' => 'integer',
            'source_last_updated' => 'datetime',
            'last_synced_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(
            SeriesStandingDriver::class,
            'series_standing_id'
        );
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(
            SeriesStandingSnapshot::class,
            'series_standing_id'
        );
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }
}
