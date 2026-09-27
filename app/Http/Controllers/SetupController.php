<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Stint;
use App\Models\Car;
use App\Services\SetupAlertEngine;
use App\Models\Track;
use App\Models\Setup;
use App\Models\SetupValue;
use App\Helpers\TimeHelper;
use App\Services\SetupMapper;
use App\Services\SetupAnalyzer;
use App\Services\TelemetryMapper;
use App\Services\TelemetryAnalysisEngine;

class SetupController extends Controller
{

    public function show($id)
    {

        $setup = Setup::with([
            'values',
            'stint.user',
            'stint.car',
            'stint.track',
            'stint.laps'
        ])->findOrFail($id);

        $mapped = SetupMapper::map($setup->values);

        $zones = $mapped['zones'];
        $activeZones = $mapped['active_zones'];
        $flags = $mapped['flags'] ?? [];
        $stint = $setup->stint;


        // 🔥 métricas
        $bestLap = $stint->laps->min('lap_time');
        $avgLap  = $stint->laps->avg('lap_time');

        // 🔹 convertir valores
        $mappedValues = [];

        foreach ($setup->values as $item) {
            $value = $item->value;

            if (is_string($value) && str_contains($value, ',')) {
                $mappedValues[$item->normalized_key] = $value;
            } else {
                $mappedValues[$item->normalized_key] = is_numeric($value)
                    ? (float) $value
                    : $value;
            }
        }
        $telemetryData = TelemetryMapper::map($mappedValues);
        $telemetryAlerts = TelemetryAnalysisEngine::analyze($telemetryData);

        $telemetryEnd = $stint->tyreTelemetry()
            ->where('snapshot_type', 'end')
            ->first();

        $telemetry = null;

        if ($telemetryEnd) {

            $telemetry = [
                'fl' => [
                    'outer' => round($telemetryEnd->temp_fl_o, 1),
                    'middle' => round($telemetryEnd->temp_fl_m, 1),
                    'inner' => round($telemetryEnd->temp_fl_i, 1),
                ],
                'fr' => [
                    'inner' => round($telemetryEnd->temp_fr_i, 1),
                    'middle' => round($telemetryEnd->temp_fr_m, 1),
                    'outer' => round($telemetryEnd->temp_fr_o, 1),

                ],
                'rl' => [
                    'outer' => round($telemetryEnd->temp_rl_o, 1),
                    'middle' => round($telemetryEnd->temp_rl_m, 1),
                    'inner' => round($telemetryEnd->temp_rl_i, 1),
                ],
                'rr' => [
                    'outer' => round($telemetryEnd->temp_rr_o, 1),
                    'middle' => round($telemetryEnd->temp_rr_m, 1),
                    'inner' => round($telemetryEnd->temp_rr_i, 1),
                ],
            ];
        }

        $telemetryWear = null;

        if ($telemetryEnd) {

            $telemetryWear = [
                'fl' => [
                    'outer' => round($telemetryEnd->wear_fl, 3),
                    'middle' => round($telemetryEnd->wear_fl, 3),
                    'inner' => round($telemetryEnd->wear_fl, 3),
                ],
                'fr' => [
                    'outer' => round($telemetryEnd->wear_fr, 3),
                    'middle' => round($telemetryEnd->wear_fr, 3),
                    'inner' => round($telemetryEnd->wear_fr, 3),
                ],
                'rl' => [
                    'outer' => round($telemetryEnd->wear_rl, 3),
                    'middle' => round($telemetryEnd->wear_rl, 3),
                    'inner' => round($telemetryEnd->wear_rl, 3),
                ],
                'rr' => [
                    'outer' => round($telemetryEnd->wear_rr, 3),
                    'middle' => round($telemetryEnd->wear_rr, 3),
                    'inner' => round($telemetryEnd->wear_rr, 3),
                ],
            ];
        }

        // 🔥 CONTEXTO (HARDCODE DE MOMENTO)
        $context = [
            'track' => 'fuji',
            'track_type' => 'mixed',
            'car' => 'mustang_gt3',
        ];

        $alerts = SetupAlertEngine::evaluate($mappedValues, $context);

        $bestLap = $stint->laps
        ->where('lap_time', '>', 0)
        ->sortBy('lap_time')
        ->first();

        return view('setups.show', compact(
            'setup',
            'zones',
            'activeZones',
            'flags', // 🔥 IMPORTANTE
            'stint',
            'bestLap',
            'avgLap',
            'alerts',
            'telemetryAlerts',
            'telemetryWear',
            'telemetry',
            'stint',
            'bestLap'
        ));
    }

    private function flatten(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {

            // normalizamos key (opcional pero recomendable)
            $key = strtolower($key);

            $newKey = $prefix ? $prefix . '.' . $key : $key;

            if (is_array($value)) {
                $result += $this->flatten($value, $newKey);
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    public function store(Request $request)
    {
        $request->validate([
            'stint_id' => 'required|exists:ir_stints,id',
            'values'   => 'required|array'
        ]);

        // 🔹 1. STINT
        $stint = Stint::findOrFail($request->stint_id);

        // 🔒 evitar duplicados
        if ($stint->setup_id) {
            return response()->json(['status' => 'already_exists'], 200);
        }

        // 🔹 2. MAPEO CAR (IRACING → INTERNO)
        $car = Car::firstOrCreate(
            ['iracing_car_id' => $stint->car_id],
            ['name' => $stint->car_name]
        );

        // 🔹 3. MAPEO TRACK (igual patrón)
        $track = Track::firstOrCreate(
            ['iracing_track_id' => $stint->track_id],
            ['name' => 'Track ' . $stint->track_id]
        );

        // 🔹 4. CREAR SETUP
        $setup = Setup::create([
            'stint_id' => $stint->id,
            'user_id'  => $stint->user_id,
            'team_id'  => $stint->team_id,
            'car_id'   => $car->id,     // ✅ FK correcta
            'track_id' => $track->id,   // ✅ FK correcta
            'name'     => 'Stint ' . $stint->id . ' - ' . $stint->car_name,
            'visibility' => 'private',
            'setup_type' => 'T',
            'setup_name' => 'setup_' . $stint->id,
        ]);

        // =====================================
        // STO FILE LINK
        // =====================================

        $setupFile =

        'setups/stint_' .

        $stint->id .

        '.sto';

        if (Storage::exists($setupFile)) {

        $setup->update([

            'setup_file_path' => $setupFile,

            'setup_file_hash' => md5(

                Storage::get($setupFile)
            ),

            'setup_file_size' => Storage::size(
                $setupFile
            ),
        ]);

        \Log::info(

            'SETUP FILE LINKED',

            [

                'setup_id' => $setup->id,

                'stint_id' => $stint->id,

                'file' => $setupFile
            ]
        );
        }

        // 🔹 5. GUARDAR VALUES
        $flatValues = $this->flatten($request->values);

        foreach ($flatValues as $key => $value) {
            SetupValue::create([
                'setup_id' => $setup->id,
                'key'      => $key,
                'value'    => (string) $value
            ]);
        }
        $weather = SetupValue::query()

            ->join(
                'setup_item_definitions',
                'setup_values.key',
                '=',
                'setup_item_definitions.raw_key'
            )

            ->where(
                'setup_values.setup_id',
                $setup->id
            )

            ->where(
                'setup_item_definitions.label',
                'TYRE TYPE'
            )

            ->value(
                'setup_values.value'
            );

            /// test
            \Log::info(

                'SETUP WEATHER DETECTED',

                [

                    'setup_id' => $setup->id,

                    'weather' => $weather
                ]
            );
            /// test

            $setup->setup_weather = $weather

                ? strtoupper(
                    trim($weather)
                )

                : 'DRY';

            $setup->save();

            $setup->setup_name = $this->generateSetupName(

                $setup->fresh([
                    'user',
                    'track',
                    'car'
                ])
            );

            $setup->save();

            \Log::info(

                'SETUP WEATHER FINAL',

                [

                    'setup_id' => $setup->id,

                    'weather' => $weather ?: 'DRY (DEFAULT)'
                ]
            );

        // 🔗 6. LINK
        $stint->update([
            'setup_id' => $setup->id
        ]);

        return response()->json([
            'status' => 'ok',
            'setup_id' => $setup->id
        ]);
    }

    public function download(Setup $setup)
    {
        abort_unless(

            $setup->setup_file_path,

            404
        );

        abort_unless(

            Storage::exists(
                $setup->setup_file_path
            ),

            404
        );

        $filename =

            ($setup->setup_name ?: 'setup_' . $setup->id)

            . '.sto';

        return Storage::download(

            $setup->setup_file_path,

            $filename
        );
    }


    private function generateSetupName(Setup $setup): string
    {

        $stint = $setup->stint;
        $bestLap = $stint->laps->min('lap_time');


        $date = $setup->created_at
            ->format('Ymd_Hi');

        $user = collect(
            explode(' ', $setup->user->name)
        )
            ->map(
                fn ($part) => strtoupper(
                    substr($part, 0, 1)
                )
            )
            ->join('');

        $track = strtoupper(
            preg_replace(
                '/[^A-Za-z0-9]/',
                '',
                $setup->track->short_name
            )
        );

        $car = strtoupper(
            $setup->car->short_name
                ?? 'CAR'
        );

        $type = $setup->setup_type
            ?? 'T';

        $weather = $setup->setup_weather
            ?? 'DRY';

        $time = str_replace([':','.'], '_', substr(laptime($bestLap), 0, 6));



        return implode(
            '-',
            [
                $date,
                $user,
                $track,
                $car,
                $type,
                $weather,
                $time,
            ]
        );
    }



    public function install(Setup $setup)
    {
        return response()->json([

            'setup_id' => $setup->id,

            'setup_name' =>

                $setup->setup_name ??

                ('setup_' . $setup->id),

            'car_folder' =>

                $setup->car->iracing_setup_folder,

            'track_name' =>

                $setup->track->display_name .

                ($setup->track?->variant
                    ? ' - ' . $setup->track->variant
                    : ''),
                    ]);
    }

    public function repairFile(Setup $setup)
    {
        /*
        |--------------------------------------------------------------------------
        | Repair track metadata
        |--------------------------------------------------------------------------
        */

        if (! $setup->track_id && $setup->stint) {

            $stintTrackId = $setup->stint->track_id;

            if ($stintTrackId) {

                $track = Track::query()
                    ->where(
                        'iracing_track_id',
                        $stintTrackId
                    )
                    ->first();

                if ($track) {

                    $setup->track_id = $track->id;

                    $setup->save();
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Validate required relations
        |--------------------------------------------------------------------------
        */

        if (! $setup->car) {

            return response()->json([
                'message' => 'El setup no tiene un coche válido.',
            ], 422);
        }

        if (! $setup->track) {

            return response()->json([
                'message' => 'No se ha podido recuperar el circuito del setup.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Repair response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'setup_id' => $setup->id,

            'stint_id' => $setup->stint_id,

            'car_folder' => $setup->car->iracing_setup_folder,

            'track_name' => $setup->track->display_name,

            'setup_name' =>
                $setup->setup_name ??
                ('setup_' . $setup->id),

        ]);
    }

    public function downloadRaw(Setup $setup)
    {

        abort_unless(

            $setup->setup_file_path,

            404
        );

        return Storage::download(

            $setup->setup_file_path,

            ($setup->setup_name ?: 'setup_'.$setup->id)
            . '.sto'
        );
    }

    public function updateType(
        Request $request,
        Setup $setup
    )
    {
        $request->validate([

            'setup_type' =>

                'required|in:T,P,Q,RAC,SPR,END'
        ]);

        $setup->update([

            'setup_type' =>

                $request->setup_type
        ]);

        $setup->update([

            'setup_name' =>

                $this->generateSetupName(

                    $setup->fresh([
                        'user',
                        'track',
                        'car'
                    ])
                )
        ]);

        return response()->json([

            'status' => 'ok',

            'setup_name' =>

                $setup->setup_name
        ]);
    }




}
