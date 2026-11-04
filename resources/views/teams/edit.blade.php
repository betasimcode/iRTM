@extends('layouts.app')

@section('title', __('ui.edit_team'))
@section('page-title', __('ui.edit_team'))

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
{{ __('ui.team_name') }}
</label>

<input type="text"
       name="name"
       value="{{ $team->name }}"
       required
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>

<div>

<label class="block text-sm text-[var(--text)] mb-2">
{{ __('ui.short_name') }}
</label>

<input type="text"
       name="short_name"
       value="{{ $team->short_name }}"
       required
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>



<div>

<label class="block text-sm text-gray-400 mb-2">
{{ __('ui.logo') }}
</label>

<input type="file"
       name="logo"
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>

<div>

<label class="block text-sm text-gray-400 mb-2">
{{ __('ui.logo_light') }}
</label>

<input type="file"
       name="logo_light"
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>

<div>

<label class="block text-sm text-gray-400 mb-2">
{{ __('ui.logo_dark') }}
</label>

<input type="file"
       name="logo_dark"
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>


<div>

<label class="block text-sm text-gray-400 mb-2">
{{ __('ui.banner') }}
</label>

<input type="file"
       name="banner"
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>

<div>

<label class="block text-sm text-gray-400 mb-2">
{{ __('ui.banner_dark') }}
</label>

<input type="file"
       name="banner_dark"
       class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--input-text)]">

</div>

<div class="flex justify-end gap-4">

<a href="{{ route('teams.index') }}"
   class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-white">

{{ __('ui.cancel') }}

</a>

<button
    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white">

{{ __('ui.save_team') }}

</button>

</div>

</form>

</div>

@endsection
