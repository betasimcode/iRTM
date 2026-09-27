<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Team')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen">

    <div class="min-h-screen flex">

        {{-- SIDEBAR --}}
        <aside class="shrink-0 border-r flex flex-col"
               style="width: 260px; min-width: 144px; max-width: 260px;">

            {{-- LOGO --}}
            <div class="h-20 w-full flex items-center justify-center border-b px-3">
                <x-team.logo :team="$team" class="w-full h-full max-w-full max-h-full"/>
            </div>

            {{-- NAVIGATION --}}
            <nav class="flex-1 p-4 space-y-2">
                <div>Dashboard</div>
                <div>Competitions</div>
                <div>Drivers</div>
                <div>Cars</div>
                <div>Sessions</div>
                <div>Stints</div>
                <div>Strategy</div>
            </nav>

        </aside>

        {{-- RIGHT SIDE --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- HEADER --}}
            <x-team.header.technical :team="$team" />

            {{-- CONTENT --}}
            <main class="flex-1 min-w-0 overflow-y-auto p-8">
                @yield('content')
            </main>

        </div>

    </div>
@include("components.layout.scripts")
</body>
</html>
