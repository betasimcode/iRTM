<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeriesStandingSync extends Model
{
    protected $fillable = [
        'series_id',
        'sync_type',
        'status',
        'classifications_processed',
        'drivers_imported',
        'error_message',
        'started_at',
        'finished_at',
        'source_last_updated',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'source_last_updated' => 'datetime',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(
            SeriesStandingSnapshot::class,
            'series_standing_sync_id'
        );
    }
}
