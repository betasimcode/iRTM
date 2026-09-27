<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Team')</title>
<script>
    const savedTheme = "{{ auth()->user()->theme ?? 'theme-dark' }}";
    document.documentElement.className = savedTheme;
</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen">

    <div class="min-h-screen flex flex-col">

        {{-- HEADER --}}
       <x-team.header.navigation :team="$team" />
       <x-team.banner.banner :team="$team" size="medium"/>
        {{-- BODY --}}
        <div class="flex flex-1 min-h-0">

            {{-- SIDEBAR --}}
            <x-team.sidebar.default :team="$team" />

            {{-- CONTENT --}}
            <main class="flex-1 min-w-0 overflow-y-auto p-8">
                @yield('content')
            </main>

        </div>

    </div>
@include("components.layout.scripts")
</body>
</html>
