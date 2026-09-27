<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<title>Live Timing</title>

@vite(['resources/js/app.js'])

</head>

<body class="bg-gray-900 text-white p-6">

<h1 class="text-2xl font-bold mb-6">
Live Timing
</h1>

<table class="w-full text-sm">

<thead class="border-b border-gray-700 text-gray-400">

<tr>
<th class="text-left py-2">Driver</th>
<th>Team</th>
<th>Lap</th>
<th>Last</th>
<th>Gap</th>
</tr>

</thead>

<tbody id="timing-table">

</tbody>

</table>

<script>

document.addEventListener("DOMContentLoaded", function () {

    console.log("LiveTiming ready");

    Echo.channel('livetiming')
    .listen('LapCompleted', (e) => {

        console.log("EVENT RECEIVED", e)

        const table = document.getElementById("timing-table");

        const row = `
        <tr class="border-b border-gray-800">
            <td>${e.driver}</td>
            <td>${e.team}</td>
            <td>${e.lap}</td>
            <td>${e.lapTime}</td>
            <td>${e.gap ?? '+0.000'}</td>
        </tr>
        `;

        table.insertAdjacentHTML("afterbegin", row)

    })

});

</script>

</body>
</html>
