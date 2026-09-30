<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeriesStandingConfig extends Model
{
    protected $fillable = [
        'series_id',
        'car_class_id',
        'car_class_name',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'car_class_id' => 'integer',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(SeriesStanding::class, 'series_id', 'series_id')
            ->where('car_class_id', $this->car_class_id);
    }
}
