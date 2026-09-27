@extends('layouts.app')

@section('title', 'Stints')
@section('page-title', 'session'. ' ' .  $session->iracing_subsession_id)

@section('content')

<div class="bg-[var(--bg)] border border-[var(--border)] rounded-3xl p-12 space-y-10">

<div class="block text-[var(--text)] mb-5 px-6 pb-5 rounded-lg p-2 transition">
    <div class="ml-0 grid-flow-col flex items-center gap-4">

        <table class="m-0"><thead>
            <tr>
            <td class="w-40 px-2">
                {{-- LOGO --}}
        @if($session->series?->logo_path)
            <img src="{{ asset('storage/'.$session->series->logo_path) }}"
                class="h-40 w-auto object-contain">
        @else
            <div class="h-12 w-12 bg-gray-700 rounded"></div>
        @endif
        @php
        $firstStint = $session->stints->first();
        @endphp

            </td>

            <td class="w-96 px-2">

                        {{-- TITULO --}}
        <div class="flex-1">

            <div class="text-[var(--card-title)] font-semibold text-md leading-tight uppercase">
                {{ $session->series?->short_name ?? 'Unknown Series' }}
            </div>

            <div class="text-[var(--text-soft)] text-sm">
                {{ \Carbon\Carbon::parse($session->created_at)->format('d M H:i') }}
            </div>

        </div>

            </td>
            <td class=" w-96 px-2">

                <div class=" flex-auto">

                    <div class="text-[var(--text)] font-semibold text-lg leading-tight">
                        <img src="{{ asset('storage/' . themedLogo($firstStint->track)) }}"
                        class=" h-40 w-auto object-contain">
                    </div>
                </div>
            </td>

            </tr></thead>
        </table>

    </div>

{{-- FOOTER --}}
<div class="grid-flow-col grid-cols-2 mt-4 justify-between items-center text-sm">

    {{-- BADGES --}}
    <div class="flex gap-3">
        <span class="w-32 h-5 max-h-5 text-center px-2 bg-[var(--badge)] text-[var(--badge-text)] hover:text-[var(--text-link)] border border-[var(--badge-b)] rounded text-xs uppercase">
            <a href="https://members-ng.iracing.com/web/racing/results-stats/results?subsessionid={{ $session->iracing_subsession_id }} ">
            {{ $session->iracing_subsession_id ?? 'Unknown Series' }}
        </a>
        </span>
        <span class="w-56 max-h-5 text-center px-2 bg-[var(--badge)] text-[var(--badge-text)] border border-[var(--badge-b)] rounded text-xs uppercase">
            {{ $firstStint->car_name }}
        </span>
        <span class="w-72 max-h-5 text-center px-2 bg-[var(--badge)] text-[var(--badge-text)] border border-[var(--badge-b)] rounded text-xs uppercase">
            {{ $firstStint->track->display_name }}
        </span>

        {{-- SETUP --}}
        @if($session->series)
                @if($session->series->setup_type === "open")
                <span title="TThe sessions marked as PRO indicate that the competition establishes setup OPEN" class="w-28 max-h-5 text-center px-2 rounded text-xs bg-[var(--A)] border border-[var(--badge-b)] text-yellow-200">
                    PRO
            </span>
                    @else
            <span title="The sessions marked as AMATEUR indicate that the competition establishes FIXED setup for all competitors." class="w-28 max-h-5 text-center px-2 rounded text-xs bg-[var(--D)] border border-[var(--badge-b)] text-[var(--value-info)]">
                AMATEUR
                @endif
            </span>
        @endif

        {{-- LICENSE --}}
        @if($session->series?->iracing_class)
            @if($session->series?->iracing_class ==='D')
                <span class="w-8 max-h-5 text-center bg-[var(--badge)] border border-[var(--badge-b)] text-white rounded text-xs">
                    {{ $session->series?->iracing_class }}
                </span>
            @elseif($session->series?->iracing_class ==='C')
                <span class="w-8 max-h-5 text-center bg-[var(--C)] border border-[var(--badge-b)] text-gray-900 rounded text-xs">
                    {{ $session->series?->iracing_class }}
                </span>
            @elseif($session->series?->iracing_class ==='B')
                <span class="w-8 max-h-5 text-center bg-[var(--B)] border border-[var(--badge-b)] text-white rounded text-xs">
                    {{ $session->series?->iracing_class }}
                </span>
            @elseif($session->series?->iracing_class ==='A')
                <span class="w-8 max-h-5 text-center bg-[var(--A)] border border-[var(--badge-b)] text-white rounded text-xs">
                    {{ $session->series?->iracing_class }}
                </span>
            @else($session->series?->iracing_class ==='P')
                <span class="w-8 max-h-5 text-center bg-[var(--P)] border border-[var(--badge-b)] text-white rounded text-xs">
                    {{ $session->series?->iracing_class }}
            </span>
            @endif
        @endif

        <span class="flex w-52 max-h-5 ml-96 right-5">
            <form

            method="POST"

            action="{{ route('sessions.rebuild', $session) }}"
        >

            @csrf

            <button
                type="submit"
                title="Reprocess session data"
                class="p-2 rounded-md bg-[var(--card)] border border-[var(--border)] text-center max-h-20 max-w-20 mb-5 text-[var(--text)] hover:text-[var(--text-h)]"
            >

                <svg width="32" height="32" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="CurrentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill="CurrentColor" d="M12 0h-7v6h0.7l0.2 0.7 0.1 0.1v-5.8h5v4h4v9h-6l0.3 0.5-0.5 0.5h7.2v-11l-4-4zM12 4v-3l3 3h-3z"></path> <path fill="CurrentColor" d="M5.5 11.5c0 0.552-0.448 1-1 1s-1-0.448-1-1c0-0.552 0.448-1 1-1s1 0.448 1 1z"></path> <path fill="#444" d="M7.9 12.4l1.1-0.4v-1l-1.1-0.4c-0.1-0.3-0.2-0.6-0.4-0.9l0.5-1-0.7-0.7-1 0.5c-0.3-0.2-0.6-0.3-0.9-0.4l-0.4-1.1h-1l-0.4 1.1c-0.3 0.1-0.6 0.2-0.9 0.4l-1-0.5-0.7 0.7 0.5 1.1c-0.2 0.3-0.3 0.6-0.4 0.9l-1.1 0.3v1l1.1 0.4c0.1 0.3 0.2 0.6 0.4 0.9l-0.5 1 0.7 0.7 1.1-0.5c0.3 0.2 0.6 0.3 0.9 0.4l0.3 1.1h1l0.4-1.1c0.3-0.1 0.6-0.2 0.9-0.4l1 0.5 0.7-0.7-0.5-1.1c0.2-0.2 0.3-0.5 0.4-0.8zM4.5 13.5c-1.1 0-2-0.9-2-2s0.9-2 2-2 2 0.9 2 2c0 1.1-0.9 2-2 2z"></path> </g></svg>

            </button>

        </form>
    </span>
</div>





    </div>



</div>

<div class="space-y-0 ">
    <div class="bg-[var(--card)] rounded-t-lg border border-[var(--border)]">
        <h2 class="text-sm font-semibold p-4 text-[var(--text-card-title)]">

            Session benchmarks

        </h2>
    </div>
    <div class="bg-[var(--card-header)] overflow-hidden border border-[var(--border)]">

            <table class="bg-[var(--card-header)] divide-y divide-[var(--div)] min-w-full text-xs">

                <thead class="">

                    <tr class="text-[var(--text-card-title)] uppercase text-xxs tracking-wider">

                        <th class="w-10 text-left p-2">Pos</th>

                        <th class="w-44 text-left p-2">Driver</th>

                        <th class="w-40 text-left p-2">Country</th>

                        <th class="w-52 text-left p-2">Car</th>

                        <th class="w-10 p-2">#</th>

                        <th class="w-24 p-2">Fast Lap</th>

                        <th class="w-16 text-center p-2">iRating</th>

                        <th class="w-20 text-center p-2">Division</th>

                    </tr>

                </thead>

                <tbody class="bg-[var(--bg)] divide-y divide-[var(--div)]">

                    @foreach($session->results as $r)

                    <tr class="transition hover:bg-[var(--hover)] hover:text-[var(--text-h)]">

                        <td class="p-2">
                            {{ $r->class_position+1 }}
                        </td>

                        <td class="p-2">
                            {{ $r->user_name }}
                        </td>
                        <td class="p-2">
                            {{ $r->country }}
                        </td>
                        <td class="p-2">
                            {{ $r->car_name }}
                        </td>

                        <td class="p-2 text-center">
                            {{ $r->car_number }}
                        </td>

                        <td class="p-2 text-center font-mono-timing">
                            {{ laptime($r->fastest_time, 3) }}
                        </td>

                        <td class="p-2 text-center">
                            {{ $r->irating }}
                        </td>

                        <td class="p-2 text-center">
                            {{ $r->division_name }}
                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>


    </div>


</div>


        @foreach($session->stints->sortBy('created_at') as $stint)
        <a href="{{ route('stints.show', $stint->id) }}"
        class="block bg-[var(--card)] text-[var(--text)] border-[var(--border)] border mb-2 hover:bg-[var(--card-hover)] rounded-md px-6 pb-5 p-2 transition">

        @php

        $lapCount = $stint->laps->count();

        $avgLap = $stint->laps
            ->where('lap_time', '>', 0)
            ->avg('lap_time');

        $avgFuel = $stint->laps
            ->where('fuel_consumed', '>', 0)
            ->avg('fuel_consumed');

        @endphp

    <div class="flex gap-3 items-center justify-between">

        {{-- LEFT --}}
        <div>

            <div class="text-[var(--text)] font-semibold mb-5">
                Stint #{{ $stint->id }}
            </div>

            <div class="text-xs text-[var(--text-soft)] mt-1">
                <span class="w-10 text-center mx-3 my-4 py-1 px-4 bg-[var(--badge)] border border-[var(--badge-b)] text-[var(--text)]  rounded text-xs">
                {{ $lapCount }} {{ __('ui.laps') }}
                </span>
                <span class="w-10 text-center mx-3 my-4 py-1 px-4 bg-[var(--badge)] border border-[var(--badge-b)] text-[var(--text)]  rounded text-xs">
                    {{ __('ui.avglap') }}

                @if($avgLap)

                    {{ gmdate('i:s', (int)$avgLap) }}.
                    {{ substr(($avgLap - floor($avgLap)), 2, 3) }}

                @else

                    --

                @endif
                </span>
                <span class="w-10 text-center mx-3 my-4 py-1 px-4 bg-[var(--badge)] border border-[var(--badge-b)] text-[var(--text)]  rounded text-xs">
                {{ number_format($avgFuel, 2) }}L/lap
                </span>
            </div>

        </div>

        {{-- RIGHT --}}
        <div class="text-[var(--text-soft)] text-xs">

            {{ $stint->created_at->format('H:i') }}

        </div>

    </div>
        </a>

            @endforeach









@endsection
