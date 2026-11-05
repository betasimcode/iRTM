
@php
    $weather = is_array($weather ?? null) ? $weather : [];

    $weatherItems = [
        'weather_track_temperature' => [
            'track_surface_temp_c',
            '°C',
        ],
        'weather_air_temperature' => [
            'track_air_temp_c',
            '°C',
        ],
        'weather_atmospheric_pressure' => [
            'track_air_pressure_hg',
            'inHg',
        ],
        'weather_air_density' => [
            'track_air_density_kg_m3',
            'kg/m³',
        ],
        'weather_wind_speed' => [
            'track_wind_speed_m_s',
            'm/s',
        ],
        'weather_relative_humidity' => [
            'track_relative_humidity_pct',
            '%',
        ],
        'weather_fog' => [
            'track_fog_pct',
            '%',
        ],
        'weather_precipitation' => [
            'track_precipitation_pct',
            '%',
        ],
    ];

    $formatWeather = static function ($value): string {
        return is_numeric($value)
            ? number_format((float) $value, 1, ',', '.')
            : '—';
    };
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach ($weatherItems as $label => [$key, $unit])
        <div class="rounded-lg border border-[var(--border)] bg-[var(--card)] p-3">
            <div class="text-xs text-[var(--muted)]">
                {{ __('ui.' . $label) }}
            </div>

            <div class="mt-1 font-mono-timing font-semibold text-[var(--text)]">
                @if (is_numeric($weather[$key] ?? null))
                    {{ $formatWeather($weather[$key]) }}
                    <span class="text-xs font-normal">{{ $unit }}</span>
                @else
                    —
                @endif
            </div>
        </div>
    @endforeach
</div>
