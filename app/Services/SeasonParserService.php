<?php

namespace App\Services;

use App\Models\SeasonImport;
use App\Models\SeasonPdf;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;
use RuntimeException;

class SeasonParserService
{
    public function generateSeriesJson(
        int $year,
        int $season
    ): string {
        $imports = SeasonImport::query()
            ->where('year', $year)
            ->where('season', $season)
            ->orderBy('page_start')
            ->orderBy('title')
            ->get();

        if ($imports->isEmpty()) {
            throw new RuntimeException(
                "No existen configuraciones de importación para {$year} S{$season}."
            );
        }

        $data = [
            'season' => [
                'weeks' => 12,
            ],

            'series' => $imports->map(
                function (SeasonImport $import) {
                    return [
                        'iracing_series_id' => $import->ir_serie_id,
                        'page' => $import->page_start,
                        'end' => $import->page_end,
                        'header' => $import->title,
                    ];
                }
            )->values()->all(),
        ];

        $parserConfigPath = base_path(
            'tools/iracing-season-parses/config'
        );

        if (! File::isDirectory($parserConfigPath)) {
            throw new RuntimeException(
                'No existe el directorio config del parser.'
            );
        }

        $seriesFile = $parserConfigPath
            . DIRECTORY_SEPARATOR
            . 'series.json';

        File::put(
            $seriesFile,
            json_encode(
                $data,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            )
        );

        return $seriesFile;
    }

    public function configureSeasonPdf(
        int $year,
        int $season
    ): string {
        $filename = "{$year}_{$season}.pdf";

        $seasonPdf = SeasonPdf::query()
            ->where('file', $filename)
            ->first();

        if (! $seasonPdf) {
            throw new RuntimeException(
                "No existe un PDF registrado para {$year} S{$season}."
            );
        }

        $parserRoot = base_path(
            'tools/iracing-season-parses'
        );

        $parserSamplesPath = $parserRoot
            . DIRECTORY_SEPARATOR
            . 'samples';

        $pdfPath = $parserSamplesPath
            . DIRECTORY_SEPARATOR
            . $filename;

        if (! File::exists($pdfPath)) {
            throw new RuntimeException(
                "El PDF registrado {$filename} no existe físicamente en samples."
            );
        }

        $settingsPath = $parserRoot
            . DIRECTORY_SEPARATOR
            . 'config'
            . DIRECTORY_SEPARATOR
            . 'settings.py';

        if (! File::exists($settingsPath)) {
            throw new RuntimeException(
                'No existe config/settings.py del parser.'
            );
        }

        $settings = File::get($settingsPath);

        $pattern = '/^SEASON_PDF\s*=.*$/m';

        $replacement =
            "SEASON_PDF = SAMPLES_DIR / \"{$filename}\"";

        if (! preg_match($pattern, $settings)) {
            throw new RuntimeException(
                'No se encontró la configuración SEASON_PDF en settings.py.'
            );
        }

        $settings = preg_replace(
            $pattern,
            $replacement,
            $settings
        );

        File::put(
            $settingsPath,
            $settings
        );

        return $settingsPath;
    }


    public function prepareSeason(
        int $year,
        int $season
    ): array {
        $filename = "{$year}_{$season}.pdf";

        $seasonPdf = SeasonPdf::query()
            ->where('file', $filename)
            ->first();

        if (! $seasonPdf) {
            throw new RuntimeException(
                "No existe un PDF registrado para {$year} S{$season}."
            );
        }

        $pdfPath = base_path(
            "tools/iracing-season-parses/samples/{$filename}"
        );

        if (! File::exists($pdfPath)) {
            throw new RuntimeException(
                "El PDF {$filename} no existe físicamente."
            );
        }

        $importsCount = SeasonImport::query()
            ->where('year', $year)
            ->where('season', $season)
            ->count();

        if ($importsCount === 0) {
            throw new RuntimeException(
                "No existen configuraciones de importación para {$year} S{$season}."
            );
        }

        $seriesFile = $this->generateSeriesJson(
            $year,
            $season
        );

        $settingsFile = $this->configureSeasonPdf(
            $year,
            $season
        );

        return [
            'year' => $year,
            'season' => $season,
            'pdf' => $filename,
            'imports' => $importsCount,
            'series_file' => $seriesFile,
            'settings_file' => $settingsFile,
        ];
    }


    public function runSeason(
        int $year,
        int $season
    ): array {
        $preparation = $this->prepareSeason(
            $year,
            $season
        );

        $parserRoot = base_path(
            'tools/iracing-season-parses'
        );

        $process = new Process(
            [
                'python',
                'main.py',
                '--year',
                (string) $year,
                '--season',
                (string) $season,
            ],
            $parserRoot
        );

        $process->setEnv([
            'PYTHONIOENCODING' => 'utf-8',
        ]);

        $process->setTimeout(null);

        $process->run();

        return [
            ...$preparation,

            'successful' => $process->isSuccessful(),
            'exit_code' => $process->getExitCode(),
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput(),
        ];
    }







}
