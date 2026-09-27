<?php

namespace App\Models;
use App\Models\TeamCar;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $slug
 * @property string $name
 * @property int|null $owner_id
 * @property string|null $logo_path
 * @property string|null $banner_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $banner_url
 * @property-read mixed $logo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\User|null $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Series> $series
 * @property-read int|null $series_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Stint> $stints
 * @property-read int|null $stints_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereBannerPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Team extends Model
{
    protected $fillable = [
    'name',
    'short_name',
    'slug',
    'created_by',
    'logo_path',
    'logo_dark',
    'logo_light',
    'banner_path',
    'banner_dark',
];
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function owner()
    {
        return $this->hasOne(User::class)
            ->where('driver_role','team_owner');
    }

    public function stints()
    {
        return $this->hasMany(Stint::class);
    }

    public function series()
    {
        return $this->hasMany(Series::class);
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path
            ? asset('storage/'.$this->logo_path)
            : null;
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner_path
            ? asset('storage/'.$this->banner_path)
            : null;
    }

    public function members()
    {
        return $this->hasMany(\App\Models\User::class, 'team_id');
    }

    public function teamCars()
    {
        return $this->hasMany(TeamCar::class);
    }




}
