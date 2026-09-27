<?php

namespace App\Services\BS90;

use App\Models\TrackMap;

class TracksExporter
{
    public function build(): string
    {
        // Leer todos los sectores ordenados
        $rows = TrackMap::query()
            ->orderBy('track_id')
            ->orderBy('sector_number')
            ->get([
                'track_id',
                'sector_number',
                'start_pct'
            ]);

        // Agrupar por circuito
        $tracks = [];

        $grouped = $rows->groupBy('track_id')->sortKeys();

        foreach ($grouped as $trackId => $trackRows) {

            $tracks[(int)$trackId] = [

                'sectorCount' => $trackRows->count(),

                'sectors' => $trackRows
                    ->pluck('start_pct')
                    ->map(fn ($pct) => (float) $pct)
                    ->values()
                    ->toArray()

            ];
        }

        // --------------------------------------------------
        // Generación del fichero Javascript
        // --------------------------------------------------

        $output = [];

        $output[] = "/*";
        $output[] = "----------------------------------------------------------";
        $output[] = " BetaSimCode SHD";
        $output[] = " BS90.Data.Tracks";
        $output[] = " AUTO GENERATED FILE";
        $output[] = " DO NOT EDIT";
        $output[] = "----------------------------------------------------------";
        $output[] = "*/";
        $output[] = "";

        $output[] = "if (!globalThis.BS90) globalThis.BS90 = {};";
        $output[] = "";
        $output[] = "BS90.Data = BS90.Data || {};";
        $output[] = "";
        $output[] = "BS90.Data.Tracks = {";

        $totalTracks = count($tracks);
        $currentTrack = 0;

        foreach ($tracks as $trackId => $track) {

            $currentTrack++;

            $output[] = "";
            $output[] = "    \"{$trackId}\": {";
            $output[] = "        sectorCount: {$track['sectorCount']},";
            $output[] = "        sectors: [";

            $totalSectors = count($track['sectors']);

            foreach ($track['sectors'] as $index => $sector) {

                $comma = ($index < $totalSectors - 1) ? "," : "";

                $output[] = "            {$sector}{$comma}";
            }

            $trackComma = ($currentTrack < $totalTracks) ? "," : "";

            $output[] = "        ]";
            $output[] = "    }{$trackComma}";
        }

        $output[] = "";
        $output[] = "};";
        $output[] = "";

        $content = implode(PHP_EOL, $output);

        // --------------------------------------------------
        // Crear carpeta si no existe
        // --------------------------------------------------

        $path = storage_path('app/bs90');

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // --------------------------------------------------
        // Guardar fichero
        // --------------------------------------------------

        file_put_contents(
            $path . DIRECTORY_SEPARATOR . 'BS90.Data.Tracks.js',
            $content
        );

        return $content;
    }
}
