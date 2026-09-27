<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'car_id',
        'image_path',
        'livery_file',
        'number',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::creating(function ($teamCar) {
            if (!$teamCar->team_id || !$teamCar->car_id) {
                throw new \Exception('TeamCar requires team_id and car_id');
            }
        });
    }
    // 🔹 Team propietario
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function drivers()
    {
        return $this->belongsToMany(User::class, 'team_car_user')
            ->withPivot(['series_id','role']);
    }
    
    // 🔹 Modelo base (cars)
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    // 🔹 Series donde participa
    public function series()
    {
        return $this->hasMany(Series::class);
    }

    // 🔹 Entries (drivers en series)
    public function entries()
    {
        return $this->hasMany(SeriesEntry::class);
    }

    public function getDisplayImageAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        if ($this->car && $this->car->image_path) {
            return asset('storage/' . $this->car->image_path);
        }

        return asset('images/default-car.png'); // opcional fallback final
    }
    /*
    |--------------------------------------------------------------------------
    | HELPERS (MUY ÚTILES)
    |--------------------------------------------------------------------------
    */

    public function getDisplayNameAttribute()
    {
        return $this->car->name . 
               ($this->number ? ' #' . $this->number : '') .
               ($this->livery_file ? ' (' . $this->livery_file . ')' : '');
    }
}