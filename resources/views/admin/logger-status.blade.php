<table border="1" cellpadding="10">
    <tr>
        <th>Driver</th>
        <th>User</th>
        <th>Versión</th>
        <th>Última conexión</th>
        <th>Estado</th>
    </tr>

    @foreach($drivers as $driver)
        <tr>
            <td>{{ $driver->name }}</td>
            <td>{{ $driver->user->name ?? '—' }}</td>
            <td>{{ $driver->logger_version ?? 'Desconocida' }}</td>
            <td>{{ $driver->last_logger_ping }}</td>
            <td>
                @if($driver->last_logger_ping && $driver->last_logger_ping->gt(now()->subMinutes(5)))
                    🟢 Activo
                @else
                    🔴 Offline
                @endif
            </td>
        </tr>
    @endforeach
</table>
