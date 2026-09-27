<table id="main-aero" style="margin-bottom: 5px">

    <colgroup>

        <col style="width:calc(var(--col) * 1)">

        <col style="width:calc(var(--col) * 2)">
        <col style="width:calc(var(--col) * 2)">
        <col style="width:calc(var(--col) * 1)">

        <col style="width:calc(var(--col) * 1)">

        <col style="width:calc(var(--col) * 1)">

        <col style="width:calc(var(--col) * 1)">
        <col style="width:calc(var(--col) * 1)">
        <col style="width:calc(var(--col) * 1)">

    </colgroup>

    <tr>

        <td class="label vertical" rowspan="8">
            MAIN AND<br>
            AERODYNAMIC<br>
            CONFIG
        </td>
        <td class="label">START FUEL</td>
        <td class="label">AERO BALANCE TRIM</td>
        <td class="label vertical" rowspan="8">FRONT WINGS</td>
        <td class="label" colspan="2">FLAP CONFIG</td>
        <td class="label vertical" rowspan="8">REAR WINGS</td>
        <td class="label" colspan="2">WING ANGLE</td>
    </tr>

    <tr>

        <td class="value">{{ $values['CONFIG']['FUEL'] ?? '' }}</td>
        <td class="info">{{ $values['CONFIG']['AERO BALANCE TRIM'] ?? '' }}</td>
        <td class="value" colspan="2">{{ $values['FRONT_AERO']['FLAP CONFIG'] ?? '' }}</td>
        <td class="value"colspan="2">{{ $values['REAR_AERO']['WING ANGLE'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="label">AERO PACKAGE</td>
        <td class="label">DRAG TRIM</td>
        <td class="label" colspan="2">FLAP ANGLE</td>
        <td class="label" colspan="2">REAR BEAM WING</td>
    </tr>

    <tr>

        <td class="value">{{ $values['CONFIG']['AERO PACKAGE'] ?? '' }}</td>
        <td class="info">{{ $values['CONFIG']['DRAG TRIM'] ?? '' }}</td>
        <td class="value" colspan="2">{{ $values['FRONT_AERO']['FLAP ANGLE'] ?? '' }}</td>
        <td class="value"colspan="2">{{ $values['REAR_AERO']['REAR BEAM WING'] ?? '' }}</td>
    </tr>

    <tr>


        <td class="empty"></td>
        <td class="label">DOWNFORCE DRAG</td>
        <td class="label" colspan="2">FLAP GURNEY</td>
        <td class="labelmin" colspan="2">----------</td>
    </tr>

    <tr>


        <td class="empty"></td>
        <td class="info">{{ $values['CONFIG']['DOWNFORCE\DRAG'] ?? '' }}</td>
        <td class="value" colspan="2">{{ $values['FRONT_AERO']['FLAP GURNEY'] ?? '' }}</td>
        <td class="labelmin" colspan="2">----------</td>
    </tr>

    <tr>


        <td class="empty"></td>
        <td class="label">DOWNFORCE TRIM</td>
        <td class="label" colspan="2">RH TOP SPEED</td>
        <td class="label" colspan="2">RH TOP SPEED</td>
    </tr>

    <tr>


        <td class="empty"></td>
        <td class="info">{{ $values['CONFIG']['DOWNFORCE TRIM'] ?? '' }}</td>
        <td class="info" colspan="2">{{ $values['FRONT_AERO']['RH TOP SPEED'] ?? '' }}</td>
        <td class="info" colspan="2">{{ $values['REAR_AERO']['RH TOP SPEED'] ?? '' }}</td>
    </tr>

</table>
