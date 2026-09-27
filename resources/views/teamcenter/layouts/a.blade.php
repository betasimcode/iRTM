<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield('title', 'TeamCenter')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('head')
</head>

<body
    class="{{ auth()->user()->theme ?? 'theme-dark' }} min-h-screen"
    style="
        background-color: var(--bg);
        color: var(--text);
    "
>

    @include(
        'teamcenter.components.header.header',
        [
            'logoPosition' => 'center',
            'navigationPosition' => 'bottom',
        ]
    )

    <main class="min-h-[calc(100vh-5rem)]">
        @yield('teamcenter-main')
    </main>

    @include('components.layout.scripts')

    @stack('scripts')

</body>

</html>
