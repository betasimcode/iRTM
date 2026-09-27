@extends($layout)

@section('title', 'Team Preview')

@php
    $team = $team ?? auth()->user()?->team;
@endphp

@section('content')

    <div class="space-y-6">

        <div>
            <h1 class="text-2xl font-semibold">
                Team Site Preview
            </h1>

            <p class="mt-2 opacity-70">
                {{ $layoutName }}
            </p>
        </div>

        <div class="grid grid-cols-3 gap-6">

            <div class="border rounded-xl p-6">
                HEADER
            </div>

            <div class="border rounded-xl p-6">
                SIDEBAR
            </div>

            <div class="border rounded-xl p-6">
                CONTENT
            </div>

        </div>

    </div>

@endsection
