<?php

namespace App\Services;

use App\Models\IrSession;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use App\Models\IrSessionResult;
use App\Models\UserSeriesDivision;

class SessionRecoveryService
{
    public function rebuild(
        IrSession $session
    ) {

        // ====================================
        // DEBUG
        // ====================================

       // ========================================
        // FIND IBT
        // ========================================

        $files = Storage::allFiles(
            'telemetry'
        );

        $target = null;

        foreach ($files as $file) {

            if (

                str_contains(

                    $file,

                    $session->iracing_subsession_id . '.ibt'
                )

            ) {

                $target = $file;

                break;
            }
        }

        // ========================================
        // NOT FOUND
        // ========================================

        if (! $target) {

            logger()->warning(

                'REBUILD IBT NOT FOUND',

                [
                    'subsession_id' =>
                        $session->iracing_subsession_id
                ]
            );

            return 'IBT not found';
        }

        // ========================================
        // FOUND
        // ========================================

        logger()->info(

            'REBUILD IBT FOUND',

            [
                'file' => $target
            ]
        );

        // ========================================
        // PYTHON PARSER
        // ========================================

        $pythonScript = base_path(
            'python/rebuild_session.py'
        );

        $fullPath = storage_path(
            'app/private/' . $target
        );

        $process = new Process([

            'python',

            $pythonScript,

            $fullPath
        ]);

        $process->run();

        // ========================================
        // FAILED
        // ========================================

        if (! $process->isSuccessful()) {

            logger()->error(

                'REBUILD PROCESS FAILED',

                [
                    'error' => $process->getErrorOutput()
                ]
            );

            return 'Python rebuild failed';
        }

        // ========================================
        // OUTPUT
        // ========================================

        $output = json_decode(

            $process->getOutput(),

            true
        );

        logger()->info(

            'REBUILD PYTHON OUTPUT',

            $output
        );


        // ========================================
        // VALIDATE RESULTS
        // ========================================

        if (

            ! isset($output['results'])

            || ! is_array($output['results'])

        ) {

            return 'No results parsed';
        }

        // ========================================
        // DELETE OLD RESULTS
        // ========================================

        IrSessionResult::where(

            'iracing_subsession_id',

            $session->iracing_subsession_id

        )->delete();

        // ========================================
        // INSERT RESULTS
        // ========================================

        foreach ($output['results'] as $r) {

            IrSessionResult::create([

                'iracing_subsession_id' =>
                    $session->iracing_subsession_id,

                'position' =>
                    $r['position'] ?? null,

                'class_position' =>
                    $r['class_position'] ?? null,

                'car_idx' =>
                    $r['car_idx'] ?? null,

                'user_name' =>
                    $r['user_name'] ?? null,

                'car_name' =>
                    $r['car_name'] ?? null,

                'car_number' =>
                    $r['car_number'] ?? null,

                'fastest_time' =>
                    $r['fastest_time'] ?? null,

                'irating' =>
                    $r['irating'] ?? null,

                'iracing_user_id' =>
                    $r['iracing_user_id'] ?? null,

                'license_class' =>
                    $r['license_class'] ?? null,

                'country' =>
                    $r['country'] ?? null,

                'country_id' =>
                    $r['country_id'] ?? null,

                'division_name' =>
                    $r['division_name'] ?? null,

                'division_id' =>
                    $r['division_id'] ?? null,
            ]);

            // ====================================
            // USER DIVISION REBUILD
            // ====================================

            if (

                isset($r['iracing_user_id'])

                && $r['division_id']

            ) {

                $user = \App\Models\User::where(

                    'iracing_user_id',

                    $r['iracing_user_id']

                )->first();

                if ($user) {

                    UserSeriesDivision::updateOrCreate(

                        [

                            'user_id' => $user->id,

                            'serie_id' => $session->serie_id,
                        ],

                        [

                            'division_id' =>
                                $r['division_id'],

                            'division_name' =>
                                $r['division_name'],

                            'irating_avg' =>
                                $r['irating'],

                            'last_seen_at' =>
                                now(),
                        ]
                    );
                }
            }
        }


        return

            count($output['results'])

            . ' results rebuilt';

        // ====================================
        // TODO:
        // localizar IBT
        // ====================================
    }
}