<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IbtSessionProcessor
{
    public function process($path)
    {
        try {
            $raw = Storage::get($path);

            // 🔥 parse básico (texto plano)
            $data = $this->parse($raw);

            if (!$data) {
                Log::warning("IBT parse vacío");
                return;
            }

            DB::transaction(function () use ($data) {

                // =========================
                // 🔥 SESSION
                // =========================
                $sessionId = DB::table('ir_sessions')->updateOrInsert(
                    ['iracing_subsession_id' => $data['subsession_id']],
                    [
                        'name' => $this->buildName($data),
                        'series_id' => $data['series_id'],
                        'official' => $data['official'],
                        'is_fixed_setup' => $data['fixed'],
                        'session_date' => $data['date'],
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );

                $dbSessionId = DB::table('ir_sessions')
                    ->where('iracing_subsession_id', $data['subsession_id'])
                    ->value('id');

                // =========================
                // 🔥 PHASES
                // =========================
                foreach ($data['sessions'] as $s) {

                    DB::table('ir_session_phases')->updateOrInsert(
                        [
                            'session_id' => $dbSessionId,
                            'session_num' => $s['num']
                        ],
                        [
                            'session_type' => $s['type'],
                            'updated_at' => now(),
                            'created_at' => now()
                        ]
                    );
                }

                // =========================
                // 🔥 LINK STINTS
                // =========================
                DB::table('ir_stints')
                    ->where('iracing_subsession_id', $data['subsession_id'])
                    ->update([
                        'session_id' => $dbSessionId
                    ]);

            });

            Log::info("✅ IBT procesado correctamente");

        } catch (\Exception $e) {
            Log::error("❌ IBT process error: ".$e->getMessage());
        }
    }

    // =========================
    // 🔥 PARSER SIMPLE
    // =========================
    private function parse($text)
    {
        try {

            preg_match('/SubSessionID:\s*(\d+)/', $text, $sub);
            preg_match('/SeriesID:\s*(\d+)/', $text, $series);
            preg_match('/Official:\s*(\d+)/', $text, $official);
            preg_match('/IsFixedSetup:\s*(\d+)/', $text, $fixed);
            preg_match('/Date:\s*([0-9\-]+)/', $text, $date);

            preg_match_all('/SessionNum:\s*(\d+).*?SessionType:\s*([^\n]+)/s', $text, $sessions, PREG_SET_ORDER);

            $parsedSessions = [];

            foreach ($sessions as $s) {
                $parsedSessions[] = [
                    'num' => (int)$s[1],
                    'type' => trim($s[2])
                ];
            }

            return [
                'subsession_id' => $sub[1] ?? null,
                'series_id' => $series[1] ?? null,
                'official' => $official[1] ?? 0,
                'fixed' => $fixed[1] ?? 0,
                'date' => $date[1] ?? null,
                'sessions' => $parsedSessions
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    private function buildName($data)
    {
        if ($data['series_id'] > 0) {
            return "{$data['series_id']}_{$data['subsession_id']}";
        }

        return "{$data['date']}_{$data['subsession_id']}";
    }
}