<?php

namespace App\Models;
use Illuminate\Support\Collection;
use App\Models\Car;
use App\Models\TeamCar;
use App\Models\SeriesRound;
use App\Models\IracingSerie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

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
 * @property-read Car|null $car
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
 * @property-read \App\Models\IracingSerie|null $iracingSeries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SeriesRound> $rounds
 * @property-read int|null $rounds_count
 * @property-read \App\Models\Team|null $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereCarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereIracingSerieId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereSeasonNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereSeasonYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Series whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Series extends Model
{
    protected $fillable = [
        'name',
        'car_id',
        'team_car_id',
        'season_year',
        'season_number',
        'start_date',
        'end_date',
        'iracing_series_id',
        'iracing_season_id',
        'status',
        'team_id'

        ];


    protected $casts = [

       'iracing_season_id' => 'integer',

    ];

    public function rounds()
    {
        return $this->hasMany(
            SeriesRound::class,
            'series_id'
        )->orderBy('week');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function teamCar()
    {
        return $this->belongsTo(TeamCar::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function iracingSerie()
    {
        return $this->belongsTo(
            IracingSerie::class,
            'iracing_series_id'
        );
    }




////////////////////////////////////////////////////////////////////////////////
    public function getDisplayCarAttribute()
    {
        return $this->teamCar ?? $this->car;
    }

    public function getDisplayImageAttribute()
    {
        if ($this->teamCar && $this->teamCar->image_path) {
            return asset('storage/'.$this->teamCar->image_path);
        }

        return asset('storage/'.$this->car->image_path);
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} {$this->season_year}-S{$this->season_number}";
    }

////////////////////////////////////////////////////////////////////////////////
    public function activeRound()
    {
        if ($this->rounds->isEmpty()) {
            return null;
        }

        $now = now();

        // 1️⃣ Semana en disputa
        $current = $this->rounds->first(function ($round) use ($now) {
            return $round->week_start &&
                $round->week_end &&
                $now->between(
                    \Carbon\Carbon::parse($round->week_start),
                    \Carbon\Carbon::parse($round->week_end)
                );
        });

        if ($current) {
            return $current;
        }

        // 2️⃣ Próxima semana futura
        $upcoming = $this->rounds
            ->filter(fn($r) => $r->week_start && \Carbon\Carbon::parse($r->week_start)->isFuture())
            ->sortBy('week_start')
            ->first();

        if ($upcoming) {
            return $upcoming;
        }

        // 3️⃣ Última semana disputada
        return $this->rounds
            ->filter(fn($r) => $r->week_end)
            ->sortByDesc('week_end')
            ->first();
    }


    /**
     * Ronda actualmente en disputa.
     *
     * Devuelve únicamente una ronda cuyo periodo
     * contiene la fecha/hora actual.
     */
    public function currentRound()
    {
        return $this->rounds
            ->first(fn ($round) => $round->status === 'active');
    }

    /**
     * Última ronda de la temporada.
     *
     * Se utiliza para consultas históricas cuando
     * la temporada ya ha terminado.
     */

    /**
     * Ronda que debe utilizarse como referencia visual de la temporada.
     *
     * Prioridad:
     * 1. Ronda actualmente activa.
     * 2. Próxima ronda futura.
     * 3. Última ronda disputada.
     */
    public function displayRound()
    {
        $this->loadMissing('rounds');

        // 1. Ronda actualmente activa.
        $currentRound = $this->rounds
            ->first(fn ($round) => $round->status === 'active');

        if ($currentRound) {
            return $currentRound;
        }

        // 2. Próxima ronda futura.
        $nextRound = $this->rounds
            ->filter(fn ($round) => $round->status === 'future')
            ->sortBy('week_start')
            ->first();

        if ($nextRound) {
            return $nextRound;
        }

        // 3. Última ronda disputada.
        return $this->rounds
            ->filter(fn ($round) => $round->status === 'past')
            ->sortByDesc('week_end')
            ->first();
    }



    public function lastRound()
    {
        return $this->rounds
            ->filter(fn ($round) => $round->week_end)
            ->sortByDesc('week_end')
            ->first();
    }


/**
 * Sincroniza las fechas globales de la temporada
 * a partir de sus rondas.
 *
 * start_date = primera week_start
 * end_date   = última week_end
 */
    public function syncCalendarDates(): void
    {
        $this->loadMissing('rounds');

        $rounds = $this->rounds
            ->filter(fn ($round) =>
                $round->week_start &&
                $round->week_end
            );

        if ($rounds->isEmpty()) {
            $this->start_date = null;
            $this->end_date = null;

            return;
        }

        $this->start_date = $rounds
            ->min('week_start');

        $this->end_date = $rounds
            ->max('week_end');

        $this->save();
    }


////////////////////////////////////////////////////////////////////////////////
    public function getTeamCarForTeam($teamId = null)
    {
        $teamId = $teamId ?? auth()->user()->team_id;

        return $this->teamCars
            ->where('team_id', $teamId)
            ->first();
    }
    ////////////////////////////////////////////////////////////////////////////////
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'active'   => 'Activa',
            'archived' => 'Finalizada',
            default    => 'Borrador'
        };
    }
////////////////////////////////////////////////////////////////////////////////
    public function entries()
    {
        return $this->hasMany(SeriesEntry::class);
    }
////////////////////////////////////////////////////////////////////////////////
    public function getActiveRoundAttribute()
    {
        return $this->rounds
            ->firstWhere('status', 'active');
    }

////////////////////////////////////////////////////////////////////////////////
    public function getProgressAttribute()
    {
        $total = $this->rounds->count();

        if ($total === 0) return 0;

        $completed = $this->rounds
            ->filter(fn($r) => $r->status === 'past')
            ->count();

        return round(($completed / $total) * 100);
    }

////////////////////////////////////////////////////////////////////////////////
    public function getSeasonStatusAttribute()
    {
        if ($this->rounds->contains(fn($r) => $r->status === 'active')) {
            return 'active';
        }

        if ($this->rounds->contains(fn($r) => $r->status === 'future')) {
            return 'upcoming';
        }

        return 'finished';
    }

////////////////////////////////////////////////////////////////////////////////
    public function getHasSessionAttribute()
    {
        return $this->raceSessions->isNotEmpty();
    }

////////////////////////////////////////////////////////////////////////////////
    public function getIsPastAttribute()
    {
        return $this->week_end
            ? Carbon::parse($this->week_end)->isPast()
            : false;
    }

////////////////////////////////////////////////////////////////////////////////
    public function getIsCurrentWeekAttribute()
    {
        return $this->week_start && $this->week_end
            ? now()->between(
                Carbon::parse($this->week_start),
                Carbon::parse($this->week_end)
            )
            : false;
    }

//////////////////////////////-NUCLEO-////////////////////////////////////////
    public function strategicStats($userId, $mode = 'season')
{

    $round = $this->activeRound();
    if (!$round) return [];

    $car = $this->car;
    if (!$car) return [];
    // 🔥 QUERY BASE
    $query = Stint::with('laps')
        ->where('car_id', $car->iracing_car_id)
        ->where('track_id', $round->track->iracing_track_id)
        ->where('user_id', $userId);

    // 🔹 FILTROS
    if ($mode === 'week') {
        $query->whereBetween('created_at', [
            $round->week_start,
            $round->week_end
        ]);
    }

    if ($mode === 'season') {
        $seasonStart = $this->rounds->min('week_start');
        $query->where('created_at', '>=', $seasonStart);
    }

    $stints = $query->get();

    if ($stints->isEmpty()) {
        return [
            'total_stints' => 0,
            'best_lap' => null,
            'representative_pace' => null,
            'avg_fuel' => null,
            'long_run_best' => null,
            'alerts' => [],
        ];
    }

    // =========================
    // 🟢 TOTAL STINTS
    // =========================
    $totalStints = $stints->count();

    // =========================
    // 🟣 BEST LAP (GLOBAL)
    // =========================
    $bestLap = $stints
        ->flatMap->laps
        ->where('lap_time', '>', 0)
        ->min('lap_time');

// =========================
// 🔵 REPRESENTATIVE PACE (con 107%)
// =========================
$paceSamples = $stints->map(function ($stint) {

    if (!$stint->laps || $stint->laps->isEmpty()) {
        return null;
    }

    $laps = $stint->laps
        ->pluck('lap_time')
        ->filter(function($l) {
            return $l > 0 && $l < 200;
        })
        ->values();

    // 🔴 excluir stints cortos
    if ($laps->count() < 3) {
        return null;
    }

    $bestLap = $laps->min();
    $threshold = $bestLap * 1.07;

    $filtered = $laps->filter(function($l) use ($threshold) {
        return $l <= $threshold;
    })->values();

    // 🔴 si el stint queda inconsistente → fuera
    if ($filtered->count() < 3) {
        return null;
    }

    $sorted = $filtered->sort()->values();
    $topCount = max(3, ceil($sorted->count() * 0.3));
    $topLaps = $sorted->take($topCount);

    return $topLaps->avg();

})->filter();


// ✅ AQUÍ (después de crearlo)
$representativePace = $paceSamples->isNotEmpty()
    ? $paceSamples->avg()
    : null;

    // =========================
    // 🟢 CONSUMO MEDIO
    // =========================
    $avgFuel = $stints
    ->flatMap->laps
    ->where('fuel_consumed', '>', 0)
    ->avg('fuel_consumed');
    // =========================
    // 🟠 LONG RUN (mejor stint largo)
    // =========================
    $longRunBest = $stints
        ->map(fn($s) => $s->laps->count())
        ->max();

    // =========================
    // 🔴 ALERTAS BÁSICAS
    // =========================
    $alerts = [];

    if ($totalStints < 1) {
        $alerts[] = [
            'type' => 'warning',
            'message' => 'Pocos stints registrados para análisis fiable'
        ];
    }

    if ($avgFuel && $avgFuel > 4) {
        $alerts[] = [
            'type' => 'warning',
            'message' => 'Consumo elevado detectado'
        ];
    }

    return [
        'total_stints' => $totalStints,
        'best_lap' => $bestLap,
        'representative_pace' => $representativePace,
        'avg_fuel' => $avgFuel,
        'long_run_best' => $longRunBest,
        'alerts' => $alerts,
    ];
}

public function racePlan($userId)
{
    $car = $this->car;
    $seriesConfig = $this->iracingSeries;

    $config = session('strategy_config', []);

    // Prioridad: usuario > serie > default
    $mode = $config['mode'] ?? 'auto';
    $forcedStops = $config['forced_stops'] ?? null;

    $tankLimit = $config['tank_limit'] ?? null;

    $mandatoryPit = $config['mandatory_pit']
        ?? ($seriesConfig->mandatory_pit ?? false);

    $refuelAllowed = $config['refuel_allowed']
        ?? ($seriesConfig->refuel_allowed ?? true);

    $round = $this->activeRound();
    $teamId = auth()->user()->team_id;



    if (!$round || !$car) return null;

    $stats = $this->strategicStats($userId, 'season');

    if (!$stats || is_null($stats['representative_pace'])) {
    return null;
}
    $consumption = $stats['avg_fuel'];
    $pace = $stats['representative_pace'];

    // =======================
    // 🔹 CALCULO DE VUELTAS
    // =======================

    if ($round->race_type === 'time') {
        $raceSeconds = $round->race_length * 60;
        $laps = ceil($raceSeconds / $pace);
        $config = session('strategy_config', []);

        $marginLaps = $config['margin_laps'] ?? 0;

        // 🔥 aplicar margen
        $laps += $marginLaps;

    } else {
        $laps = $round->race_length;
    }

    // =======================
    // 🔹 AJUSTE REAL DE CARRERA
    // =======================

    $extraLaps = 0;

    if ($round->race_type === 'time') {

        // +1 vuelta por cruce en tiempo límite
        $extraLaps += 1;

        // +1 si salida lanzada (formación)
        if ($round->start_type === 'rolling') {
            $extraLaps += 1;
        }
    }

    // Vueltas finales reales por escenario
    $baseLaps = $laps + $extraLaps;
    $conservativeLaps = $baseLaps;
    $safetyLaps = $baseLaps;
    $aggressiveLaps = $baseLaps; // clave: sin margen pero con vueltas reales


    // =======================
    // 🔹 TANQUE REALISTA
    // =======================

    $seriesConfig = $this->iracingSeries;

    $baseTank = $car->tank_capacity;

    // 1️⃣ Override manual (máxima prioridad)
    if (!empty($tankLimit)) {

        $tank = $tankLimit;

    // 2️⃣ Fuel limit de la serie (IMSA etc)
    } elseif (!empty($seriesConfig->fuel_limit)) {

        $tank = $baseTank * ($seriesConfig->fuel_limit / 100);

    // 3️⃣ Default coche
    } else {

        $tank = $baseTank;
    }
    // =======================
    // 🔹 COMBUSTIBLE
    // =======================

    // Margen SOLO para base/conservador/safety
    if ($laps < 20) {
        $margin = 1.02;
    } elseif ($laps <= 40) {
        $margin = 1.04;
    } else {
        $margin = 1.05;
    }

    // 🔵 BASE
    $fuelBase = $baseLaps * $consumption * $margin;
    $extraFuel = $config['extra_fuel'] ?? 0;

    // 🔥 añadir litros extra
    $fuelBase += $extraFuel;

    // 🟢 CONSERVADOR
    $fuelConservative = $conservativeLaps * $consumption * ($margin * 1.03);

    // 🔴 AGRESIVO (SIN margen)
    $fuelAggressive = $aggressiveLaps * $consumption;

    // 🟡 SAFETY
    $fuelSafety = $safetyLaps * $consumption * ($margin * 1.02);

    // =======================
    // 🔹 BUFFER Y RIESGO
    // =======================

    $bufferLiters = $fuelBase - ($baseLaps * $consumption);

    $riskLevel = match(true) {
        $bufferLiters < 0.5 => 'Alto',
        $bufferLiters < 1.0 => 'Medio',
        default => 'Bajo',
    };

    // =======================
    // 🔧 CALCULO HIBRIDO
    // =======================

    $baseStints = ceil($fuelBase / $tank);
    $conservativeStints = ceil($fuelConservative / $tank);
    $aggressiveStints = ceil($fuelAggressive / $tank);
    $safetyStints = ceil($fuelSafety / $tank);

    if ($mode === 'manual') {

        if ($forcedStops !== null) {
            $manualStints = $forcedStops + 1;

            $baseStints = $manualStints;
            $conservativeStints = $manualStints;
            $aggressiveStints = $manualStints;
            $safetyStints = $manualStints;
        }

        if ($mandatoryPit && $baseStints < 2) {
            $baseStints = 2;
        }

        if (!$refuelAllowed) {
            $baseStints = 1;
        }
    }

    // Validación
    $fuelPerStint = $fuelBase / $baseStints;

    if ($fuelPerStint > $tank) {
        $baseStints = ceil($fuelBase / $tank);
    }

    return [
        'estimated_laps' => $laps,
        'real_laps'      => $baseLaps, // 🔥 NUEVO (muy útil para debug/UI)

        'tank_capacity'  => $tank,
        'margin_percent' => round(($margin - 1) * 100, 1),
        'buffer'         => round($bufferLiters, 2),
        'risk'           => $riskLevel,

        'base' => [
            'fuel' => round($fuelBase, 2),
            'stints' => $baseStints,
        ],

        'conservative' => [
            'fuel' => round($fuelConservative, 2),
            'stints' => $conservativeStints,
        ],

        'aggressive' => [
            'fuel' => round($fuelAggressive, 2),
            'stints' => $aggressiveStints,
        ],

        'safety_car' => [
            'fuel' => round($fuelSafety, 2),
            'stints' => $safetyStints,
        ],
    ];
}

    public function iracingSeries()
    {
        return $this->belongsTo(IracingSerie::class);
    }

    public function getDisplayConfig()
    {
        if ($this->iracingSeries) {
            return [
                'type' => $this->iracingSeries->race_type,
                'length' => $this->iracingSeries->race_length,
                'start' => $this->iracingSeries->start_type,
                'class' => $this->iracingSeries->ir_class,
            ];
        }

        // fallback (series custom)
        $firstRound = $this->rounds()->first();

        return [
            'type' => $firstRound?->race_type,
            'length' => $firstRound?->race_length,
            'start' => $firstRound?->start_type,
            'class' => null,
        ];
    }

    public function estimatedDistance()
    {
        $config = $this->getDisplayConfig();

        if ($config['type'] !== 'laps') return null;

        $round = $this->rounds->first();

        if (!$round || !$round->circuit) return null;

        return round($config['length'] * $round->circuit->length_km, 1);
    }

    public function teamCars()
    {
        return $this->hasMany(TeamCarSerie::class);
    }

    public function generateTeamEvents(
        int $teamId,
        Carbon $from,
        Carbon $to
    ): Collection {

        $events = collect();

        $seriesList = Series::query()

            ->with(
                'iracingSeries'
            )

            ->where(
                'team_id',
                $teamId
            )

            ->get();

        foreach ($seriesList as $series) {

            if (! $series->iracingSeries) {

                continue;
            }

            $raceEvents = $this->generateEvents(

                $from,

                $to,

                $series
                    ->iracingSeries
                    ->week_start_day,

                $series
                    ->iracingSeries
                    ->week_start_time,

                $series
                    ->iracingSeries
                    ->race_interval_minutes
            );

            foreach ($raceEvents as $eventTime) {

                $events->push([

                    'series_id' => $series->id,

                    'series_name' => $series->name,

                    'time' => $eventTime
                ]);
            }
        }

        return $events

            ->sortBy(
                'time'
            )

            ->values();
    }

    public function standingConfigs(): HasMany
    {
        return $this->hasMany(
            SeriesStandingConfig::class,
            'series_id'
        );
    }

    public function standings(): HasMany
    {
        return $this->hasMany(
            SeriesStanding::class,
            'series_id'
        );
    }






}
