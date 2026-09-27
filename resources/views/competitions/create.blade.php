@extends('layouts.app')

@section('title', 'iRTM Competitions')
@section('page-title', 'Competitions')

@section('content')

<div class="space-y-4">

    <div>

        <h2 class="text-2xl font-bold text-[var(--text)]">
            Available Competitions
        </h2>

        <p class="text-sm text-[var(--text-muted)] mt-1">
            {{ count($series) }} iRacing {{ __('ui.series') }}
        </p>

    </div>

<hr class="border-[var(--border)]">
    @include('competitions.partials.browser', ['competitions' => $series,])

</div>

@endsection
