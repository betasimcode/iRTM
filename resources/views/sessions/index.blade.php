@extends('layouts.app')

@section('title', 'Sesiones')
@section('page-title', __('ui.sessions'))

@section('content')

<div class="w-max grid-cols-1 align-middle m-auto md:grid-cols-1 xl:grid-cols-1 gap-6">

    @foreach($sessions as $session)

    @include('sessions.cards.session')
    
    @endforeach


    {{ $sessions->links() }}
    
</div>


@endsection
