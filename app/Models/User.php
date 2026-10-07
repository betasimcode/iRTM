<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Lap;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $iracing_name
 * @property string|null $iracing_helmet_path
 * @property int|null $iracing_user_id
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property string $driver_role
 * @property int|null $team_id
 * @property string|null $last_logger_ping
 * @property string|null $logger_version
 * @property string $current_status
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $api_token
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SeriesEntry> $seriesEntries
 * @property-read int|null $series_entries_count
 * @property-read \App\Models\Team|null $team
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereApiToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDriverRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIracingHelmetPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIracingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIracingUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoggerPing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLoggerVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'iracing_helmet_path',
        'iracing_name',
        'iracing_user_id',
        'team_id',
        'role',
        'driver_role',
        'current_status',
        'last_logger_ping',
        'timezone',
        'api_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->api_token = Str::random(60);
        });
    }

    public function seriesEntries()
    {
        return $this->hasMany(SeriesEntry::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeamDirector()
{
    return $this->driver && in_array($this->driver->role, [
        'team_owner',
        'team_director'
    ]);
}

    public function isTeamOwner()
    {
        return $this->driver && $this->driver->role === 'team_owner';
    }

    public function isDriver()
    {
        return $this->driver && $this->driver->role === 'driver';
    }

    public function teamCars()
    {
        return $this->belongsToMany(TeamCar::class, 'team_car_user')
            ->withPivot(['series_id','role']);
    }

    // public function lap()
    // {
    //     return $this->hasMany(Lap::class);
    // }
    public function seriesDivisions()
    {
        return $this->hasMany(
            UserSeriesDivision::class
        );
    }

    public function StintFiles()
    {
        return $this->hasMany(
            StintFile::class
        );
    }




}
