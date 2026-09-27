<table id="summary" style="margin-bottom: 5px">

    <colgroup>

        <col style="width:calc(var(--col) * 2)">
        <col style="width:calc(var(--col) * 2)">
        <col style="width:calc(var(--col) * 2)">
        <col style="width:calc(var(--col) * 2)">
        <col style="width:calc(var(--col) * 3)">

    </colgroup>

    <tr>

        <td class="label">AIR TEMP</td>
        <td class="label">TRACK TEMP</td>
        <td class="label">SURFACE</td>
        <td class="label">WIND</td>
        <td class="label">AVG LAPTIME</td>

    </tr>

    <tr>

        <td class="info">{{ $summary['air_temp'] }}</td>
        <td class="info">{{ $summary['track_temp'] }}</td>
        <td class="info">{{ $summary['surface'] }}</td>
        <td class="info">{{ $summary['wind'] }}</td>
        <td class="info">{{ $summary['avg_laptime'] }}</td>

    </tr>

    <tr>

        <td class="label">AVG CONSUMPTION</td>
        <td class="label">DURATION</td>
        <td class="label">TOTAL LAPS</td>
        <td class="label">BEST LAP</td>

        <td class="label">
            BEST LAPTIME
        </td>

    </tr>

    <tr>

        <td class="info">{{ $summary['avg_consumption'] }}</td>
        <td class="info">{{ $summary['duration'] }}</td>
        <td class="info">{{ $summary['laps'] }}</td>
        <td class="info">{{ $summary['best_lap'] }}</td>

        <td class="bestlap" rowspan="2">

            {{ $summary['best_laptime'] }}

        </td>

    </tr>

    <tr>

        <td class="label">

            SETUP CODE NAME

        </td>

        <td class="info" colspan="3">

            {{ $summary['setup_code'] }}

        </td>

    </tr>

</table>
