@extends('layouts.app')

@section('title','Crear Equipo')
@section('page-title','Crear Equipo')

@section('content')

<div class="max-w-2xl mx-auto py-8">

<form method="POST"
      action="{{ route('teams.store') }}"
      enctype="multipart/form-data"
      class="bg-gray-800 border border-gray-700 rounded-xl p-8 space-y-6">

@csrf

<div>

<label class="block text-sm text-gray-400 mb-2">
Nombre del equipo
</label>

<input type="text"
       name="name"
       required
       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">

</div>

<div>

<label class="block text-sm text-gray-400 mb-2">
Logo
</label>

<input type="file"
       name="logo"
       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">

</div>

<div>

<label class="block text-sm text-gray-400 mb-2">
Banner
</label>

<input type="file"
       name="banner"
       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">

</div>

<div class="flex justify-end gap-4">

<a href="{{ route('teams.index') }}"
   class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white">

Cancelar

</a>

<button
    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white">

Crear Equipo

</button>

</div>

</form>

</div>

@endsection
