@extends('layouts.app')

@section('title','Series')
@section('page-title', __('ui.champs') )

@section('content')

<div class="flex justify-between items-center mb-6">

    <x-ui.button variant="add" href="{{ route('series.create') }}">
     <x-heroicon-o-plus class="w-4 h-4 mr-2"/> Add
    </x-ui.button>

</div>


@if($series->isEmpty())

<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 text-center text-gray-400">
    {{ __('ui.noseries') }}
</div>

@else


<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

@foreach($series as $serie)

<x-series.card2 :serie="$serie" />

@endforeach

</div>

@endif
<script>
function toggleMenu(id) {
    const menu = document.getElementById('menu-' + id);

    // cerrar otros
    document.querySelectorAll('[id^="menu-"]').forEach(m => {
        if (m !== menu) m.classList.add('hidden');
    });

    menu.classList.toggle('hidden');
}

// cerrar al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="menu-"]') && !e.target.closest('button')) {
        document.querySelectorAll('[id^="menu-"]').forEach(m => m.classList.add('hidden'));
    }
});
</script>
@endsection
