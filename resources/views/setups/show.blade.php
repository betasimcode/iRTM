@extends('layouts.app')

@section('title', 'Detalle Stint')

@section('page-title', $stint->created_at . ' - ' . $stint->user?->name. ' - ' .  $stint->track->display_name. ' - ' . $stint->car_name)

@section('content')

        @include('setups.layouts.' . $stint->car_id)


@endsection
