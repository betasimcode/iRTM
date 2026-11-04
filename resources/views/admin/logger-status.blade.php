<table border="1" cellpadding="10">
    <tr>
        <th>{{ __('ui.driver') }}</th>
        <th>{{ __('ui.user') }}</th>
        <th>{{ __('ui.version') }}</th>
        <th>{{ __('ui.last_connection') }}</th>
        <th>{{ __('ui.status') }}</th>
    </tr>

    @foreach($drivers as $driver)
        <tr>
            <td>{{ $driver->name }}</td>
            <td>{{ $driver->user->name ?? '—' }}</td>
            <td>{{ $driver->logger_version ?? __('ui.unknown') }}</td>
            <td>{{ $driver->last_logger_ping }}</td>
            <td>
                @if($driver->last_logger_ping && $driver->last_logger_ping->gt(now()->subMinutes(5)))
                    🟢 {{ __('ui.active') }}
                @else
                    🔴 {{ __('ui.offline') }}
                @endif
            </td>
        </tr>
    @endforeach
</table>
