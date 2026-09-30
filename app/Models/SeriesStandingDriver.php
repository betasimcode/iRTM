<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesStandingDriver extends Model
{
    protected $fillable = [
        'series_standing_id',
        'cust_id',
        'rank',
        'division',
        'display_name',
        'country_code',
        'points',
        'raw_points',
        'week_dropped',
        'weeks_counted',
        'starts',
        'wins',
        'top5',
        'top25_percent',
        'poles',
        'avg_start_position',
        'avg_finish_position',
        'avg_field_size',
        'laps',
        'laps_led',
        'incidents',
        'irating',
        'license_category_id',
        'license_level',
        'safety_rating',
        'license_color',
        'helmet',
    ];

    protected function casts(): array
    {
        return [
            'helmet' => 'array',
            'points' => 'decimal:3',
            'raw_points' => 'decimal:3',
            'safety_rating' => 'decimal:2',
            'avg_start_position' => 'decimal:3',
            'avg_finish_position' => 'decimal:3',
            'avg_field_size' => 'decimal:3',
        ];
    }

    public function standing(): BelongsTo
    {
        return $this->belongsTo(
            SeriesStanding::class,
            'series_standing_id'
        );
    }
}
