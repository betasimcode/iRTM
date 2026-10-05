<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IracingSerie extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'iracing_series_id',
        'car_class_id',
        'logo_path',
        'background_img',
        'race_type',
        'start_type',
        'race_length',
        'iracing_class',
        'category',
        'discipline',
        'fuel_limit',
        'tank_capacity_override',
        'mandatory_pit',
        'refuel_allowed',
        'ir_url',
        'stats_url',
        'serie_info',

        // 🔥 NUEVOS
        'setup_type',
        'race_duration_minutes',
        'fast_repair',
        'drive_through_limit',
        'has_additional_penalties',
        'disqualification_limit',
        'network_quality_rule',
        'quali_scrutiny',
        'grid_by_class',
        'tire_rules',
        'quali_tires',
        'joker_laps',
        'team_rules',
        'week_start_day',
        'week_start_time',
        'race_interval_minutes',
        'registration_open_minutes',
    ];

    public function getClassLabelAttribute()
    {
        return match($this->iracing_class) {
            'R' => 'Rookie',
            'D' => 'Class D',
            'C' => 'Class C',
            'B' => 'Class B',
            'A' => 'Class A',
            'P' => 'Pro',
            default => null,
        };
    }

    public function getClassStylesAttribute()
    {
        return match($this->ir_class) {

            'R' => 'bg-red-500/10 border-red-500 text-red-400 shadow-[0_0_6px_rgba(239,68,68,0.4)]',

            'D' => 'bg-orange-500/10 border-orange-500 text-orange-400 shadow-[0_0_6px_rgba(249,115,22,0.4)]',

            'C' => 'bg-yellow-500/10 border-yellow-500 text-yellow-400 shadow-[0_0_6px_rgba(234,179,8,0.4)]',

            'B' => 'bg-green-500/10 border-green-500 text-green-400 shadow-[0_0_6px_rgba(34,197,94,0.4)]',

            'A' => 'bg-blue-500/10 border-blue-500 text-blue-400 shadow-[0_0_6px_rgba(59,130,246,0.4)]',

            'P' => 'bg-gray-200/10 border-gray-300 text-gray-200 shadow-[0_0_6px_rgba(229,231,235,0.4)]',

            default => 'bg-gray-500/10 border-gray-500 text-gray-300',
        };
    }

    public function userDivisions()
    {
        return $this->hasMany(
            UserSeriesDivision::class,
            'serie_id'
        );
    }

    public function cars()
    {
        return $this->belongsToMany(

            Car::class,

            'iracing_series_cars',

            'series_id',

            'car_id'
        );
    }

}
