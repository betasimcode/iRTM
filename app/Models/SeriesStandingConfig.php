<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeriesStandingConfig extends Model
{
    protected $fillable = [
        'series_id',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(
            SeriesStanding::class,
            'series_id',
            'series_id'
        );
    }
}
