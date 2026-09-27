<table id="tyres" style="margin-bottom: 5px">

    <colgroup>

        <col style="width:calc(var(--col) * 1)">

        <col style="width:calc(var(--col) * 1)">
        <col style="width:calc(var(--col) * 1)">
        <col style="width:calc(var(--col) * 1)">

        <col style="width:calc(var(--col) * 1)">
        <col style="width:calc(var(--col) * 1)">
        <col style="width:calc(var(--col) * 1)">

        <col style="width:calc(var(--col) * 1)">

        <col style="width:calc(var(--col) * 1)">
        <col style="width:calc(var(--col) * 1)">
        <col style="width:calc(var(--col) * 1)">

    </colgroup>

    <tr>

        <td class="label vertical" rowspan="7">

            TYRE<br>
            START SETUP

        </td>

        <td class="label">TYRE FL</td>
        <td class="label">TYRE FR</td>

        <td class="label vertical" rowspan="7">

            TYRE<br>
            END SETUP

        </td>

        <td class="label">TYRE FL</td>
        <td class="label">TYRE FR</td>

        <td class="label vertical" rowspan="8">

            TYRE<br>
            ANALYSIS<br>
            (WEAR)

        </td>

        <td class="label-sm">FRONT LEFT</td>
        <td class="labelmin">Outer ºC</td>
        <td class="labelmin">Middle ºC</td>
        <td class="labelmin">Inner ºC</td>

    </tr>

    <tr>

        <td class="labelmin">Pressure</td>
        <td class="labelmin">Pressure</td>

        <td class="labelmin">Pressure</td>
        <td class="labelmin">Pressure</td>

        <td class="info">{{ $telemetry['fl']['wear'] ?? '' }}%</td>
        <td class="info">{{ $telemetry['fl']['outer'] ?? '' }}</td>
        <td class="info">{{ $telemetry['fl']['middle'] ?? '' }}</td>
        <td class="info">{{ $telemetry['fl']['inner'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="info">{{ $values['FL_TYRE']['PRESSURE'] ?? '' }}</td>
        <td class="info">{{ $values['FR_TYRE']['PRESSURE'] ?? '' }}</td>

        <td class="info">{{ $values['RL_TYRE']['PRESSURE'] ?? '' }}</td>
        <td class="info">{{ $values['RR_TYRE']['PRESSURE'] ?? '' }}</td>

        <td class="label-sm">FRONT RIGHT</td>
        <td class="labelmin">Inner ºC</td>
        <td class="labelmin">Middle ºC</td>
        <td class="labelmin">Outer ºC</td>

    </tr>

    <tr>

        <td class="labelmin">Temp</td>
        <td class="labelmin">Temp</td>

        <td class="labelmin">Temp</td>
        <td class="labelmin">Temp</td>

        <td class="info">{{ $telemetry['fr']['wear'] ?? '' }}%</td>
        <td class="info">{{ $telemetry['fr']['outer'] ?? '' }}</td>
        <td class="info">{{ $telemetry['fr']['middle'] ?? '' }}</td>
        <td class="info">{{ $telemetry['fr']['inner'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="info">44C</td>
        <td class="info">44C</td>
        <td class="info">44C</td>
        <td class="info">44C</td>

        <td class="label-sm">REAR LEFT</td>
        <td class="labelmin">Outer ºC</td>
        <td class="labelmin">Middle ºC</td>
        <td class="labelmin">Inner ºC</td>

    </tr>

    <tr>

        <td class="labelmin">Wear</td>
        <td class="labelmin">Wear</td>

        <td class="labelmin">Wear</td>
        <td class="labelmin">Wear</td>

        <td class="info">{{ $telemetry['rl']['wear'] ?? '' }}%</td>
        <td class="info">{{ $telemetry['rl']['outer'] ?? '' }}</td>
        <td class="info">{{ $telemetry['rl']['middle'] ?? '' }}</td>
        <td class="info">{{ $telemetry['rl']['inner'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="info">100%</td>
        <td class="info">100%</td>

        <td class="info">100%</td>
        <td class="info">100%</td>

        <td class="label-sm">REAR RIGHT</td>
        <td class="labelmin">Inner ºC</td>
        <td class="labelmin">Middle ºC</td>
        <td class="labelmin">Outer ºC</td>

    </tr>
     <tr>

        <td class="labelmin" colspan="6">----------------------------------------------------------------------------------------------------------------</td>
        <td class="info">{{ $telemetry['rr']['wear'] ?? '' }}%</td>
        <td class="info">{{ $telemetry['rr']['outer'] ?? '' }}</td>
        <td class="info">{{ $telemetry['rr']['middle'] ?? '' }}</td>
        <td class="info">{{ $telemetry['rr']['inner'] ?? '' }}</td>

</table>
