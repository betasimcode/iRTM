<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Track extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     * * @var array<int, string>
     */
    protected $fillable = [
        'display_name',
        'name',
        'short_name',
        'iracing_track_id',
        'variant',
        'logo',
        'logo_dark',
        'logo_light',
        'map',
        'map_svg',
        'length_km',
        'type',
        'layout_type',
        'direction',
        'longest_straight_m',
        'avg_speed_kmh',
        'grip_level',
        'surface_type',
        'braking_intensity',
        'traction_zones',
        'climate_type',
        'altitude_m',
    ];

    /**
     * Tipado de atributos para Laravel 12.
     * * @var array<string, string>
     */
    protected $casts = [
        'iracing_track_id' => 'integer',
        'length_km' => 'float',
    ];

    /**
     * Relación con los sectores del circuito.
     * Un circuito tiene muchos sectores (ej. los 6 de Sebring).
     * * @return HasMany<TrackSector, $this>
     */
    // app/Models/Track.php

    public static function fromIracing($iracingId)
    {
        return self::where('iracing_track_id', $iracingId)->firstOrFail();
    }

    public function sectors()
    {
        return $this->hasMany(TrackSector::class);
    }
}
