<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamCarSerie extends Model
{
    protected $fillable = [
        'team_id',
        'series_id',
        'car_id',
        'season',
        'livery_file',
        'team_car_image',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function getLiveryUrlAttribute()
    {
        return $this->livery_file
            ? asset('storage/'.$this->livery_file)
            : null;
    }

    public function getImageUrlAttribute()
    {
        return $this->team_car_image
            ? asset('storage/'.$this->team_car_image)
            : asset('images/default-car.png'); // fallback
    }



}
