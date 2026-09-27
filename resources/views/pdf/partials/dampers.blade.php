<table id="rear-chassis" style="margin-bottom: 5px">

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

        <td class="label vertical" rowspan="10">DAMPERS</td>

        <td class="label" colspan="3">FRONT</td>

        <td class="label" colspan="3">REAR</td>

        <td class="label vertical" rowspan="6">REAR WINGS</td>

        <td class="label" colspan="3">WING ANGLE</td>

    </tr>

    <tr>

        <td class="labelmin">Heave Deflect</td>
        <td class="value" colspan="2">{{ $values['FRONT_DAMPERS']['HEAVE DAMPER DEF'] ?? '' }}</td>

        <td class="labelmin">3rd spring deflect</td>
        <td class="value" colspan="2">{{ $values['REAR_DAMPERS']['3RD DAMPER DEF'] ?? '' }}</td>

        <td class="value" colspan="3">12º</td>

    </tr>

    <tr>

        <td class="labelmin">Heave Offset</td>
        <td class="value"colspan="2">{{ $values['FRONT_DAMPERS']['HEAVE OFFSET'] ?? '' }} mm</td>

        <td class="labelmin">3rd spring offset</td>
        <td class="value"colspan="2">{{ $values['REAR_DAMPERS']['3RD SPRING OFFSET'] ?? '' }}</td>

        <td class="label" colspan="3">

            REAR BEAM WING

        </td>

    </tr>

    <tr>

        <td class="labelmin">Heave Spring</td>
        <td class="value"colspan="2">{{ $values['FRONT_DAMPERS']['HEAVE SPRING'] ?? '' }} mm</td>

        <td class="labelmin">3rd spring</td>
        <td class="value"colspan="2">{{ $values['REAR_DAMPERS']['3RD SPRING'] ?? '' }}</td>

        <td class="value" colspan="3">8º</td>

    </tr>

    <tr>

        <td class="labelmin">Heave spring def</td>
        <td class="value"colspan="2">{{ $values['FRONT_DAMPERS']['HEAVE SPRING DEF'] ?? '' }} mm</td>

        <td class="labelmin">3rd spring def</td>
        <td class="value"colspan="2">{{ $values['REAR_DAMPERS']['3RD SPRING DEF'] ?? '' }}</td>

        <td class="label" colspan="3">

            RH TOP SPEED

        </td>

    </tr>

    <tr>

        <td class="labelmin">Push Rod offset</td>
        <td class="value"colspan="2">{{ $values['FRONT_DAMPERS']['PUSH ROD OFFSET'] ?? '' }} mm</td>

        <td class="labelmin">Push Rod offset</td>
        <td class="value"colspan="2">{{ $values['REAR_DAMPERS']['PUSH ROD OFFSET'] ?? '' }} mm</td>

        <td class="value" colspan="3">

            15 mm

        </td>

    </tr>


</table>
