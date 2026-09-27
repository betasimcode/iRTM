@extends('layouts.app')

@section('title', 'iRTM Team Competitions')
@section('page-title', 'Register Team Competition')

@section('content')

<div class="space-y-8">

    <div>

        <h2 class="text-2xl font-bold text-[var(--text)]">
            Register Team Competition
        </h2>

    </div>

    @include(
        'competitions.partials.browser',
        [
            'competitions' => $series,
            'registerRoute' => 'team.competitions.register',
        ]
    )

</div>

@endsection
