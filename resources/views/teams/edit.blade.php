@extends('layouts.app')

@section('title','Editar Equipo')
@section('page-title','Editar Equipo')

@section('content')

<div class="max-w-2xl mx-auto py-8">

<form method="POST"
      action="{{ route('teams.update',$team) }}"
      enctype="multipart/form-data"
      class="bg-[var(--card)] border-[var(--border)] [box-shadow:var(--card-shadow)] rounded-xl p-8 space-y-6">

@csrf
@method('PUT')

<div>

<label class="block text-sm text-[var(--text)] mb-2">
Nombre del equipo
</label>

<input type="text"
       name="name"
       value="{{ $team->name }}"
       required
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>

<div>

<label class="block text-sm text-[var(--text)] mb-2">
Nombre corto
</label>

<input type="text"
       name="short_name"
       value="{{ $team->short_name }}"
       required
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>



<div>

<label class="block text-sm text-gray-400 mb-2">
Logo
</label>

<input type="file"
       name="logo"
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>

<div>

<label class="block text-sm text-gray-400 mb-2">
Logo light
</label>

<input type="file"
       name="logo_light"
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>

<div>

<label class="block text-sm text-gray-400 mb-2">
Logo Dark
</label>

<input type="file"
       name="logo_dark"
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>


<div>

<label class="block text-sm text-gray-400 mb-2">
Banner
</label>

<input type="file"
       name="banner"
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>

<div>

<label class="block text-sm text-gray-400 mb-2">
Banner Dark
</label>

<input type="file"
       name="banner_dark"
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>

<div class="flex justify-end gap-4">

<a href="{{ route('teams.index') }}"
   class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white">

Cancelar

</a>

<button
    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white">

Guardar

</button>

</div>

</form>

</div>

@endsection
