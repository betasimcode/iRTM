<!DOCTYPE html>

<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>
<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield('title', 'Telemetry Lab')
        · iRTeam Manager
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body
    class="min-h-screen
           bg-[var(--bg)]
           text-[var(--text)]"
>

    <header
        class="border-b border-[var(--border)]
               bg-[var(--card)]"
    >

        <div
            class="mx-auto
                   max-w-7xl
                   px-6
                   py-4"
        >

            <div
                class="flex
                       items-center
                       justify-between"
            >

                <div>

                    <p
                        class="text-xs
                               uppercase
                               tracking-[0.2em]
                               text-[var(--text-muted)]"
                    >
                        iRTeam Manager
                    </p>

                    <h1
                        class="mt-1
                               text-lg
                               font-semibold
                               text-[var(--text)]"
                    >
                        Telemetry Lab
                    </h1>

                </div>


                <a
                    href="{{ url('/') }}"
                    class="text-sm
                           text-[var(--text-muted)]
                           transition
                           hover:text-[var(--text)]"
                >
                    iRTM
                </a>

            </div>

        </div>

    </header>


    <main>

        @yield('content')

    </main>

</body>

</html>
