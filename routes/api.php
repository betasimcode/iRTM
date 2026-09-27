<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\RaceSessionController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CircuitController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TelemetryController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\IbtController;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\LoggerController;
use App\Http\Controllers\LapController;
use App\Http\Controllers\Api\SessionResultController;
use App\Http\Controllers\TrackDataController;
use App\Http\Controllers\Api\TelemetryTyreController;
use App\Http\Controllers\Api\NewTelemetryController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\Sync\SeriesSyncController;
use App\Http\Controllers\Api\Sync\RoundsSyncController;
use App\Http\Controllers\Api\ConnectorController;
use App\Http\Controllers\Api\Series\SeriesBrowserController;
use App\Http\Controllers\Api\IrStintController;

Route::get('/', function () {
    return view('welcome');
});

// Route::resource('race_sessions', RaceSessionController::class);
// Route::resource('cars', CarController::class);
// Route::resource('circuits', CircuitController::class);
// Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
// Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar');
// Route::resource('race_sessions', RaceSessionController::class);
// Route::get('/api/consumption/{car}/{circuit}', [RaceSessionController::class,'getAverageConsumption']);
// Route::get('/api/consumption/{car}/{circuit}',[RaceSessionController::class,'averageConsumption']);
// Route::post('/telemetry/stint',[TelemetryController::class,'store']);
// Route::get('/tracks/{car}', [FuelController::class, 'tracks']);

Route::prefix('v1')->group(function () {
    Route::post('/stints', [IrStintController::class, 'store']);
});
Route::post('/v1/stints/{id}/end', [irStintController::class, 'end']);

Route::post('/tracks/sync', [TrackDataController::class, 'sync']);

Route::post('/sync/series', SeriesSyncController::class);

Route::post('/sync/rounds', [RoundsSyncController::class, '__invoke']);

Route::get('/sync/series', function () {
    return 'Series Sync OK';
});

Route::post('/logger/token', [LoggerController::class, 'getToken']);

Route::post('/logger/token', function (Request $request) {
    // Buscamos al usuario que coincida con el ID que reporta el simulador
    $user = \App\Models\User::where('iracing_user_id', $request->iracing_user_id)->first();

    if (!$user) {
        return response()->json(['error' => 'Tu ID de iRacing no está vinculado a ninguna cuenta.'], 404);
    }

    return response()->json([
        'api_token' => $user->api_token,
        'id' => $user->id // 👈 AÑADE ESTO

        ]);
});

Route::post('/telemetry/ibt', [TelemetryController::class, 'processIbt']);

Route::post(
    '/telemetry/setup',
    [TelemetryController::class, 'processSetup']
);

Route::get(

    '/setups/{setup}/file',
    [SetupController::class, 'downloadRaw']

);

Route::post(
    '/sessions/results',
    [SessionResultController::class, 'store']
);

Route::post('/logger/car', [CarController::class, 'syncFromLogger']);

Route::post('/logger/setup-snapshot', [SetupController::class, 'store']);

Route::post('/logger/status', [LoggerController::class, 'status']);
Route::get('/logger/version', function () {
    return response()->json([
        'version' => config('app.logger_version'),
        'download_url' => url('/downloads/iRacingTeamManager.exe')
    ]);
});

Route::post('/init-session', [NewTelemetryController::class, 'initSession']);
Route::post('/start-stint', [NewTelemetryController::class, 'startStint']);
Route::post('/add-lap', [LapController::class, 'addLap']);
Route::post('/laps/enrich', [LapController::class, 'enrichLap']);
Route::post('/stint-tyres', [TelemetryTyreController::class, 'store']);
Route::post('/ibt/upload', [\App\Http\Controllers\IbtController::class, 'upload']);
// routes/api.php
Route::get('/tracks/{trackID}/sectors', function ($trackID) {
    return DB::table('track_maps')
        ->where('track_id', $trackID)
        ->orderBy('sector_number', 'asc')
        ->select('sector_number', 'start_pct as pct') // Renombramos aquí mismo
        ->get()
        ->toArray(); // Forzamos a array limpio
});



Route::get('/media/ping', [MediaController::class, 'ping']);


Route::get('/connector/status', function () {

    return response()->json([
        'status' => 'ok'
    ]);

});

Route::post('/connector/driver', [ConnectorController::class, 'driver']);

Route::get('/media/helmet-url/{memberId}', [MediaController::class, 'helmetUrl']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/series/browser', SeriesBrowserController::class)
    ->name('api.series.browser');

});

// routes/api.php
// Route::get('/track-sectors/{track_id}', [App\Http\Controllers\SectorController::class, 'getSectors']);
// Route::post('/track-sectors/register', [App\Http\Controllers\SectorController::class, 'registerSectors']);

// Route::post('/telemetry/lap-live',[TelemetryController::class,'storeLapLive']);
// Route::post('/track-sectors', [TelemetryController::class,'storeTrackSectors']);
// Route::get('/track-sectors/{track_id}', [TelemetryController::class,'getTrackSectors']);
