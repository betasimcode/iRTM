@extends('teamcenter.layouts.a')

@section('title', 'TeamCenter Preview')

@section('teamcenter-header')
    <div>
        HEADER
    </div>
@endsection

@section('teamcenter-main')

    <div class="flex min-h-[calc(100vh-5rem)] items-center justify-center">

        <div class="text-center">

            <h1
                class="text-3xl font-semibold tracking-wide"
            >
                Dashboard
            </h1>

            <p
                class="mt-2 text-lg text-[var(--text-muted)]"
            >
                {{ $team->name }}
            </p>

        </div>

    </div>

@endsection
