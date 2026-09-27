@extends('layouts.app')

@section('title', 'Stints')
@section('page-title', $series->name. ' - Stints at '. $activeRound?->track->display_name )

@section('content')

<div class="space-y-6">

<br>
    <div class="bg-[var(--bg)] rounded-xl border border-[var(--border)] overflow-hidden shadow-lg shadow-black/20">

        {{-- Header --}}
        @php
    $stint = $stints->first();
    $teamCar = $stint?->series?->teamCar;
    $car = $teamCar?->car ?? $stint?->car;
@endphp

<div class="px-6 py-4 border-b border-[var(--border)] flex items-center justify-between">

    <div class="flex items-center gap-6">

        {{-- TEAM LOGO --}}
        @if($stint?->user?->team?->logo_path)
            <img src="{{ asset('storage/'.$stint->user->team->logo_path) }}"
                 class="w-32 object-contain">
        @endif

        {{-- INFO --}}
        <div>

            {{-- CAR NAME --}}
            <h2 class="text-lg font-semibold text-[var(--text)]">
                {{ $stint->car_name ?? 'No car' }}
            </h2>

            {{-- TEAM MODE --}}
            @if($teamCar)
                <p class="text-xs text-[var(--value-info)]">
                    Team entry
                </p>
            @else
                <p class="text-xs text-[var(--value-data)]">
                    Private entry
                </p>
            @endif

        </div>

    </div>

    {{-- CAR IMAGE --}}
    <div>
        <img src="
            {{
                $teamCar?->image_path
                    ? asset('storage/'.$teamCar->image_path)
                    : ($car?->image_path
                        ? asset('storage/'.$car->image_path)
                        : asset('storage/'.$activeCar?->image_path))
            }}
        "
        class="h-20 object-contain">
    </div>

</div>

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-[var(--card)] text-[var(--text)] uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('ui.driver') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('ui.sessions') }}</th>
                        <th class="px-5 py-3 text-left">{{ __('ui.date') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('ui.type') }}</th>
                        <th class="px-2 py-3 text-left">{{ __('ui.laps') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('ui.avglaps') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('ui.avgfuel') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--div)]">

                    @forelse($stints as $stint)

                        <tr
                        onclick="window.location='{{ route('stints.show', [
                            'stint' => $stint,
                            'from_series' => $series->id
                            ]) }}'"
                            class="hover:bg-[var(--card-hover)] transition cursor-pointer">

                            <td class="px-4 py-4 text-[var(--text)]">
                                {{ $stint->user?->iracing_name ?? '—' }}
                            </td>

                            <td class="px-6 py-4 text-[var(--text)]">
                                <span class="bg-[var(--card)] px-2 py-1 rounded">
                                {{ $stint->iracing_subsession_id }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[var(--text)]">
                                {{ $stint->created_at }}
                            </td>

                            <td class="px-6 py-4 dtext-[var(--text)] capitalize">
                                {{ $stint->session?->session_type }}
                            </td>

                            <td class="px-6 py-4 text-[var(--text)] font-medium">
                                {{ $stint->laps->count() }}
                            </td>

                            <td class="px-6 py-4 text-[var(--value-info)] font-medium">
                                {{ lapTime($stint->avg_lap,3) }} s
                            </td>

                            <td class="px-6 py-4 text-[var(--warning)] font-medium">
                                {{ number_format($stint->avg_fuel,3) }} L/lap
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7"
                                class="px-6 py-8 text-center text-gray-400">
                                No hay stints registrados para esta combinación.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>
    {{ $stints->links() }}
{{-- Acción volver --}}
<div class="mt-8">
                    <x-ui.button variant="exit" href="{{ route('series.show', $series) }}">
                    <x-heroicon-o-arrow-turn-left-up class="w-4 h-4 mr-2"/> Back
                    </x-ui.button>

</div>
</div>


@endsection

