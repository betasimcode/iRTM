@php
$currentLocale = LaravelLocalization::getCurrentLocale();
@endphp

<div x-data="{ open: false }" class="relative">

    <button
        @click="open = !open"
        class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-700 transition"
    >
        <span class="fi fi-{{ $currentLocale == 'en' ? 'gb' : $currentLocale }}"></span>
    </button>

    <div
        x-show="open"
        @click.outside="open = false"
        x-transition
        class="absolute right-0 mt-2 w-32 bg-gray-800 border border-gray-700 rounded shadow-lg"
    >

        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)

            <a
                href="{{ LaravelLocalization::getLocalizedURL($localeCode) }}"
                class="flex items-center gap-2 px-3 py-2 hover:bg-gray-700"
            >
                <span class="fi fi-{{ $localeCode == 'en' ? 'gb' : $localeCode }}"></span>
                <span>{{ $properties['native'] }}</span>
            </a>

        @endforeach

    </div>

</div>
