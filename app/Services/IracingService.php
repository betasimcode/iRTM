<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;

class IracingService
{
    protected $baseUrl = 'https://members.iracing.com';

    public function getSeries()
    {
        $response = Http::withHeaders([
            'Cookie' => env('IRACING_COOKIE'),
            'User-Agent' => 'Mozilla/5.0',
            'Accept' => 'application/json',
            'Referer' => 'https://members-ng.iracing.com/',
            'Origin' => 'https://members-ng.iracing.com'
        ])->get('https://members-ng.iracing.com/data/series/get');

        if (!$response->ok()) {
            dd('ERROR 1', $response->status(), $response->body());
        }
        dd(env('IRACING_COOKIE'));
        $json = $response->json();

        if (!isset($json['link'])) {
            dd('NO LINK', $json);
        }

        $data = Http::get($json['link']);

        if (!$data->ok()) {
            dd('ERROR 2', $data->status(), $data->body());
        }

        return $data->json();
    }
}