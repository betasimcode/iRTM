@extends('layouts.app')

@section('title', 'iRTM ' . __('ui.champs'))
@section('page-title', __('ui.champs'))

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- HEADER --}}
    <div class="mb-8">

        <p class="text-sm uppercase tracking-[0.18em]
                text-[var(--text-muted)] mb-2">
            Competition Registration
        </p>



        <div class="flex w-100 mt-3 gap-x-3 text-[var(--text-soft)] text-sm">
        <span class=" text-3xl font-semibold mr-4">
                {{ $series->season_year }}
                Season {{ $series->season_number }}
        </span>
            <div class="flex pt-2">
                @if($competitionStart && $competitionEnd)

                <svg width="24" height="24" viewBox="0 0 1024 1024" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M106.666667 810.666667V298.666667h810.666666v512c0 46.933333-38.4 85.333333-85.333333 85.333333H192c-46.933333 0-85.333333-38.4-85.333333-85.333333z" fill="CurrentColor"></path><path d="M917.333333 213.333333v128H106.666667v-128c0-46.933333 38.4-85.333333 85.333333-85.333333h640c46.933333 0 85.333333 38.4 85.333333 85.333333z" fill="CurrentColor"></path><path d="M704 213.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z" fill="#B71C1C"></path><path d="M320 213.333333m-64 0a64 64 0 1 0 128 0 64 64 0 1 0-128 0Z" fill="#B71C1C"></path><path d="M704 64c-23.466667 0-42.666667 19.2-42.666667 42.666667v106.666666c0 23.466667 19.2 42.666667 42.666667 42.666667s42.666667-19.2 42.666667-42.666667V106.666667c0-23.466667-19.2-42.666667-42.666667-42.666667zM320 64c-23.466667 0-42.666667 19.2-42.666667 42.666667v106.666666c0 23.466667 19.2 42.666667 42.666667 42.666667s42.666667-19.2 42.666667-42.666667V106.666667c0-23.466667-19.2-42.666667-42.666667-42.666667z" fill="#B0BEC5"></path><path d="M277.333333 426.666667h85.333334v85.333333h-85.333334zM405.333333 426.666667h85.333334v85.333333h-85.333334zM533.333333 426.666667h85.333334v85.333333h-85.333334zM661.333333 426.666667h85.333334v85.333333h-85.333334zM277.333333 554.666667h85.333334v85.333333h-85.333334zM405.333333 554.666667h85.333334v85.333333h-85.333334zM533.333333 554.666667h85.333334v85.333333h-85.333334zM661.333333 554.666667h85.333334v85.333333h-85.333334zM277.333333 682.666667h85.333334v85.333333h-85.333334zM405.333333 682.666667h85.333334v85.333333h-85.333334zM533.333333 682.666667h85.333334v85.333333h-85.333334zM661.333333 682.666667h85.333334v85.333333h-85.333334z" fill="#90A4AE"></path></g></svg>

                    <span class="pt-0.5 ml-2">
                        {{ \Carbon\Carbon::parse($competitionStart)->format('d M Y') }}
                        to
                        {{ $competitionEnd->format('d M Y') }}
                    </span>

                @endif
            </div>
        </div>

    </div>


    {{-- IDENTITY --}}
    <section
        class="rounded-2xl
               border border-[var(--border)]
               bg-[var(--card)]
               p-6"
    >

        <div class="flex flex-col md:flex-row
                    items-center gap-6">

            @if($series->iracingSeries?->logo_path)

                <img
                    src="{{ asset(
                        'storage/' .
                        $series->iracingSeries->logo_path
                    ) }}"
                    alt="{{ $series->name }}"
                    class="w-52 h-auto object-contain"
                >

            @endif

            <div>

                <h2 class="text-3xl font-bold
                           text-[var(--text-card-title)]">
                    {{ $series->name }}
                </h2>

                @if($series->iracingSeries?->short_name)

                    <p class="mt-2 text-lg
                              text-[var(--text-soft)]">
                        {{ $series->iracingSeries->short_name }}
                    </p>

                @endif

                <div class="flex flex-wrap mt-4">

                @if($series->iracingSeries?->iracing_class == 'A' )
                <span
                    class="text-center
                        rounded-l-md
                        border-l
                        border-t
                        border-b
                        border-[var(--{{ $series->iracingSeries?->iracing_class }})]
                        bg-[var(--bg)]
                        px-3 py-1.5
                        text-xs font-medium
                        text-[var(--{{ $series->iracingSeries?->iracing_class }})]"
                >
                    CLASS
                </span>

                <span
                    class="
                            w-10
                            text-center
                        rounded-r-md
                        border border-[var(--{{ $series->iracingSeries?->iracing_class }})]
                        bg-[var(--{{ $series->iracingSeries?->iracing_class }})]
                        px-3 py-1.5
                        mr-2
                        text-xs font-medium
                        text-white"
                >
                    {{ $series->iracingSeries?->iracing_class }}
                </span>

    @elseif($series->iracingSeries?->iracing_class == 'ROOKIE' )
                <span
                    class="text-center
                        rounded-l-md
                        border-l
                        border-t
                        border-b
                        border-[var(--{{ $series->iracingSeries?->iracing_class }})]
                        bg-[var(--bg)]
                        px-3 py-1.5
                        text-xs font-medium
                        text-[var(--{{ $series->iracingSeries?->iracing_class }})]"
                >
                    CLASS
                </span>

                <span
                    class="
                            w-10
                            text-center
                        rounded-r-md
                        border border-[var(--{{ $series->iracingSeries?->iracing_class }})]
                        bg-[var(--{{ $series->iracingSeries?->iracing_class }})]
                        px-3 py-1.5
                        mr-2
                        text-xs font-medium
                        text-white"
                >
                    R
                </span>

            @else
                <span
                    class="text-center
                        rounded-l-md
                        border-l
                        border-t
                        border-b
                        border-[var(--{{ $series->iracingSeries?->iracing_class }})]
                        bg-[var(--bg)]
                        px-4 py-1.5
                        text-xs font-medium
                        text-[var(--{{ $series->iracingSeries?->iracing_class }})]"
                >
                    CLASS
                </span>
                <span
                    class=" text-center
                        w-10
                        rounded-r-md
                        border border-[var(--{{ $series->iracingSeries?->iracing_class }})]
                        bg-[var(--{{ $series->iracingSeries?->iracing_class }})]
                        px-3 py-1.5
                        mr-2
                        text-xs font-medium
                        text-black"
                >
                    {{ $series->iracingSeries?->iracing_class }}
                </span>

            @endif

                    @if($series->iracingSeries?->category)

                        <span class="px-3 py-1 rounded-md
                                     border border-[var(--border)]
                                     bg-[var(--bg)]
                                     mr-2
                                     text-sm">
                            {{ $series->iracingSeries->category }}
                        </span>

                    @endif

                    @if($series->iracingSeries?->discipline)

                        <span class="px-3 py-1 rounded-md
                                     border border-[var(--border)]
                                     bg-[var(--bg)]
                                     mr-2
                                     text-sm">

                            {{ $series->iracingSeries->discipline }}
                        </span>

                    @endif

                </div>

            </div>

        </div>

    </section>


        <div class="mt-4 pb-3 text-sm text-[var(--text)] bg-[var(--bg-base)] border-b border-[var(--border)]">
            {{ $series->iracingSeries->serie_info }}
        </div>

    {{-- COMPETITION INFORMATION --}}
    <section class="mt-10">


        <div class="w-full p-5 rounded-xl border border-[var(--border)] bg-[var(--card)]">
        <h2 class="text-2xl font-semibold
                   text-[var(--text-card-title)]">
            Competition Information
        </h2>
        </div>
        <div class="flex rounded mt-4">

            @if($series->iracingSeries->race_length)
                <h1 class="
                        w-48
                        font-courier
                        font-medium
                        rounded-1-md
                        px-2 py-1
                        border-b
                        border-[var(--border)]
                        bg-[var(--base)]
                        text-[var(--text)]
                        uppercase
                        ">
                    Race start type
                </h1>
                <span
                    class="

                        w-32
                        font-courier
                        rounded-1-md
                        border-b
                        border-[var(--border)]
                        bg-[var(--bg)]
                        px-3 py-1
                        font-medium
                        text-[var(--text)]
                        uppercase">
                @if ($series->iracingSeries->start_type)
                {{ $series->iracingSeries->start_type }}
                @endif
                </span>

            @endif

        </div>

        <div class="flex rounded mt-4">

            @if($series->iracingSeries->race_length)
                <h1 class="
                        w-48
                        font-courier
                        font-medium
                        rounded-1-md
                        px-2 py-1
                        border-b
                        border-[var(--border)]
                        bg-[var(--base)]
                        text-[var(--text)]
                        uppercase
                        ">
                    Race type
                </h1>
                <span
                    class="
                        w-32
                        font-courier
                        rounded-1-md
                        border-b
                        border-[var(--border)]
                        bg-[var(--bg)]
                        px-3 py-1
                        font-medium
                        text-[var(--text)]
                        uppercase"
                >@if ($series->iracingSeries->race_type)
                   By  {{ $series->iracingSeries->race_type }}
                @endif
                </span>

            @endif

        </div>

        <div class="flex rounded mt-4">

            @if($series->iracingSeries->race_length)
                <h1 class="
                        w-48
                        font-courier
                        font-medium
                        rounded-1-md
                        px-2 py-1
                        border-b
                        border-[var(--border)]
                        bg-[var(--base)]
                        text-[var(--text)]
                        uppercase
                        ">
                    Race lengh
                </h1>
                <span
                    class="
                        w-32
                        font-courier
                        rounded-1-md
                        border-b
                        border-[var(--border)]
                        bg-[var(--bg)]
                        px-3 py-1
                        font-medium
                        text-[var(--text)]
                        uppercase"
                >@if ($series->iracingSeries->race_type == 'time')
                    {{ $series->iracingSeries->race_length }} Mins
                @else
                    {{ $series->iracingSeries->race_length }} Laps
                @endif
                </span>

            @endif

        </div>

        <div class="flex rounded mt-4">

            @if($series->iracingSeries->race_length)
                <h1 class="
                        w-48
                        font-courier
                        font-medium
                        rounded-1-md
                        px-2 py-1
                        border-b
                        border-[var(--border)]
                        bg-[var(--base)]
                        text-[var(--text)]
                        uppercase
                        ">
                    Race time interval
                </h1>
                <span
                    class="
                        w-32
                        font-courier
                        rounded-1-md
                        border-b
                        border-[var(--border)]
                        bg-[var(--bg)]
                        px-3 py-1
                        font-medium
                        text-[var(--text)]
                        uppercase"
                >@if ($series->iracingSeries->race_interval_minutes)
                    {{ $series->iracingSeries->race_interval_minutes }} mins
                @endif
                </span>

            @endif

        </div>


    </section>
<br><br>

    {{-- CALENDAR --}}
    <section class="mt-10">

        <div class="flex items-end justify-between">

            <div>

                <p class="text-sm uppercase tracking-widest
                          text-[var(--text-muted)]">
                    Official Calendar
                </p>

                <h2 class="mt-1 text-2xl font-semibold
                           text-[var(--text)]">
                    {{ $series->rounds->count() }} Rounds
                </h2>

            </div>

        </div>


        <div class="mt-5
                    rounded-2xl
                    border border-[var(--border)]
                    overflow-hidden">

            @forelse(
                $series->rounds->sortBy('week')
                as $round
            )

                <div
                    class="grid md:grid-cols-[100px_180px_1fr_auto]
                           gap-4
                           items-center
                           px-5 py-4
                           border-b border-[var(--border)]
                           bg-[var(--card)]
                           last:border-b-0"
                >

                    <div class="font-semibold
                                text-[var(--text)]">
                        Week {{ $round->week }}
                    </div>

                    <div class="text-sm
                                text-[var(--text-soft)]">

                        {{ \Carbon\Carbon::parse(
                            $round->week_start
                        )->format('d M Y') }}

                    </div>

                    <div class="text-[var(--text)]">

                        {{ $round->track?->display_name
                            ?? $round->track?->name
                            ?? 'Track not resolved' }}

                        @if ($round->track?->variant)
                        -
                        {{ $round->track?->variant}}
                        @endif

                    </div>

                    <div class="text-sm
                                text-[var(--text-muted)]">

                        @if($round->race_type === 'time')

                            {{ $round->race_length }} min

                        @else

                            {{ $round->race_length }} laps

                        @endif

                    </div>

                </div>

            @empty

                <div class="p-6 text-center
                            text-[var(--text-muted)]">
                    No rounds available.
                </div>

            @endforelse

        </div>

    </section>


    {{-- DRIVER --}}
    <section class="mt-10">

        <p class="text-sm uppercase tracking-widest
                  text-[var(--text-muted)]">
            Your Entry
        </p>

        <h2 class="mt-1 text-2xl font-semibold
                   text-[var(--text)]">
            Driver
        </h2>

        <div class="mt-4 p-6 rounded-2xl
                    border border-[var(--border)]
                    bg-[var(--card)]">

            <p class="text-xs uppercase
                      text-[var(--text-muted)]">
                Driver
            </p>

            <p class="mt-2 text-xl font-semibold
                      text-[var(--text)]">
                {{ auth()->user()->name }}
            </p>

        </div>

    </section>


    {{-- REGISTRATION --}}
    <form
    method="POST"
    action="{{ route('competitions.register.store', [
        'series' => $series,
    ]) }}"
>

        @csrf

        <section>

            <p class="text-sm uppercase tracking-widest
                      text-[var(--text-muted)]">
                Competition Car
            </p>

            <h2 class="mt-1 text-2xl font-semibold
                       text-[var(--text)]">
                Select your car
            </h2>

            <div class="grid md:grid-cols-2 gap-4 mt-5">

                @forelse(
                    $series->iracingSeries?->cars ?? []
                    as $car
                )

                    <label
                        class="cursor-pointer"
                    >

                        <input
                            type="radio"
                            name="competition_car_id"
                            value="{{ $car->id }}"
                            class="peer sr-only"
                            required
                        >

                        <div
                            class="rounded-xl
                                   border border-[var(--border)]
                                   bg-[var(--card)]
                                   p-5
                                   transition
                                   peer-checked:ring-2
                                   peer-checked:ring-[var(--accent)]
                                   peer-checked:border-[var(--accent)]"
                        >

                            <div class="flex items-center gap-4">

                                @if($car->logo_path)

                                    <img
                                        src="{{ asset(
                                            'storage/' .
                                            $car->logo_path
                                        ) }}"
                                        class="w-20 h-14
                                               object-contain"
                                        alt="{{ $car->name }}"
                                    >

                                @endif

                                <div>

                                    <p class="font-semibold
                                              text-[var(--text)]">
                                        {{ $car->name }}
                                    </p>

                                    @if($car->short_name)

                                        <p class="text-sm
                                                  text-[var(--text-muted)]">
                                            {{ $car->short_name }}
                                        </p>

                                    @endif

                                </div>

                            </div>

                        </div>

                    </label>

                @empty

                    <div class="md:col-span-2
                                p-6 rounded-xl
                                border border-red-400
                                text-red-400">
                        No eligible cars are configured
                        for this competition.
                    </div>

                @endforelse

            </div>

        </section>

{{-- @if($type === 'team')

    <div class="mt-6 rounded-xl border border-[var(--border)] bg-[var(--card)] p-5">

        <h3 class="text-base font-semibold text-[var(--text)]">
            Information sharing
        </h3>

        <p class="mt-1 text-sm text-[var(--text-muted)]">
            Choose how competition data will be shared between team drivers.
        </p>

        <div class="mt-4 space-y-3">

            <label class="flex items-start gap-3 cursor-pointer">

                <input
                    type="radio"
                    name="data_policy"
                    value="shared"
                    checked
                    class="mt-1"
                >

                <div>
                    <div class="font-medium text-[var(--text)]">
                        Shared
                    </div>

                    <div class="text-sm text-[var(--text-muted)]">
                        Team drivers can access each other's session and stint data.
                    </div>
                </div>

            </label>

            <label class="flex items-start gap-3 cursor-pointer">

                <input
                    type="radio"
                    name="data_policy"
                    value="compartmented"
                    class="mt-1"
                >

                <div>
                    <div class="font-medium text-[var(--text)]">
                        Compartmented
                    </div>

                    <div class="text-sm text-[var(--text-muted)]">
                        Drivers can only access their own session and stint data.
                    </div>
                </div>

            </label>

        </div>

        <div class="mt-4 rounded-lg border border-[var(--border)] p-3 text-sm text-[var(--text-muted)]">
            <strong class="text-[var(--text)]">
                Drivers are managed after registration.
            </strong>
            Registering the team does not automatically register you or any other driver.
            Drivers can be added and managed from the competition page.
        </div>

    </div>

@endif --}}

        {{-- COMMITMENT --}}
        <section
            class="mt-12
                   border-t-2
                   border-[var(--border)]
                   pt-10"
        >

            <p class="text-sm uppercase tracking-widest
                      text-[var(--text-muted)]">
                Commitment
            </p>

            <h2 class="mt-1 text-3xl font-bold
                       text-[var(--text)]">
                Competition Commitment
            </h2>

            <div class="mt-6
                        rounded-2xl
                        border border-[var(--border)]
                        bg-[var(--card)]
                        p-6
                        leading-7
                        text-[var(--text-soft)]">

                <p>
                    By registering for this competition,
                    you confirm that you have reviewed the
                    competition information and official
                    calendar shown above.
                </p>

                <p class="mt-4">
                    Your registration represents your intention
                    to participate in this competition as a
                    Driver using the selected car.
                </p>

                <p class="mt-4">
                    Competition rules and participation
                    conditions may be extended here as the
                    iRTM competition system evolves.
                </p>

            </div>


            <label class="mt-6 flex items-start gap-3
                          cursor-pointer">

                <input
                    type="checkbox"
                    name="commitment"
                    value="1"
                    required
                    class="mt-1"
                >

                <span class="text-[var(--text)]">
                    I have reviewed the competition,
                    calendar and registration information
                    and I accept this participation commitment.
                </span>

            </label>

            @error('commitment')

                <p class="mt-2 text-sm text-red-400">
                    {{ $message }}
                </p>

            @enderror

        </section>


        {{-- ACTIONS --}}
        <div
            class="mt-10
                   flex flex-col md:flex-row
                   gap-3
                   justify-end"
        >

            <a
                href="{{ route('competitions.create') }}"
                class="px-6 py-3 rounded-lg
                       border border-[var(--border)]
                       text-[var(--text)]
                       text-center"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="px-8 py-3 rounded-lg
                       bg-[var(--accent)]
                       text-white
                       font-semibold
                       hover:opacity-90"
            >
                Register for Competition
            </button>

        </div>

    </form>

</div>
@endsection
