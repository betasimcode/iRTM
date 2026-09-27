<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $stint_id
 * @property int $lap
 * @property bool $is_pit_lap
 * @property numeric $lap_time
 * @property numeric $fuel
 * @property numeric|null $fuel_used
 * @property numeric|null $length_km
 * @property numeric $track_temp
 * @property numeric $air_temp
 * @property string $timestamp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $humidity
 * @property float|null $wind_speed
 * @property float|null $wind_dir
 * @property string|null $sky
 * @property string|null $track_state
 * @property int|null $car_id
 * @property int|null $track_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LapSector> $sectors
 * @property-read int|null $sectors_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereAirTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereCarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereFuel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereFuelUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereHumidity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereIsPitLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereLapTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereLengthKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereSky($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereStintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereTrackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereTrackState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereTrackTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereWindDir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereWindSpeed($value)
 * @mixin \Eloquent
 */
class Telemetry extends Model
{
    protected $fillable = [
    'user_id',
    'track_id',
    'car_id',
    'stint_id',
    'lap',
    'is_pit_lap',
    'lap_time',
    'fuel',
    'fuel_used',
    'length_km',
    'track_temp',
    'air_temp',
    'timestamp',
    'humidity',
    'wind_speed',
    'wind_dir',
    'sky',
    'track_state'
];

    public $timestamps = true;

    protected $casts = [
    'is_pit_lap' => 'boolean',
];

public function sectors()
{
    return $this->hasMany(\App\Models\LapSector::class, 'telemetry_id')
                ->orderBy('sector_number');
}

}
