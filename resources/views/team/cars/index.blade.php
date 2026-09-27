@extends('layouts.app')

@section('content')

<div class="max-w-2xl mx-auto ">

    <h2 class="text-xl font-bold mb-4">Coches del equipo</h2>

    <a href="{{ route('team-cars.create') }}"
       class="bg-green-600 text-white px-3 py-1 rounded">
        + Añadir coche
    </a>

    <div class="mt-4 space-y-2 ">

        @foreach($teamCars as $tc)
            <div class="p-3 border rounded flex justify-between items-center">

                <div>
                    <strong>{{ $tc->car->name }}</strong>
                    @if($tc->number)
                        #{{ $tc->number }}
                    @endif

                    @if($tc->livery_file)
                        <span class="text-sm text-gray-500">
                            ({{ $tc->livery_file }})
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('team-cars.destroy', $tc) }}">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-600 text-sm">Eliminar</button>
                </form>

            </div>
        @endforeach

    </div>

</div>

@endsection
