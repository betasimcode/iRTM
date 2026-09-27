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

        <td class="label vertical" rowspan="9">

            MEASUREMENTS <br>   TELEMETRY


        </td>

        <td class="label-sm" colspan="1">FRONT LEFT</td>
        <td class="label-sm" colspan="1">FRONT RIGHT</td>
        <td class="label-sm" colspan="1">REAR LEFT</td>
        <td class="label-sm" colspan="1">REAR RIGHT</td>

        <td class="label vertical" rowspan="9">

            DRIVETRAIN<br>


        </td>

        <td class="label-sm" colspan="2">DIFFERENTIAL</td>
        <td class="label-sm" colspan="3">GEARS</td>

    </tr>

    <tr>

        <td class="labelmin" colspan="1">CORNER WGHT</td>
        <td class="labelmin" colspan="1">CORNER WGHT</td>
        <td class="labelmin" colspan="1">CORNER WGHT</td>
        <td class="labelmin" colspan="1">CORNER WGHT</td>
        <td class="labelmin" colspan="2">CLUTH PLATES</td>
        <td class="labelmin">1</td>
        <td class="info" colspan="2">{{ $values['DRIVE_TRAIN']['GEAR 1'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="info" colspan="1">{{ $values['TELEMETRY_FL']['CORNER WEIGHT'] ?? '' }}</td>
        <td class="info" colspan="1">{{ $values['TELEMETRY_FR']['CORNER WEIGHT'] ?? '' }}</td>
        <td class="info" colspan="1">{{ $values['TELEMETRY_RL']['CORNER WEIGHT'] ?? '' }}</td>
        <td class="info" colspan="1">{{ $values['TELEMETRY_RR']['CORNER WEIGHT'] ?? '' }}</td>
        <td class="value" colspan="2">{{ $values['DIFF']['CLUTH PLATES'] ?? '' }}</td>
        <td class="labelmin">2</td>
        <td class="value" colspan="2">{{ $values['DRIVE_TRAIN']['GEAR 2'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="labelmin" colspan="1">RIDE HEIGHT</td>

        <td class="labelmin" colspan="1">RIDE HEIGHT</td>

        <td class="labelmin" colspan="1">RIDE HEIGHT</td>

        <td class="labelmin" colspan="1">RIDE HEIGHT</td>
        <td class="labelmin" colspan="2">COAST ANGLE</td>
        <td class="labelmin">3</td>
        <td class="value" colspan="2">{{ $values['DRIVE_TRAIN']['GEAR 3'] ?? '' }}</td>
    </tr>

    <tr>

        <td class="info" colspan="1">{{ $values['TELEMETRY_FL']['RIDE HEIGHT'] ?? '' }} mm</td>

        <td class="info" colspan="1">{{ $values['TELEMETRY_FR']['RIDE HEIGHT'] ?? '' }} mm</td>

        <td class="info" colspan="1">{{ $values['TELEMETRY_RL']['RIDE HEIGHT'] ?? '' }} mm</td>

        <td class="info" colspan="1">{{ $values['TELEMETRY_RR']['RIDE HEIGHT'] ?? 'X' }}</td>
        <td class="value" colspan="2">{{ $values['DIFF']['COAST ANGLE'] ?? '' }}</td>
        <td class="labelmin">4</td>
        <td class="value" colspan="2">{{ $values['DRIVE_TRAIN']['GEAR 4'] ?? '' }}</td>
    </tr>

    <tr>

        <td class="labelmin" colspan="1">SHOCK DEF</td>

        <td class="labelmin" colspan="1">SHOCK DEF</td>

        <td class="labelmin" colspan="1">SHOCK DEF</td>

        <td class="labelmin" colspan="1">SHOCK DEF</td>

        <td class="labelmin" colspan="2">PRELOAD</td>
        <td class="labelmin">5</td>
        <td class="value" colspan="2">{{ $values['DRIVE_TRAIN']['GEAR 5'] ?? '' }}</td>
    </tr>

    <tr>

        <td class="info" colspan="1">{{ $values['TELEMETRY_FL']['SHOCK DEF'] ?? '' }}</td>

        <td class="info" colspan="1">{{ $values['TELEMETRY_FR']['SHOCK DEF'] ?? '' }}</td>

        <td class="info" colspan="1">{{ $values['TELEMETRY_RL']['SHOCK DEF'] ?? '' }}</td>

        <td class="info" colspan="1">{{ $values['TELEMETRY_RR']['SHOCK DEF'] ?? '' }}</td>

        <td class="value" colspan="2">{{ $values['DIFF']['DIFF PRELOAD'] ?? '' }}</td>
        <td class="labelmin">6</td>
        <td class="value" colspan="2">{{ $values['DRIVE_TRAIN']['GEAR 6'] ?? '' }}</td>
    </tr>
    <tr>

        <td class="labelmin" colspan="1">SPRING DEF</td>

        <td class="labelmin" colspan="1">SPRING DEF</td>

        <td class="labelmin" colspan="1">SPRING DEF</td>

        <td class="labelmin" colspan="1">SPRING DEF</td>
        <td class="labelmin" rowspan="2" colspan="5">------------</td>

    </tr>

    <tr>

        <td class="info" colspan="1">{{ $values['TELEMETRY_FL']['SPRING DEF'] ?? '' }}</td>

        <td class="info" colspan="1">{{ $values['TELEMETRY_FR']['SPRING DEF'] ?? '' }}</td>

        <td class="info" colspan="1">{{ $values['TELEMETRY_RL']['SPRING DEF'] ?? '' }}</td>

        <td class="info" colspan="1">{{ $values['TELEMETRY_RR']['SPRING DEF'] ?? '' }}</td>


    </tr>
</table>
