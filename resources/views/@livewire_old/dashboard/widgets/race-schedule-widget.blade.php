<div
    class="
        bg-white
        dark:bg-gray-900
        rounded-lg
        border
        border-gray-200
        dark:border-gray-700
        p-4
    "
>

    <h3
        class="
            text-sm
            font-semibold
            uppercase
            tracking-wide
        "
    >
        Next Races
    </h3>

    <div class="mt-4">

        @forelse($events as $event)

            <div
                class="
                    flex
                    justify-between
                    items-center
                    py-2
                    border-b
                    border-gray-100
                    dark:border-gray-800
                "
            >

                <div>

                    <div
                        class="
                            font-medium
                        "
                    >
                        {{ $event['series_short_name'] }}
                    </div>

                    <div
                        class="
                            text-xs
                            text-gray-500
                        "
                    >
                        Registration

                        {{ \Carbon\Carbon::parse(
                            $event['registration_time']
                        )->format('H:i') }}
                    </div>

                </div>

                <div
                    class="
                        text-right
                    "
                >

                    <div
                        class="
                            font-mono
                            font-semibold
                        "
                    >
                        {{ \Carbon\Carbon::parse(
                            $event['event_time']
                        )->format('H:i') }}
                    </div>

                </div>

            </div>

        @empty

            <div
                class="
                    text-sm
                    text-gray-500
                "
            >
                No active series
            </div>

        @endforelse

    </div>

</div>