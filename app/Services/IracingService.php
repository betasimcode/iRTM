<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class IracingService
{
    protected string $baseUrl = 'https://members-ng.iracing.com';

    protected function client()
    {
        $cookie = config('services.iracing.cookie');

        if (empty($cookie)) {
            throw new RuntimeException(
                'IRACING_COOKIE no está configurada.'
            );
        }

        return Http::withHeaders([
            'Cookie' => $cookie,
            'User-Agent' => 'Mozilla/5.0',
            'Accept' => 'application/json',
            'Referer' => $this->baseUrl . '/',
            'Origin' => $this->baseUrl,
        ])
            ->connectTimeout(10)
            ->timeout(30);
    }

    public function getSeries(): array
    {
        try {
            $response = $this->client()
                ->get($this->baseUrl . '/data/series/get');

            if (!$response->successful()) {
                Log::warning('iRacing getSeries HTTP error', [
                    'status' => $response->status(),
                ]);

                throw new RuntimeException(
                    'iRacing ha rechazado la consulta de series.'
                );
            }

            $json = $response->json();

            if (!is_array($json) || empty($json['link'])) {
                throw new RuntimeException(
                    'iRacing no ha devuelto un enlace de descarga válido.'
                );
            }

            // El enlace es temporal y lo devuelve iRacing.
            $data = Http::connectTimeout(10)
                ->timeout(30)
                ->get($json['link']);

            if (!$data->successful()) {
                Log::warning('iRacing series download error', [
                    'status' => $data->status(),
                ]);

                throw new RuntimeException(
                    'No se pudieron descargar los datos de series.'
                );
            }

            $result = $data->json();

            if (!is_array($result)) {
                throw new RuntimeException(
                    'La respuesta de series no es un JSON válido.'
                );
            }

            return $result;

        } catch (RuntimeException $e) {
            throw $e;

        } catch (\Throwable $e) {
            Log::error('Error consultando series de iRacing', [
                'exception' => get_class($e),
            ]);

            throw new RuntimeException(
                'Error de conexión con iRacing.',
                0,
                $e
            );
        }
    }
}
