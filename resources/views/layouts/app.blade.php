<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- <script>
        (function () {
            const theme = localStorage.getItem('theme');

            if (theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }#9ca3af
        })();
    </script> --}}

<script>
    const savedTheme = "{{ auth()->user()->theme ?? 'theme-dark' }}";
    document.documentElement.className = savedTheme;
</script>

    <title>@yield('title', 'SimuPanel')</title>
    @vite(['resources/css/app.css','resources/js/app.js'])

</head>
<body
    x-data
    :class="$store.theme.current">
{{-- <body class="bg-gray-100 text-gray-900 dark:bg-gray-950 dark:text-gray-200 relative"> --}}
@php
$team = auth()->user()?->team;
@endphp
<div class="flex h-screen overflow-hidden">

 @include("components.layout.sidebar")

    {{-- MAIN CONTENT --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- HEADER --}}
        @include("components.layout.header")

        {{-- PAGE CONTENT --}}
        <main class="flex-1 overflow-y-auto p-8 bg-[var(--base)] text-[var(--text)]">


        @yield('content')
        <x-toasts />

        </main>

    </div>
{{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
</div>
{{-- <script>
function toggleTheme() {
    const html = document.documentElement;

    if (html.classList.contains('dark')) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
}
</script> --}}

@include("components.layout.scripts")

</body>
</html>

