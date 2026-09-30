<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class TestIracingStandingsRequest extends Command
{
    protected $signature = 'iracing:test-standings
                            {--season=6507 : ID de temporada iRacing}
                            {--class=22 : ID de clase}
                            {--week=-1 : Semana de carrera}
                            {--division=3 : División a consultar}';

    protected $description =
        'Prueba la petición BFF de clasificaciones de iRacing sin importar datos';

    public function handle(): int
    {
        $url = 'https://members-ng.iracing.com/bff/pub/proxy/data/stats/season_driver_standings';

        $params = [
            'season_id' => (int) $this->option('season'),
            'car_class_id' => (int) $this->option('class'),
            'race_week_num' => (int) $this->option('week'),
            'division' => (int) $this->option('division'),
        ];

        $this->info('Consultando clasificaciones de iRacing...');
        $this->line('URL: ' . $url);
        $this->line('Parámetros: ' . json_encode($params));

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json, text/plain, */*',
                'User-Agent' => 'Mozilla/5.0',
            ])
                ->timeout(30)
                ->withoutRedirecting()
                ->get($url, $params);

            $this->newLine();
            $this->info('HTTP STATUS: ' . $response->status());

            $this->line('CONTENT-TYPE: ' .
                $response->header('Content-Type'));

            $this->line('LOCATION: ' .
                ($response->header('Location') ?? '(sin Location)'));

            $this->newLine();
            $this->info('CABECERAS DE RESPUESTA:');

            foreach ($response->headers() as $name => $values) {
                $this->line(
                    $name . ': ' . implode(', ', $values)
                );
            }

            $this->newLine();
            $this->info('CUERPO DE RESPUESTA (RAW):');

            $this->line($response->body());

            return self::SUCCESS;

        } catch (Throwable $e) {
            $this->error('ERROR DE PETICIÓN: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
