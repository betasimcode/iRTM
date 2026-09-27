<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $telemetry_id
 * @property int $sector_number
 * @property numeric $sector_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LapSector newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LapSector newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LapSector query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LapSector whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LapSector whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LapSector whereSectorNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LapSector whereSectorTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LapSector whereTelemetryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LapSector whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LapSector extends Model
{
    protected $fillable = [
        'telemetry_id',
        'sector_number',
        'sector_time'
    ];


}
