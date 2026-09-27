@extends('layouts.app')

@section('title', 'iRTM Team Competition Registration')
@section('page-title', 'Team Competition Registration')

@section('content')

<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- HEADER --}}
    <div class="mb-8">

        <p class="text-sm uppercase tracking-[0.18em]
                  text-[var(--text-muted)] mb-2">
            Team Competition Registration
        </p>

        <div class="flex items-center gap-3 text-[var(--text-soft)] text-sm">

            <span class="text-3xl font-semibold">
                {{ $series->season_year }}
                Season {{ $series->season_number }}
            </span>

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

                <div class="flex flex-wrap mt-4 gap-2">

                    @if($series->iracingSeries?->category)

                        <span class="px-3 py-1 rounded-md
                                     border border-[var(--border)]
                                     bg-[var(--bg)]
                                     text-sm">
                            {{ $series->iracingSeries->category }}
                        </span>

                    @endif

                    @if($series->iracingSeries?->discipline)

                        <span class="px-3 py-1 rounded-md
                                     border border-[var(--border)]
                                     bg-[var(--bg)]
                                     text-sm">
                            {{ $series->iracingSeries->discipline }}
                        </span>

                    @endif

                </div>

            </div>

        </div>

    </section>


    {{-- REGISTRATION --}}
    <form
        method="POST"
        action="{{ route(
            'team.competitions.register.store',
            [
                'series' => $series,
            ]
        ) }}"
    >

        @csrf


        {{-- COMPETITION CAR --}}
        <section class="mt-10">

            <p class="text-sm uppercase tracking-widest
                      text-[var(--text-muted)]">
                Competition Car
            </p>

            <h2 class="mt-1 text-2xl font-semibold
                       text-[var(--text)]">
                Select your team's car
            </h2>

            <div class="grid md:grid-cols-2 gap-4 mt-5">

                @forelse(
                    $series->iracingSeries?->cars ?? []
                    as $car
                )

                    <label class="cursor-pointer">

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
                                        class="w-20 h-14 object-contain"
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


        {{-- DATA POLICY --}}
        <section class="mt-10">

            <h2 class="text-2xl font-semibold
                       text-[var(--text)]">
                Information sharing
            </h2>

            <p class="mt-1 text-sm
                      text-[var(--text-muted)]">
                Choose how competition data will be shared
                between team drivers.
            </p>


            <div
                class="mt-5 rounded-xl
                       border border-[var(--border)]
                       bg-[var(--card)]
                       p-5 space-y-4"
            >

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

                        <div class="text-sm
                                    text-[var(--text-muted)]">
                            Team drivers can access each other's
                            session and stint data.
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

                        <div class="text-sm
                                    text-[var(--text-muted)]">
                            Drivers can only access their own
                            session and stint data.
                        </div>

                    </div>

                </label>

            </div>


            <div
                class="mt-4 rounded-lg
                       border border-[var(--border)]
                       p-4 text-sm
                       text-[var(--text-muted)]"
            >

                <strong class="text-[var(--text)]">
                    Drivers are managed after registration.
                </strong>

                Registering the team does not automatically
                register you or any other driver.

            </div>

        </section>


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
                Team Competition Commitment
            </h2>

            <div
                class="mt-6 rounded-2xl
                       border border-[var(--border)]
                       bg-[var(--card)]
                       p-6 leading-7
                       text-[var(--text-soft)]"
            >

                <p>
                    By registering your team for this competition,
                    you confirm that you have reviewed the
                    competition information and official calendar.
                </p>

                <p class="mt-4">
                    Your registration represents your team's intention
                    to participate in this competition using the
                    selected competition car.
                </p>

            </div>


            <label class="mt-6 flex items-start gap-3 cursor-pointer">

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
                    and I accept this team participation commitment.
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
            class="mt-10 flex flex-col md:flex-row
                   gap-3 justify-end"
        >

            <a
                href="{{ route('team.competitions.create') }}"
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
                Register Team
            </button>

        </div>

    </form>

</div>

@endsection
