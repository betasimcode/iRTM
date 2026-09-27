@extends('layouts.app')

@section('page_title', $series->name . ' · Strategy')

@section('content')

<div class="max-w-6xl mx-auto py-10 space-y-10">

    @include(
        'series.plan',
        [
            'series' => $series,
            'plan' => $plan,
        ]
    )

</div>

@endsection
