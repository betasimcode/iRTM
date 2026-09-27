<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int|null $car_id
 * @property string|null $logo_path
 * @property string|null $image_path
 * @property string|null $category
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property numeric|null $fuel_capacity
 * @property numeric|null $fuel_consumption
 * @property string|null $tyre_type
 * @property int|null $power_hp
 * @property numeric|null $tank_capacity
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Stint> $fuelStints
 * @property-read int|null $fuel_stints_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereCarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereFuelCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereFuelConsumption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car wherePowerHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereTankCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereTyreType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Car whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Car extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'category',
        'iracing_setup_folder',
        'logo_path',
        'image_path',
        'weight_kg',
        'tank_capacity',
        'power_hp',
        'drive_type',
        'engine_position',
        'wheelbase_mm',
        'front_track_mm',
        'rear_track_mm',
        'aero_level',
        'mechanical_grip',
        'tyre_model',
        'iracing_car_id',
    ];

    /*
    |-------------------------------------------------
    | Relaciones
    |-------------------------------------------------
    */

    // Relación lógica por nombre (stints guardan texto del coche)
    public function fuelStints()
    {
        return $this->hasMany(Stint::class, 'car', 'name');
    }

    /*
    |-------------------------------------------------
    | ESTADÍSTICAS DE CARRERA
    |-------------------------------------------------
    */

    // Consumo medio en tandas largas
    public function averageConsumption()
    {
        return $this->fuelStints()
            ->whereIn('type',['long run','race sim'])
            ->avg('avg_fuel');
    }

    // Ritmo medio en tandas largas
    public function averageRacePace()
    {
        return $this->fuelStints()
            ->whereIn('type',['long run','race sim'])
            ->avg('avg_lap');
    }

    public function stintsAtTrack($track)
    {
    return $this->fuelStints()
    ->where('track',$track)
    ->whereIn('type',['long run','race sim']);
    }

    public function consumptionAtTrack($track)
    {
    return $this->stintsAtTrack($track)->avg('avg_fuel');
    }

    public function paceAtTrack($track)
    {
    return $this->stintsAtTrack($track)->avg('avg_lap');
    }

    public function maxLapsAtTrack($track)
    {
    $cons = $this->consumptionAtTrack($track);

    if(!$this->tank_capacity || !$cons || $cons<=0)
        return null;

    return floor($this->tank_capacity / $cons);

    }

    public static function fromIracing($iracingId)
    {
        return self::where('iracing_car_id', $iracingId)->firstOrFail();
    }

    // Vueltas máximas por depósito
    public function maxLapsPerTank()
    {
        if(!$this->tank_capacity)
            return null;

        $cons = $this->averageConsumption();

        if(!$cons || $cons <= 0)
            return null;

        return floor($this->tank_capacity / $cons);
    }

    public function teamSeries()
    {
        return $this->hasMany(TeamCarSerie::class);
    }

    public function series()
    {
        return $this->belongsToMany(
    
            IracingSerie::class,
    
            'iracing_series_cars',
    
            'car_id',
    
            'series_id'
        );
    }





}

