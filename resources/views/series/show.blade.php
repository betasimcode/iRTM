@extends('layouts.app')

@section('title', $series->name . ' ' .$series->iracingSeries->name)
{{-- @section('page-title', $series->name . ' ' .  $series->season_label . ' - ' .  $series->car->name ?? 'No car found') --}}

@section('content')
@php
    $rounds = $series->rounds->sortBy('week_start');
    $activeCar = $series->car;
    $activeRound = $rounds->first(fn($r) =>
        $r->week_start &&
        $r->week_end &&
        now()->between(
            \Carbon\Carbon::parse($r->week_start),
            \Carbon\Carbon::parse($r->week_end)
        )
    );
@endphp


<div class="space-y-4 text-gray-900 dark:text-gray-200">

    <div class="flex justify-between items-center">

        <div class="flex items-center space-x-3">

            <x-ui.button variant="gestion" href="{{ route('series.manage', $series) }}">
            <x-heroicon-o-cog-6-tooth class="w-4 h-4 mr-2"/> {{ __('ui.gestion') }}
            </x-ui.button>

            @if($series->status === 'active')
                <span class="px-4 py-2 text-xs rounded bg-green-100 text-green-700 dark:bg-green-600/20 dark:text-green-400">
                    {{ __('ui.active') }}
                </span>
            @endif

        </div>
    </div>

@if($activeRound)

{{-- @include('modules.race-timeline-module', $raceTimeline) --}}

<div class="bg-[var(--bg)] border border-[var(--border)] rounded-3xl p-12 space-y-10">
    <table class="m-0"><thead>
        <tr>

          <td class="w-40 px-2">
            <img src="{{ asset('storage/'.$series->iracingSeries->logo_path) }}" class="w-40" title="{{ $series->iracingSeries->name }}">
          </td>

          <td class=" w-6/12 px-2">
            <p class="text-xs uppercase tracking-widest text-indigo-500 dark:text-indigo-400">
                {{ __('ui.weekac') }}
            </p>

            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mt-3"></h2>

            <p class="text-[var(--text)] mt-2">
                {{ __('ui.week') }} {{ $activeRound->week }}
                · {{ $series->season_label }} {{ $activeRound->track->display_name ?? 'Sin circuito asignado' }} - {{ $activeRound->track->variant}}
            </p>
          </td>
          {{-- {{ dd ($series->team_car_id) }} --}}
          <td class="w-auto px-2">
             {{-- NAME --}}
             @if($series->team_car_id)
             <p class="text-white font-bold">
                <img
                src="{{ asset('storage/'. $series->TeamCar->image_path) }}"
                class="h-40 w-auto object-contain">
            </p>
            @else

                <p class="text-xs text-blue-400">
                    {{ auth()->user()->team->name }}
                </p>
            @endif
          </td>
          <td class="w-auto px-2">
            <p class="text-lg font-semibold text-green-600 dark:text-green-400">
                <img src="{{ asset('storage/' . themedLogo($activeRound->track)) }}" class=" h-40 w-auto object-contain">



          </td>
        </tr></thead>
      </table>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-10">

        <div class="bg-[var(--card)] shadow-md rounded-lg p-2">
            <p class="text-[var(--text-title)] text-xs uppercase">{{ __('ui.totalstint') }}</p>
            <p class="text-2xl font-bold text-[var(--value-data)] mt-2 pl-10">
                {{ $stats['total_stints'] ?? 0 }}
            </p>
        </div>

        <div class="bg-[var(--card)] shadow-md rounded-lg p-2 ">
            <p class="text-[var(--text-title)] text-xs uppercase">{{ __('ui.bestlap') }}</p>
            <p class="text-2xl font-bold text-[var(--lap-best)] mt-2 pl-10">
                {{ data_get($stats, 'best_lap') ? lapTime(data_get($stats, 'best_lap')) : '—' }}
            </p>
        </div>

        <div class="bg-[var(--card)] shadow-md rounded-lg p-2">
            <p class="text-[var(--text-title)] text-xs uppercase">{{ __('ui.timingtarget') }}</p>
            <p class="text-2xl font-bold text-[var(--value-info)] mt-2 pl-10">
                {{ data_get($stats, 'representative_pace') ? lapTime(data_get($stats, 'representative_pace')) : '—' }}
            </p>
        </div>

        <div class="bg-[var(--card)] shadow-md rounded-lg p-2">
            <p class="text-[var(--text-title)] text-xs uppercase">{{ __('ui.avg_cons') }}</p>
            <p class="text-2xl font-bold text-[var(--value-data)] mt-2 pl-10">
                {{ data_get($stats,'avg_fuel') ? number_format($stats['avg_fuel'],3).' L' : '—' }}
            </p>
        </div>

    </div>

    <div class="bg-[var(--card)]  shadow-md border-[var(--border)] rounded-2xl p-6">

        <div class="flex justify-between items-center">

            <div>
                <p class="text-xs uppercase text-[var(--text-title)] ">
                    {{ __('ui.bestlong') }}
                </p>
                <p class="text-xl font-semibold text-[var(--value-data)]  mt-2">
                    {{ $stats['long_run_best'] ?? '—' }} {{ __('ui.laps') }}
                </p>
            </div>

            <div>
                <form method="GET">
                    <select name="mode"
                            onchange="this.form.submit()"
                            class="bg-[var(--badge)] border-[var(--border)] text-sm rounded pr-10 px-3 py-1 text-[var(--text)]">
                        <option value="week" {{ request('mode')=='week' ? 'selected' : '' }}>
                            {{ __('ui.week') }}
                        </option>
                        <option value="season" {{ request('mode','season')=='season' ? 'selected' : '' }}>
                            {{ __('ui.season') }}
                        </option>
                        <option value="all" {{ request('mode')=='all' ? 'selected' : '' }}>
                            {{ __('ui.historic') }}
                        </option>
                    </select>
                </form>
            </div>

        </div>

    </div>
<div class="mt-6 flex gap-4">
                <div>
                    <x-ui.button variant="info" href="{{ route('series.stints', [$series]) }}">
                    <x-heroicon-o-bars-4 class="w-4 h-4 mr-2"/> {{ __('ui.stints') }}
                    </x-ui.button>
                </div>
                <div>
                    <x-ui.button variant="tec" href="{{ route('series.plan', [$series]) }}">
                    <x-heroicon-o-swatch class="w-4 h-4 mr-2"/> {{ __('ui.strategy') }}
                    </x-ui.button>
                </div>

</div>
@endif

    <div class="space-y-6">

        <h3 class="text-lg font-semibold text-[var(--text)]">
            {{ __('ui.seasoncal') }}
        </h3>

        <div class="bg-[var(--card)] border border-[var(--border)] rounded-2xl overflow-hidden shadow-lg shadow-black/20">

            <table class="min-w-full text-sm">

                <thead class="bg-[var(--card-header)] border-b border-[var(--border)]">
                    <tr class="text-[var(--text-title)] uppercase text-xs tracking-wider">
                        <th class="px-6 py-4 text-left">{{ __('ui.week') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('ui.startdate') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('ui.track') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('ui.format') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--div)]">

                    @foreach($rounds as $r)
                    <tr class="hover:bg-[var(--card-hover) transition">

                        <td class="px-6 py-4 text-[var(--text)]">
                            {{ __('ui.week') }} {{ $r->week }}
                        </td>

                        <td class="px-6 py-4 text-[var(--text-soft)]">
                            {{ $r->week_start ?? '-' }}
                        </td>

                        <td class="px-6 py-4 text-[var(--text)]">
                            {{ $r->track->display_name }} - {{ $r->track->variant }}
                        </td>

                        <td class="px-6 py-4 text-[var(--text-soft)]">
                            {{ $r->race_type == 'time'
                                ? $r->race_length.' min'
                                : $r->race_length.' vueltas' }}
                        </td>

                    </tr>
                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

<div class="mt-8">
    <x-ui.button variant="exit" href="{{ route('series.index', $series) }}">
    <x-heroicon-o-arrow-left-start-on-rectangle class="w-4 h-4 mr-2"/> {{ __('ui.exit') }}
    </x-ui.button>

</div>
</div>

@endsection
