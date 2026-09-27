@extends('layouts.app')

@section('title', 'Stints')
@section('page-title',  'Stints' )

@section('content')

<div class="space-y-2 mb-10">

<br>
    <div class="bg-[var(--card)] rounded-md mb-20 overflow-hidden shadow-lg shadow-black/20">

        {{-- Header --}}

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-[var(--bg)] text-[var(--text-h)] uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('ui.driver') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('ui.car') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('ui.subsession') }}</th>
                        <th class="px-4 py-3 text-left"></th>
                        <th class="px-4 py-3 text-left">{{ __('ui.track') }}</th>
                        <th class="px-5 py-3 text-left">{{ __('ui.date') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('ui.type') }}</th>
                        <th class="px-2 py-3 text-left">{{ __('ui.laps') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('ui.avglap') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('ui.avgfuel') }}</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--div)]">

                    @forelse($stints as $stint)

                        <tr
                            onclick="window.location='{{ route('stints.show', [$stint]) }}'"
                            class="hover:bg-[var(--card-hover)] transition cursor-pointer">

                            <td class="px-4 py-4 text-[var(--text)]">
                                {{ $stint->user?->iracing_name ?? '—' }}
                            </td>

                            <td class="px-4 py-4 text-[var(--text)]">
                                {{ $stint->car_name }}
                            </td>

                            <td class="px-6 py-4 text-[var(--text)]">

                                {{ $stint->iracing_subsession_id }}

                            </td>
                            <td class="items-center px-1 py-1 text-[var(--text)]">
                                <img src="{{ asset('storage/' . themedLogo($stint->track)) }}" class=" h-14 w-auto object-contain">


                            <td class="flex px-6 py-4 text-[var(--text)]">
                                <span class="px-2 py-1 rounded">
                                {{ $stint->track?->display_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[var(--text)]">
                                {{ $stint->created_at }}
                            </td>

                            <td class="px-6 py-4 text-[var(--text)] capitalize">
                                {{ $stint->session?->session_type }}
                            </td>

                            <td class="px-6 py-4 text-[var(--text)] font-medium">
                                {{ $stint->laps->count() }}
                            </td>

                            <td class="px-6 py-4  text-[var(--lap-avg)] font-mono-timing font-medium">
                                {{ lapTime($stint->avg_lap,3) }} s
                            </td>

                            <td class="px-6 py-4 text-[var(--fuel)] font-medium">
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
</div>

    {{ $stints->links() }}

    {{-- Acción volver --}}
    <div class="flex mt-8">
                        <x-ui.button variant="exit" href="/">
                        <x-heroicon-o-arrow-turn-left-up class="w-4 h-4 mr-2"/> Back
                        </x-ui.button>




    </div>






@endsection

