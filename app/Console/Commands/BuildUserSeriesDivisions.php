<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\User;
use App\Models\IrSession;
use App\Models\IrSessionResult;
use App\Models\UserSeriesDivision;

class BuildUserSeriesDivisions extends Command
{
    protected $signature =
        'iracing:build-divisions';

    protected $description =
        'Build user series divisions';

    public function handle()
    {
        $results = IrSessionResult::all();

        $this->info(
            "Results found: " . $results->count()
        );

        foreach ($results as $r) {

            // ====================================
            // USER
            // ====================================

            $user = User::where(

                'iracing_user_id',
                $r->iracing_user_id

            )->first();

            if (! $user) {

                $this->warn(
                    "User not found: {$r->iracing_user_id}"
                );

                continue;
            }

            // ====================================
            // SESSION
            // ====================================

            $session = IrSession::where(

                'iracing_subsession_id',
                $r->iracing_subsession_id

            )->first();

            if (! $session) {

                $this->warn(
                    "Session not found: {$r->iracing_subsession_id}"
                );

                continue;
            }

            // ====================================
            // SERIE
            // ====================================

            if (! $session->serie_id) {

                $this->warn(
                    "Session without serie_id: {$session->id}"
                );

                continue;
            }

            // ====================================
            // EXISTING
            // ====================================

            $existing = UserSeriesDivision::where(

                'user_id',
                $user->id

            )->where(

                'serie_id',
                $session->serie_id

            )->where(

                'division_id',
                $r->division_id

            )->first();

            // ====================================
            // UPDATE
            // ====================================

            if ($existing) {

                $existing->update([

                    'division_name' =>
                        $r->division_name,

                    'irating_avg' =>
                        $r->irating,

                    'last_seen_at' =>
                        now(),
                ]);

                $this->info(

                    "UPDATED | {$user->name} | {$r->division_name}"
                );

            } else {

                // ================================
                // CREATE
                // ================================

                UserSeriesDivision::create([

                    'user_id' =>
                        $user->id,

                    'serie_id' =>
                        $session->serie_id,

                    'division_id' =>
                        $r->division_id,

                    'division_name' =>
                        $r->division_name,

                    'irating_avg' =>
                        $r->irating,

                    'first_seen_at' =>
                        now(),

                    'last_seen_at' =>
                        now(),
                ]);

                $this->info(

                    "CREATED | {$user->name} | {$r->division_name}"
                );
            }
        }

        $this->info('DONE');

        return Command::SUCCESS;
    }
}