<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Stint;

/**
 * @property int $id
 * @property int|null $iracing_track_id
 * @property string $name
 * @property string|null $variant
 * @property string|null $city
 * @property string|null $country
 * @property numeric|null $length_km
 * @property numeric|null $fuel_per_lap
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $laps_standard
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RaceSession> $raceSessions
 * @property-read int|null $race_sessions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereFuelPerLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereIracingTrackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereLapsStandard($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereLengthKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Circuit whereVariant($value)
 * @mixin \Eloquent
 */
class Circuit extends Model
{
   protected $fillable = [
    'name',
    'display_name',
    'iracing_track_id',
    'city',
    'country',
    'length_km'
];

public function tracks($carName)
{
    $tracks = Stint::where('car', $carName)
        ->distinct()
        ->orderBy('track')
        ->pluck('track');

    return response()->json($tracks);
}


    /*
    |-------------------------------------------------
    | Relaciones
    |-------------------------------------------------
    */

    public function raceSessions()
    {
        return $this->hasMany(RaceSession::class);
    }
}

