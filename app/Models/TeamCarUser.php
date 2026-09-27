<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamCarUser extends Model
{
    protected $table = 'team_car_user';

    protected $fillable = [
        'team_car_id',
        'user_id',
        'series_id',
    ];

    public function teamCar()
    {
        return $this->belongsTo(TeamCar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }
}