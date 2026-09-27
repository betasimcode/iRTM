<table id="front-chassis" style="margin-bottom: 5px">

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


    <td class="label vertical" rowspan="9">FRONT SUSPENSION</td>

    <td class="label" colspan="2">FRONT LEFT TYRE</td>

    <td class="label" colspan="2">FRONT RIGHT TYRE</td>
    <td class="empty" rowspan="9">
    <td class="label vertical" rowspan="8">REAR SUSPENSION</td>

    <td class="label" colspan="2">REAR LEFT TYRE</td>

    <td class="label" colspan="2">REAR RIGHT TYRE</td>

    </tr>


    <tr>

        <td class="labelmin">Camber</td>
        <td class="value">{{ $values['FL_TYRE']['CAMBER'] ?? '' }}</td>

        <td class="labelmin">Camber</td>
        <td class="value">{{ $values['FR_TYRE']['CAMBER'] ?? '' }}</td>

        <td class="labelmin">Camber</td>
        <td class="value">{{ $values['RL_TYRE']['CAMBER'] ?? '' }}</td>

        <td class="labelmin">Camber</td>
        <td class="value">{{ $values['RR_TYRE']['CAMBER'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="labelmin">Caster</td>
        <td class="value">{{ $values['FL_TYRE']['CASTER'] ?? '' }}</td>

        <td class="labelmin">Caster</td>
        <td class="value">{{ $values['FR_TYRE']['CASTER'] ?? '' }}</td>

        <td class="labelmin">Toe in</td>
        <td class="value">{{ $values['RL_TYRE']['TOE IN'] ?? '' }}</td>

        <td class="labelmin">Toe in</td>
        <td class="value">{{ $values['RR_TYRE']['TOE IN'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="labelmin">Toe in</td>
        <td class="value">{{ $values['FL_TYRE']['TOE IN'] ?? '' }}</td>

        <td class="labelmin">Toe in</td>
        <td class="value">{{ $values['FR_TYRE']['TOE IN'] ?? '' }}</td>

        <td class="label" colspan="2">RL SUSPENSION</td>

        <td class="label" colspan="2">RR SUSPENSION</td>

    </tr>

    <tr>

        <td class="label" colspan="2">FL SUSPENSION</td>

        <td class="label" colspan="2">FR SUSPENSION</td>

        <td class="labelmin">Slow compress</td>
        <td class="value">{{ $values['RL_SUSP']['SLOW COMPRESS'] ?? '' }}</td>

        <td class="labelmin">Slow compress</td>
        <td class="value">{{ $values['RR_SUSP']['SLOW COMPRESS'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="labelmin">Slow compress</td>
        <td class="value">{{ $values['FL_SUSP']['SLOW COMPRESS'] ?? '' }}</td>

        <td class="labelmin">Slow compress</td>
        <td class="value">{{ $values['FR_SUSP']['SLOW COMPRESS'] ?? '' }}</td>

        <td class="labelmin">Slow rebound</td>
        <td class="value">{{ $values['RL_SUSP']['SLOW REBOUND'] ?? '' }}</td>

        <td class="labelmin">Slow rebound</td>
        <td class="value">{{ $values['RR_SUSP']['SLOW REBOUND'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="labelmin">Slow rebound</td>
        <td class="value">{{ $values['FL_SUSP']['SLOW REBOUND'] ?? '' }}</td>

        <td class="labelmin">Slow rebound</td>
        <td class="value">{{ $values['FR_SUSP']['SLOW REBOUND'] ?? '' }}</td>

        <td class="labelmin">Srping rate</td>
        <td class="value">{{ $values['RL_SUSP']['SPRING RATE'] ?? '' }}</td>

        <td class="labelmin">Spring rate</td>
        <td class="value">{{ $values['RR_SUSP']['SPRING RATE'] ?? '' }}</td>

    </tr>

    <tr>

        <td class="labelmin">Tor-Bar Diameter</td>
        <td class="value">{{ $values['FL_SUSP']['TOR-BAR DIAMETER'] ?? '' }}</td>

        <td class="labelmin">Tor-Bar Diameter</td>
        <td class="value">{{ $values['FR_SUSP']['TOR-BAR DIAMETER'] ?? '' }}</td>

        <td class="labelmin">Srping offset</td>
        <td class="value">{{ $values['RL_SUSP']['SPRING OFFSET'] ?? '' }}</td>

        <td class="labelmin">Spring offset</td>
        <td class="value">{{ $values['RR_SUSP']['SPRING OFFSET'] ?? '' }}</td>

    </tr>

  <tr>

        <td class="labelmin">Tor-Bar Preload</td>
        <td class="value">{{ $values['FL_SUSP']['TOR-BAR PRELOAD'] ?? '' }}</td>

        <td class="labelmin">Tor-Bar Preload</td>
        <td class="value">{{ $values['FR_SUSP']['TOR-BAR PRELOAD'] ?? '' }}</td>

        <td class="labelmin" colspan="6">----------------------------------------------------------------------------------------------------------------</td>


    </tr>





</table>
