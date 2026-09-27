<?php

namespace App\Services\Setup;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\SessionFile;
use App\Models\Stint;

class SetupRepairService
{
    public static function repair(Stint $stint): array
    {
        $ibt = $stint->session
            ->files()
            ->where('type', 'ibt')
            ->first();

        if (!$ibt) {

            return [

                'success' => false,

                'message' => 'IBT file not found.',

            ];
        }

        if (!Storage::disk('local')->exists($ibt->filepath)) {

            return [

                'success' => false,

                'message' => 'IBT file not found on disk.',

            ];
        }

        $filepath = Storage::disk('local')->path($ibt->filepath);

        $response = Http::timeout(120)
            ->post('http://127.0.0.1:32000/process-ibt', [

                'filepath' => $filepath,

            ]);

        return [

            'success' => $response->successful(),

            'status' => $response->status(),

            'body' => $response->json(),

        ];
    }





}
