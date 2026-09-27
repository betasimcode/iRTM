@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto py-10">

    <h1 class="text-2xl font-bold text-white mb-6">
        {{ __('ui.new') }}
    </h1>

    <form action="{{ route('series.store') }}"
          method="POST"
          class="bg-gray-800 border border-gray-700 rounded-2xl p-8 space-y-6">

        @csrf

        <div>
            <label class="block text-sm text-gray-400 mb-2">
                {{ __('ui.name') }}
            </label>

            <select name="iracing_series_id"
                required
                class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">

                <option value="">{{ __('ui.selectserie') }}</option>

                @foreach($iracingSeries as $s)

                    <option value="{{ $s->id }}">
                        {{ $s->name }}
                    </option>

                @endforeach

            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-2">
                {{ __('ui.car') }}
            </label>
        
            <select name="team_car_id"
                    required
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
        
                <option value="">{{ __('ui.selectcar') }}</option>
        
                @foreach($teamCars as $teamCar)
                    <option value="{{ $teamCar->id }}">
                        {{ $teamCar->car->name }}
        
                        @if($teamCar->number)
                            #{{ $teamCar->number }}
                        @endif
                    </option>
                @endforeach
        
            </select>
        </div>


        <div>
            <label class="block text-sm text-gray-400 mb-2">
                {{ __('ui.year') }}
            </label>
            <input type="number"
                   name="season_year"
                   required
                   class="w-full bg-gray-900 border border-gray-700
                          rounded-lg px-4 py-2 text-white">
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-2">
                {{ __('ui.season') }}
            </label>

            <select name="season_number"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                <option value="1">S1</option>
                <option value="2">S2</option>
                <option value="3">S3</option>
                <option value="4">S4</option>
            </select>
        </div>



        <div class="flex justify-end space-x-4">
            <a href="{{ route('series.index') }}"
               class="px-4 py-2 bg-gray-700 hover:bg-gray-600
                      text-white rounded-lg">
                {{ __('ui.cancel') }}
            </a>

            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700
                           text-white rounded-lg">
                {{ __('ui.create') }}
            </button>
        </div>

    </form>

</div>

@endsection
