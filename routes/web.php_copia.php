<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\RaceSessionController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CircuitController;
use App\Http\Controllers\SeriesController;
use App\Livewire\Drivers\DriverTable;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TelemetryController;
use App\Http\Controllers\StintController;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\IracingSeriesController;
use App\Http\Controllers\TeamWorkspaceController;
use App\Http\Controllers\LiveTimingController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;


Route::get('/', function () {
    return view('landing');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')
    ->get('/drivers', function () {
        return view('drivers.index');
    })->name('drivers.index');

Route::get('/teams', function () {
    return view('teams.index');
})
->middleware('auth')
->name('teams.index');

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {

    // FUEL
    Route::get('/fuel-calculator', [FuelController::class,'index'])->name('fuel.index');

    Route::post('/series/{series}/choose-car',
        [SeriesController::class,'chooseCar'])
        ->name('series.chooseCar');

    // SERIES

    Route::resource('race_sessions', RaceSessionController::class);
    Route::resource('cars', CarController::class);
    Route::resource('circuits', CircuitController::class);
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar');
    Route::resource('race_sessions', RaceSessionController::class);
    Route::get('/api/consumption/{car}/{circuit}', [RaceSessionController::class,'getAverageConsumption']);
    Route::get('/api/consumption/{car}/{circuit}',[RaceSessionController::class,'averageConsumption']);
    Route::get('/stints', [StintController::class, 'index'])->name('stints.index');
    Route::get('/stints/{stint}', [StintController::class, 'show'])->name('stints.show');
    Route::get('/stints/{stint}/analysis', [StintController::class, 'analysis'])->name('stints.analysis');
    Route::post('/fuel-calculator', [App\Http\Controllers\FuelController::class,'calculate']);
    // SERIES
    Route::get('/series', [SeriesController::class, 'index'])
        ->name('series.index');

    Route::resource('series', SeriesController::class)
        ->except(['show']);

    Route::get('/series/{series}', [SeriesController::class, 'show'])
        ->name('series.show');

    Route::get('/series/{series}/manage', [SeriesController::class, 'manage'])
        ->name('series.manage');

    Route::get('/series/{series}/plan', [SeriesController::class, 'plan'])
        ->name('series.plan');

    Route::prefix('series/{series}')
    ->group(function () {

        Route::get('/stints', [SeriesController::class, 'stints'])
            ->name('series.stints');

        Route::get('/stints/{stint}', [SeriesController::class, 'stintShow'])
            ->name('series.stints.show');

        Route::get('/stints/{stint}/analysis', [SeriesController::class, 'stintAnalysis'])
            ->name('series.stints.analysis');

    });


    /// ROUNDS ///////////////////////////////////////////////////////
    Route::post('/series/{series}/rounds', [SeriesController::class, 'storeRound'])
        ->name('series.rounds.store');


    Route::put('/rounds/{round}', [SeriesController::class, 'updateRound'])
        ->name('series.rounds.update');

    Route::delete('/rounds/{round}', [SeriesController::class, 'destroyRound'])
        ->name('series.rounds.destroy');

    Route::get('/admin/logger-status', [AdminController::class, 'loggerStatus']);

    Route::middleware(['auth','admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::resource('users', UserController::class);
        });

});

 Route::middleware(['auth','admin'])->group(function () {

        Route::resource('iracing-series', IracingSeriesController::class);

    });

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



Route::middleware(['auth'])->group(function () {

    Route::get('/team', [TeamWorkspaceController::class,'dashboard'])
        ->name('team.dashboard');

    Route::get('/team/members', [TeamWorkspaceController::class,'members'])
        ->name('team.members');

    Route::get('/team/stints', [TeamWorkspaceController::class,'stints'])
        ->name('team.stints');

    Route::get('/team/series', [TeamWorkspaceController::class,'series'])
        ->name('team.series');

    Route::get('/team/calendar', [TeamWorkspaceController::class,'calendar'])
        ->name('team.calendar');

    Route::patch('/team/members/{user}/role', [TeamWorkspaceController::class,'updateRole'])
        ->name('team.members.role');

    Route::delete('/team/members/{user}', [TeamWorkspaceController::class,'removeMember'])
        ->name('team.members.remove');

    });

Route::get('/livetiming', [LiveTimingController::class,'index']);
