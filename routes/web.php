<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\RaceSessionController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CircuitController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TeamController;
use App\Services\IracingService;
use App\Http\Controllers\SetupSheetController;
use App\Http\Controllers\SeasonParserController;
use App\Http\Controllers\TelemetryController;
use App\Http\Controllers\TelemetryLabController;
use App\Http\Controllers\IracingSyncController;
use App\Http\Controllers\StintController;
use App\Http\Controllers\SessionStintController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SessionRecoveryController;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeamCenter\TeamCenterController;
use App\Http\Controllers\TeamCenter\CompetitionToolController;
use App\Http\Controllers\TeamCenter\ChampionshipController;
use App\Http\Controllers\TeamCarController;
use App\Http\Controllers\TeamCarDriverController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeamCompetitionController;
use App\Http\Controllers\Admin\IracingSerieController;
use App\Http\Controllers\StintAnalisisController;
use App\Http\Controllers\TeamWorkspaceController;
use App\Http\Controllers\workspace\WorkspaceController;
use App\Http\Controllers\SetupItemDefinitionController;
use App\Http\Controllers\SeasonPdfController;
use App\Http\Controllers\LiveTimingController;
use App\Http\Controllers\RaceScheduleController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\SeasonImportController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\CompetitionStrategyController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;


Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => [
        'localeSessionRedirect',
        'localizationRedirect',
        'localeViewPath'
    ]
], function () {

    Route::get('/', function () {
        return view('landing');
    });

    Route::middleware('auth')
        ->get('/drivers', function () {
            return view('drivers.index');
        })->name('drivers.index');

    Route::get('/teams', function () {
        return view('teams.index');
    })
    ->middleware('auth')
    ->name('teams.index');

    Route::middleware('auth')->group(function () {

        Route::get('/telemetry/lab', [TelemetryLabController::class, 'index'])
            ->name('telemetry.lab');


        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::post('/user/theme', [UserController::class, 'updateTheme'])
            ->name('user.theme');
        // FUEL
        Route::get('/fuel-calculator', [FuelController::class,'index'])->name('fuel.index');
        Route::post('/fuel-calculator', [FuelController::class,'calculate']);

        Route::post('/series/{series}/choose-car',
            [SeriesController::class,'chooseCar'])
            ->name('series.chooseCar');

        // SERIES / DATA
        Route::resource('race_sessions', RaceSessionController::class)->middleware('auth');
        Route::resource('cars', CarController::class);

        Route::post('/circuits/export-bs90', [CircuitController::class, 'exportBs90'])
            ->name('circuits.export-bs90');
        Route::resource('circuits', CircuitController::class);

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

        Route::get('/api/consumption/{car}/{circuit}', [RaceSessionController::class,'averageConsumption']);

        // STINTS
        Route::get('/stints', [StintController::class, 'index'])->name('stints.index');
        Route::get('/stints/{stint}', [StintController::class, 'show'])->name('stints.show');
        Route::get('/stints/{stint}/analysis', [StintController::class, 'showAnalysis'])
            ->name('stints.analysis');

        Route::resource('setups', \App\Http\Controllers\SetupController::class);
        Route::get('/setups/{setup}/download', [SetupController::class, 'download']
        )->name('setups.download');

        Route::post('/setups/{setup}/install', [SetupController::class, 'install']
        )->name('setups.install');

        Route::post('/setups/{setup}/repair-file', [SetupController::class, 'repairFile']
        )->name('setups.repair-file');

        Route::patch('/setups/{setup}/type', [SetupController::class, 'updateType']
        )->name('setups.type');

        // SESSIONS
        Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
        Route::get('/sessions/{id}', [SessionController::class, 'show'])->name('sessions.show');
        Route::post('/sessions/{session}/rebuild', [SessionRecoveryController::class, 'rebuild'])->name('sessions.rebuild');
        Route::post('/stints/{stint}/repair-setup', [StintController::class, 'repairSetup'])->name('stints.repair-setup');



        // COMPETITIONS
        Route::get('/competitions', [CompetitionController::class, 'index'])
        ->name('competitions.index');

        Route::get('/competitions/create', [CompetitionController::class, 'create'])
        ->name('competitions.create');

        Route::get('/competitions/{series}/register', [CompetitionController::class, 'register'])
        ->name('competitions.register');

        Route::post('/competitions/{series}/register', [CompetitionController::class, 'store'])
        ->name('competitions.register.store');

        Route::get('/competitions/{series}', [CompetitionController::class, 'show'])
        ->name('competitions.show');

        Route::post('/competitions/{series}/cancel', [CompetitionController::class, 'cancel'])
        ->name('competitions.cancel');

        Route::get('/competitions/{series}/strategy', [CompetitionStrategyController::class, 'show'])
        ->name('competitions.strategy');

        Route::get('/competitions/{series}/stints', [CompetitionController::class, 'stints'])
        ->name('competitions.stints');

        Route::get('/competitions/{series}/sessions', [CompetitionController::class, 'sessions'])
        ->name('competitions.sessions');

        // SERIES
        Route::get('/series', [SeriesController::class, 'index'])->name('series.index');

        Route::resource('series', SeriesController::class)->except(['show']);

        Route::get('/series/{series}', [SeriesController::class, 'show'])->name('series.show');
        Route::get('/series/{series}/manage', [SeriesController::class, 'manage'])->name('series.manage');
        Route::get('/series/{series}/plan', [SeriesController::class, 'plan'])->name('series.plan');
        Route::post('/series/{series}/strategy', [SeriesController::class, 'plan_update'])
        ->name('strategy.update');
        Route::post('/strategy/{series}/reset', [SeriesController::class, 'resetPlan'])
        ->name('strategy.reset');

        Route::post('/strategy/{series}/favorite', [SeriesController::class, 'favorite'])
        ->name('strategy.favorite');

        Route::prefix('series/{series}')->group(function () {

            Route::get('/stints', [SeriesController::class, 'stints'])
                ->name('series.stints');
        });

        Route::get(
            '/series/{series}/stints/{stint}',
            [StintController::class, 'showFromSeries']
        )->name('series.stints.show');

        Route::get('/test-iracing-series', function (IracingService $service) {
            return $service->getSeries();
        });

        /// ROUNDS
        Route::post('/series/{series}/rounds', [SeriesController::class, 'storeRound'])
            ->name('series.rounds.store');
        Route::put('/rounds/{round}', [SeriesController::class, 'updateRound'])
            ->name('series.rounds.update');

        Route::delete('/rounds/{round}', [SeriesController::class, 'destroyRound'])
            ->name('series.rounds.destroy');





        // ADMIN LOGGER
        Route::middleware(['auth','admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () {

            // LOGGER
            Route::get('/logger-status', [AdminController::class, 'loggerStatus'])
                ->name('logger-status');

            // USERS
            Route::resource('users', UserController::class);

            // SERIES
            Route::resource('iracing-series', IracingSerieController::class);

            // 🔥 SETUP ITEMS
            Route::get('/setup-items', [SetupItemDefinitionController::class, 'index'])
            ->name('setup-items.index');

            Route::get('/setup-items/{id}/edit', [SetupItemDefinitionController::class, 'edit'])
                ->name('setup-items.edit');

            Route::put('/setup-items/{id}', [SetupItemDefinitionController::class, 'update'])
                ->name('setup-items.update');

            Route::post('/setup-items/sync', [SetupItemDefinitionController::class, 'sync'])
                ->name('setup-items.sync');

                });

        });

        Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

            Route::get('/season-imports', [SeasonImportController::class, 'index'])
                ->name('season-imports.index');

            Route::get('/season-imports/create', [SeasonImportController::class, 'create'])
                ->name('season-imports.create');

            Route::post('/season-imports', [SeasonImportController::class, 'store'])
                ->name('season-imports.store');

            Route::get('/season-pdfs', [SeasonPdfController::class, 'index'])
            ->name('season-pdfs.index');

            Route::get('/season-pdfs/create', [SeasonPdfController::class, 'create'])
            ->name('season-pdfs.create');

            Route::post('/season-pdfs', [SeasonPdfController::class, 'store'])
            ->name('season-pdfs.store');

            Route::get('/season-parser', [SeasonParserController::class, 'create'])
                ->name('season-parser.create');

            Route::post('/season-parser/run', [SeasonParserController::class, 'run'])
                ->name('season-parser.run');



        });

    // TEAMS
    Route::middleware(['auth'])->group(function () {

        Route::get('/teams', [TeamController::class,'index'])->name('teams.index');
        Route::get('/teams/create', [TeamController::class,'create'])->name('teams.create');
        Route::post('/teams', [TeamController::class,'store'])->name('teams.store');

        Route::get('/teams/{team}/edit', [TeamController::class,'edit'])->name('teams.edit');
        Route::put('/teams/{team}', [TeamController::class,'update'])->name('teams.update');

        Route::delete('/teams/{team}', [TeamController::class,'destroy'])->name('teams.destroy');

        Route::get('/teams/{team}/members', [TeamController::class,'members'])
            ->name('teams.members');
    });

    Route::middleware(['auth'])->prefix('team')->group(function () {

        Route::get('/cars', [TeamCarController::class, 'index'])->name('team.cars.index');
        Route::post('/cars', [TeamCarController::class, 'store'])->name('team.cars.store');
        Route::put('/cars/{teamCar}', [TeamCarController::class, 'update'])->name('team.cars.update');
        Route::delete('/cars/{teamCar}', [TeamCarController::class, 'destroy'])->name('team.cars.destroy');

    });


        Route::get('/setups/{setup}/sheet', [SetupSheetController::class, 'show'])
            ->name('setups.sheet');
        Route::get('/setups/{setup}/sheet/pdf', [SetupSheetController::class, 'pdf'])
            ->name('setups.sheet.pdf');


// TEAMCENTER

    Route::middleware(['auth', 'team'])->prefix('teamcenter')->group(function () {

        Route::get('/', [TeamCenterController::class, 'dashboard',])
            ->name('teamcenter.dashboard');

        Route::get('/championships', [ChampionshipController::class, 'index',])
            ->name('teamcenter.championships.index');

        Route::get('/championships/{series}', [ChampionshipController::class, 'show',])
            ->name('teamcenter.championships.show');

        Route::get('/championships/{series}/standings', [ChampionshipController::class, 'standings'])
            ->name('teamcenter.championships.standings');

        Route::post('/championships/{series}/standings', [ChampionshipController::class, 'updateStandings'])
            ->name('teamcenter.championships.standings.update');

        Route::get('/championships/{series}/stints', [CompetitionToolController::class, 'stints'])
            ->name('teamcenter.championships.stints');

        Route::get('/championships/{series}/sessions', [CompetitionToolController::class, 'sessions',])
            ->name('teamcenter.championships.sessions');


        // STRATEGY

        Route::get('/championships/{series}/strategy', [CompetitionToolController::class, 'strategy'])
            ->name('teamcenter.championships.strategy');

        Route::post('/championships/{series}/strategy', [CompetitionToolController::class, 'strategyUpdate'])
            ->name('teamcenter.championships.strategy.update');

        Route::post('/championships/{series}/strategy/reset', [CompetitionToolController::class, 'strategyReset'])
            ->name('teamcenter.championships.strategy.reset');

        Route::post('/championships/{series}/strategy/favorite', [CompetitionToolController::class, 'strategyFavorite'])
            ->name('teamcenter.championships.strategy.favorite');

    });








    // TEAM WORKSPACE
    Route::middleware(['auth'])->group(function () {

        Route::get('/team', [TeamWorkspaceController::class,'dashboard'])
            ->name('team.dashboard');

        Route::get('/team/members', [TeamWorkspaceController::class,'members'])
            ->name('team.members');

        Route::get('/team/stints', [TeamWorkspaceController::class,'stints'])
            ->name('team.stints');

        Route::get('/team/series', [TeamWorkspaceController::class,'series'])
            ->name('team.series');

        Route::get('/team/competitions', [TeamCompetitionController::class, 'index'])
            ->name('team.competitions.index');

        Route::get('/team/competitions/create', [TeamCompetitionController::class, 'create'])
            ->name('team.competitions.create');

        Route::get('/team/competitions/{series}/register', [TeamCompetitionController::class, 'register'])
            ->name('team.competitions.register');

        Route::post('/team/competitions/{series}/register', [TeamCompetitionController::class, 'store'])
            ->name('team.competitions.register.store');

        Route::get('/team/competitions/{series}', [TeamCompetitionController::class, 'show'])
            ->name('team.competitions.show');

        Route::get('/team/calendar', [TeamWorkspaceController::class,'calendar'])
            ->name('team.calendar');

        Route::post('/team/competitions/{series}/drivers', [TeamCompetitionController::class, 'addDriver'])
            ->name('team.competitions.drivers.store');

        Route::get('/team/competitions/{series}/drivers/create', [TeamCompetitionController::class, 'createDriver'])
            ->name('team.competitions.drivers.create');

        Route::patch('/team/competitions/{series}/drivers/{member}', [TeamCompetitionController::class, 'updateDriver'])
            ->name('team.competitions.drivers.update');

        Route::delete('/team/competitions/{series}/drivers/{member}', [TeamCompetitionController::class, 'removeDriver'])
            ->name('team.competitions.drivers.destroy');

        // 🔥 AQUÍ VA MEMBERS (IMPORTANTE)
        Route::put('/team/members/{user}', [TeamWorkspaceController::class,'updateRole'])
            ->name('team.members.update');

        Route::delete('/team/members/{user}', [TeamWorkspaceController::class,'removeMember'])
            ->name('team.members.destroy');
        Route::get('/team/members/{user}', [TeamWorkspaceController::class, 'showMember'])
            ->name('team.members.show');

        Route::post('/team-car/driver', [TeamCarDriverController::class, 'store']);
        Route::delete('/team-car/driver', [TeamCarDriverController::class, 'destroy']);
        Route::put('/team/members/{user}/helmet', [TeamWorkspaceController::class, 'updateHelmet'])
            ->name('team.members.updateHelmet');

        Route::post('/team-car/driver', [TeamCarDriverController::class, 'store']);
        Route::delete('/team-car/driver', [TeamCarDriverController::class, 'destroy']);
        Route::post('/workspace/{workspace}/activate',[WorkspaceController::class, 'activate'])
            ->name('workspace.activate');

        Route::get('/team/competitions/{series}/sessions', [TeamCompetitionController::class, 'sessions'])
            ->name('team.competitions.sessions');

        Route::get('/team/competitions/{series}/stints', [TeamCompetitionController::class, 'stints'])
            ->name('team.competitions.stints');

        Route::get('/team/competitions/{series}/strategy', [TeamCompetitionController::class, 'strategy'])
            ->name('team.competitions.strategy');

        Route::post('/team/competitions/{series}/strategy', [TeamCompetitionController::class, 'strategyUpdate'])
            ->name('team.strategy.update');

        Route::post('/team/competitions/{series}/strategy/reset', [TeamCompetitionController::class, 'strategyReset'])
            ->name('team.strategy.reset');

        Route::post('/team/competitions/{series}/strategy/favorite', [TeamCompetitionController::class, 'strategyFavorite'])
            ->name('team.strategy.favorite');

    });

    // LIVETIMING
    Route::get('/livetiming', [LiveTimingController::class,'index']);

});


require __DIR__.'/auth.php';

Route::get(

    '/test-team-schedule',

    function () {

        return app(

            \App\Services\Racing\RaceScheduleService::class

        )->generateTeamEvents(

            4,

            now(),

            now()->addHours(12)
        );
    }
);

use App\Http\Controllers\SeriesReferenceController;

Route::get('/series-reference/{series}', [
    SeriesReferenceController::class,
    'show',
])->name('series.reference');


