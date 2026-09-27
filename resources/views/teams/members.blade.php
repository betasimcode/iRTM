@extends('layouts.app')

@section('title','Miembros del equipo')
@section('page-title',$team->name)

@section('content')

<div class="bg-gray-800 border border-gray-700 rounded-xl shadow overflow-hidden">

<div class="px-6 py-4 border-b border-gray-700">

<h2 class="text-lg font-semibold text-white">
Miembros del equipo
</h2>

</div>

<div class="overflow-x-auto">

<table class="min-w-full text-sm">

<thead class="bg-gray-700 text-gray-300 uppercase text-xs">

<tr>
<th class="px-6 py-3 text-left">Piloto</th>
<th class="px-6 py-3 text-left">iRacing</th>
<th class="px-6 py-3 text-left">Rol</th>
<th class="px-6 py-3 text-left">Acciones</th>
</tr>

</thead>

<tbody class="divide-y divide-gray-700">

@foreach($members ?? [] as $member)

<tr class="hover:bg-gray-700/50">

<td class="px-6 py-4 text-white">
{{ $member->name }}
</td>

<td class="px-6 py-4 text-gray-300">
{{ $member->iracing_name }}
</td>

<td class="px-6 py-4">

@if($member->driver_role === 'team_owner')

<span class="px-2 py-1 text-xs rounded bg-indigo-600 text-white">
Team Owner
</span>

@else

<span class="px-2 py-1 text-xs rounded bg-gray-600 text-white">
Driver
</span>

@endif

</td>
<td class="px-6 py-4 flex items-center gap-3">

@if(auth()->user()->driver_role === 'team_owner'
    && $member->id !== auth()->id())

<form method="POST"
      action="{{ route('team.members.role',$member) }}">
@csrf
@method('PATCH')

<button
class="text-indigo-400 hover:text-indigo-300 text-sm">

@if($member->driver_role === 'driver')
Promover
@else
Degradar
@endif

</button>

</form>

@endif

@if(in_array(auth()->user()->driver_role,['team_owner','team_director'])
    && $member->driver_role !== 'team_owner')

<form method="POST"
      action="{{ route('team.members.remove',$member) }}"
      onsubmit="return confirm('Eliminar piloto del equipo?')">

@csrf
@method('DELETE')

<button
class="text-red-400 hover:text-red-300 text-sm">

Expulsar

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

@endsection
