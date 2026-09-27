<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>Setup Sheet</title>

<link rel="stylesheet" href="{{ asset('css/pdf/setup-sheet.css') }}">

</head>

<body>

<div class="sheet">

    @include('pdf.partials.header')
    @include('pdf.partials.session-summary')
    @include('pdf.partials.main-aero')
    @include('pdf.partials.tyres')
    @include('pdf.partials.suspension')
    @include('pdf.partials.dampers')
    @include('pdf.partials.telemetry-drivetrain')

</div>

</body>

</html>
