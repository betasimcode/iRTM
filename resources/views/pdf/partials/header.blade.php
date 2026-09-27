<table id="header" style="margin-bottom: 5px">

    <colgroup>

        <col style="width:calc(var(--col) * 3)">

        <col style="width:calc(var(--col) * 2)">
        <col style="width:calc(var(--col) * 2)">

        <col style="width:calc(var(--col) * 2)">
        <col style="width:calc(var(--col) * 2)">

    </colgroup>

    <tr>

        <td id="logo" class="logo" rowspan="5">

            <img src="/storage/{{ $stint->user->team->banner_path }}" width="250" height="auto">

        </td>

        <td class="hlabel">TRACK</td>

        <td class="hinfo">{{ $stint->track->display_name ?? '' }}</td>

        <td class="hlabel">DATE</td>

        <td class="hinfo">{{ optional($stint->created_at)->format('d-m-Y H:i') }}</td>

    </tr>

    <tr>

        <td class="hlabel">VARIANT</td>

        <td class="hinfo">{{ $stint->track->variant ?? '' }}</td>

        <td class="hlabel">WEEK</td>

        <td class="hinfo">{{ $week ?? '' }}</td>

    </tr>

    <tr>

        <td class="hlabel">CAR</td>

        <td class="hinfo">{{ $stint->car_name }}</td>

        <td class="hlabel">SESSION NUM</td>

        <td class="hinfo">{{ $stint->iracing_subsession_id }}</td>

    </tr>

    <tr>

        <td class="hlabel">DRIVER</td>

        <td class="hinfo">{{ $stint->user->name }}</td>

        <td class="hlabel">SESSION TYPE</td>

        <td class="hinfo">{{ strtoupper($stint->session_phase) }}</td>

    </tr>

    <tr>

        <td class="hlabel">iR SERIES</td>

        <td class="hinfo">{{ optional($stint->series)->short_name }}</td>

        <td class="hlabel">WEATHER</td>

        <td class="hinfo">{{ $setup->setup_weather }}</td>

    </tr>

</table>
