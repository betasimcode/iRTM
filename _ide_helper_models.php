<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
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
 */
	class Car extends \Eloquent {}
}

namespace App\Models{
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
 */
	class Circuit extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $subsession_id
 * @property int|null $session_id
 * @property string|null $track
 * @property int|null $track_id
 * @property string|null $session_type
 * @property int|null $sof
 * @property string|null $started_at
 * @property string|null $ended_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $fastest_lap
 * @property string|null $fastest_driver
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Stint> $stints
 * @property-read int|null $stints_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereFastestDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereFastestLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereSessionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereSof($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereSubsessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereTrack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereTrackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IrSession whereUpdatedAt($value)
 */
	class IrSession extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $category
 * @property string|null $logo_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IracingSeries newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IracingSeries newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IracingSeries query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IracingSeries whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IracingSeries whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IracingSeries whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IracingSeries whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IracingSeries whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IracingSeries whereUpdatedAt($value)
 */
	class IracingSeries extends \Eloquent {}
}

namespace App\Models{
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
 */
	class LapSector extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $round_id
 * @property int $car_id
 * @property int $circuit_id
 * @property numeric $lap_time
 * @property numeric $fuel_used
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $tyre_choice
 * @property string|null $session_type
 * @property int|null $laps_done
 * @property string|null $scheduled_at
 * @property numeric|null $fuel_start
 * @property numeric|null $fuel_end
 * @property numeric|null $track_temp
 * @property numeric|null $air_temp
 * @property string|null $weather
 * @property int|null $incidents
 * @property string|null $notes
 * @property-read \App\Models\Car $car
 * @property-read \App\Models\Circuit $circuit
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereAirTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereCarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereCircuitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereFuelEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereFuelStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereFuelUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereIncidents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereLapTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereLapsDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereSessionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereTrackTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereTyreChoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaceSession whereWeather($value)
 */
	class RaceSession extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int|null $car_id
 * @property int|null $season_year
 * @property int|null $season_number
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $iracing_series_id
 * @property int|null $team_id
 * @property-read \App\Models\Car|null $car
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SeriesEntry> $entries
 * @property-read int|null $entries_count
 * @property-read mixed $active_round
 * @property-read mixed $full_name
 * @property-read mixed $has_session
 * @property-read mixed $is_current_week
 * @property-read mixed $is_past
 * @property-read mixed $progress
 * @property-read mixed $season_label
 * @property-read mixed $season_status
 * @property-read mixed $status_label
 * @property-read \App\Models\IracingSeries|null $iracingSeries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SeriesRound> $rounds
 * @property-read int|null $rounds_count
 * @property-read \App\Models\Team|null $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereCarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereIracingSeriesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereSeasonNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereSeasonYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereUpdatedAt($value)
 */
	class Series extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $series_id
 * @property int $user_id
 * @property int $car_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Car $car
 * @property-read \App\Models\Series $series
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesEntry whereCarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesEntry whereSeriesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesEntry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesEntry whereUserId($value)
 */
	class SeriesEntry extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $series_id
 * @property int $week
 * @property \Illuminate\Support\Carbon|null $week_start
 * @property \Illuminate\Support\Carbon|null $week_end
 * @property int $circuit_id
 * @property string $race_type
 * @property int $race_length
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Circuit $circuit
 * @property-read mixed $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RaceSession> $raceSessions
 * @property-read int|null $race_sessions_count
 * @property-read \App\Models\Series $series
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereCircuitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereRaceLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereRaceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereSeriesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereWeekEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeriesRound whereWeekStart($value)
 */
	class SeriesRound extends \Eloquent {}
}

namespace App\Models{
/**
 * @property \App\Models\User $user
 * @property int $id
 * @property int $user_id
 * @property \App\Models\Car|null $car
 * @property string|null $track
 * @property string|null $session_type
 * @property int $laps
 * @property float|null $best_lap
 * @property float|null $avg_lap
 * @property float|null $avg_fuel
 * @property string|null $tyre_compound
 * @property string $started_at
 * @property string $ended_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $type
 * @property int|null $duration_seconds
 * @property int|null $driver_id
 * @property int|null $team_id
 * @property int|null $car_id
 * @property int|null $circuit_id
 * @property numeric|null $consistency
 * @property numeric|null $degradation
 * @property numeric|null $fuel_variance
 * @property int|null $track_evolution
 * @property float|null $avg_track_temp
 * @property float|null $avg_air_temp
 * @property float|null $avg_humidity
 * @property float|null $avg_wind_speed
 * @property float|null $avg_wind_dir
 * @property int|null $sky_mode
 * @property int|null $track_state_mode
 * @property int|null $ir_session_id
 * @property string|null $server_time_in
 * @property string|null $server_time_out
 * @property float|null $session_fastest_lap
 * @property string|null $session_fastest_driver
 * @property-read \App\Models\Circuit|null $circuit
 * @property-read \App\Models\IrSession|null $irSession
 * @property-read \App\Models\RaceSession|null $raceSession
 * @property-read \App\Models\Team|null $team
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Telemetry> $telemetries
 * @property-read int|null $telemetries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TyreSnapshot> $tyreSnapshots
 * @property-read int|null $tyre_snapshots_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereAvgAirTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereAvgFuel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereAvgHumidity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereAvgLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereAvgTrackTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereAvgWindDir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereAvgWindSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereBestLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereCar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereCarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereCircuitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereConsistency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereDegradation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereDriverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereDurationSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereFuelVariance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereIrSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereLaps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereServerTimeIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereServerTimeOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereSessionFastestDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereSessionFastestLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereSessionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereSkyMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereTrack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereTrackEvolution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereTrackStateMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereTyreCompound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stint whereUserId($value)
 */
	class Stint extends \Eloquent {}
}

namespace App\Models{
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
 */
	class Team extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $stint_id
 * @property int $lap
 * @property bool $is_pit_lap
 * @property numeric $lap_time
 * @property numeric $fuel
 * @property numeric|null $fuel_used
 * @property numeric|null $length_km
 * @property numeric $track_temp
 * @property numeric $air_temp
 * @property string $timestamp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float|null $humidity
 * @property float|null $wind_speed
 * @property float|null $wind_dir
 * @property string|null $sky
 * @property string|null $track_state
 * @property int|null $car_id
 * @property int|null $track_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LapSector> $sectors
 * @property-read int|null $sectors_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereAirTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereCarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereFuel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereFuelUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereHumidity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereIsPitLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereLapTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereLengthKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereSky($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereStintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereTrackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereTrackState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereTrackTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereWindDir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Telemetry whereWindSpeed($value)
 */
	class Telemetry extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $track_id
 * @property int $sector_number
 * @property numeric $start_pct
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Circuit|null $circuit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TrackSector> $sectors
 * @property-read int|null $sectors_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackSector newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackSector newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackSector query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackSector whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackSector whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackSector whereSectorNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackSector whereStartPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackSector whereTrackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrackSector whereUpdatedAt($value)
 */
	class TrackSector extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $stint_id
 * @property int|null $lap_number
 * @property string|null $tyre_compound
 * @property float|null $wear_fl
 * @property float|null $wear_fr
 * @property float|null $wear_rl
 * @property float|null $wear_rr
 * @property float|null $degradation_per_lap
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $tyre_set_number
 * @property-read \App\Models\Stint $stint
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereDegradationPerLap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereLapNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereStintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereTyreCompound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereTyreSetNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereWearFl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereWearFr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereWearRl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TyreSnapshot whereWearRr($value)
 */
	class TyreSnapshot extends \Eloquent {}
}

namespace App\Models{
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
 */
	class User extends \Eloquent {}
}

