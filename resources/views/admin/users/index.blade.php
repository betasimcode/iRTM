@extends('layouts.app')

@section('page-title', __('ui.users'))

@section('content')

<div class="mb-6 flex justify-between">
    <h2 class="text-xl font-semibold">{{ __('ui.users') }}</h2>

    <a href="{{ route('admin.users.create') }}"
       class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-sm">
        {{ __('ui.create_user') }}
    </a>
</div>

<table class="w-full bg-gray-800 rounded-xl overflow-hidden">
    <thead class="bg-gray-700 text-left text-sm text-gray-300">
        <tr>
            <th class="p-4">{{ __('ui.name') }}</th>
            <th>{{ __('ui.email') }}</th>
            <th>{{ __('ui.role') }}</th>
            <th>{{ __('ui.team') }}</th>
            <th></th>
        </tr>
    </thead>
    <tbody class="text-sm">
        @foreach($users as $user)
            <tr class="border-t border-gray-700">
                <td class="p-4">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role }}</td>
                <td>{{ $user->team?->name }}</td>
                <td class="text-right pr-4">
                    <a href="{{ route('admin.users.edit',$user) }}"
                       class="text-blue-400">{{ __('ui.edit') }}</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-6">
    {{ $users->links() }}
</div>

@endsection
