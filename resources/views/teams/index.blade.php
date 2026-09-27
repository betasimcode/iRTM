@extends('layouts.app')

@section('title','Equipos')
@section('page-title','Equipos')

@section('content')

<div class="bg-gray-800 border border-gray-700 rounded-xl shadow overflow-hidden">

    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">

        <div>
            <h2 class="text-lg font-semibold text-white">
                Equipos registrados
            </h2>
            <p class="text-sm text-gray-400">
                Lista de equipos en la plataforma
            </p>
        </div>

        {{-- CREATE TEAM --}}
        @if(auth()->user()->role === 'admin' || auth()->user()->team_id === null)

        <a href="{{ route('teams.create') }}"
           class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white text-sm">

            Crear Equipo

        </a>

        @endif

    </div>

    <div class="overflow-x-auto">

        <table class="min-w-full text-m">

            <thead class="bg-gray-700 text-gray-300 uppercase text-xs">

                <tr>

                    <th class="px-6 py-3 text-left">Logo</th>
                    <th class="px-6 py-3 text-left">Equipo</th>
                    <th class="px-6 py-3 text-left">Miembros</th>
                    <th class="px-6 py-3 text-left">Acciones</th>

                </tr>

            </thead>

            <tbody class="divide-y divide-gray-700">

                @foreach($teams as $team)

                <tr class="hover:bg-gray-700/50 transition">

                    <td class="px-6 py-4">

                        @if($team->logo_path)

                        <img
                            src="{{ asset('storage/'.$team->logo_path) }}"
                            class="h-8 w-auto object-contain">

                        @endif

                    </td>

                    <td class="px-6 py-4 text-white font-medium">
                        {{ $team->name }}
                    </td>
                    <td>
                        <a href="{{ route('teams.members',$team) }}"
                        class="text-green-400 hover:text-green-300">

                        Miembros

                        </a>
                    </td>
                    <td class="px-6 py-4 flex items-center gap-3">

                        @if(auth()->user()?->role === 'admin')

                        <a href="{{ route('teams.edit',$team) }}"
                           class="text-indigo-400 hover:text-indigo-300">

                            Editar

                        </a>

                        <form method="POST"
                              action="{{ route('teams.destroy',$team) }}"
                              onsubmit="return confirm('Eliminar equipo?')">

                            @csrf
                            @method('DELETE')

                            <button class="text-red-400 hover:text-red-300">
                                Eliminar
                            </button>

                        </form>

                        @endif

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

<div class="mt-6">

{{ $teams->links() }}

</div>

@endsection
