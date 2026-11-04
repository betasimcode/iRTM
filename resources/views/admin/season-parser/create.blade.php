@extends('layouts.app')

@section('content')

@include('admin.admin-competition-header')

<div class="max-w-5xl mx-auto py-8 px-4">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[var(--text)]">
            {{ __('ui.season_parser') }}
        </h1>

        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ __('ui.run_iracing_season_import') }}
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4">
            <div class="font-semibold text-red-800">
                {{ __('ui.cannot_run_import') }}
            </div>

            <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($seasons->isEmpty())

        <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-6">
            <h2 class="font-semibold text-yellow-900">
                {{ __('ui.no_seasons_configured') }}
            </h2>

            <p class="mt-2 text-sm text-yellow-800">
                {{ __('ui.configure_season_before_parser') }}
            </p>
        </div>

    @else

        <div class="rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm">

            <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-[var(--text-title)]">
                    {{ __('ui.run_import') }}
                </h2>

                <p class="mt-1 text-sm text-[var(--text)]">
                    {{ __('ui.select_configured_season') }}
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('admin.season-parser.run') }}"
                class="space-y-6 p-6"
            >
                @csrf

                <div>
                    <label
                        for="season"
                        class="block text-sm font-medium text-[var(--text)]"
                    >
                        {{ __('ui.season') }}
                    </label>

                    <select
                        id="season"
                        class="mt-2 block w-full rounded-lg border-[var(--border)] shadow-sm
                            focus:border-indigo-500 focus:ring-indigo-500
                              text-[var(--text)] bg-[var(--bg)]"
                    >
                        @foreach ($seasons as $index => $season)
                            <option value="{{ $index }}">
                                {{ $season['year'] }} S{{ $season['season'] }}
                            </option>
                        @endforeach
                    </select>

                    <input type="hidden" name="year" id="season-year">
                    <input type="hidden" name="season" id="season-number">
                </div>

                <div
                    id="season-info"
                    class="rounded-lg border border-[var(--border)] bg-[var(--bg)] p-5
                           "
                >
                    <h3 class="font-semibold text-[var(--text-title)]">
                        {{ __('ui.season_information') }}
                    </h3>

                    <div class="mt-4 grid gap-4 sm:grid-cols-3">

                        <div>
                            <div class="text-xs uppercase tracking-wide text-[var(--text-soft)]">
                                PDF
                            </div>

                            <div
                                id="pdf-info"
                                class="mt-1 font-medium text-[var(--text)]"
                            ></div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-[var(--text-soft)]">
                                {{ __('ui.pdf_registered') }}
                            </div>

                            <div
                                id="pdf-status"
                                class="mt-1 font-medium"
                            ></div>
                        </div>

                        <div>
                            <div class="text-xs uppercase tracking-wide text-[var(--text-soft)]">
                                {{ __('ui.configurations') }}
                            </div>

                            <div
                                id="imports-info"
                                class="mt-1 font-medium text-[var(--text)]"
                            ></div>
                        </div>

                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm
                               font-semibold text-white shadow-sm
                               hover:bg-indigo-700
                               focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        {{ __('ui.run_import') }}
                    </button>
                </div>

            </form>
        </div>

    @endif

</div>

@if ($seasons->isNotEmpty())
<script>
    const seasonYear = document.getElementById('season-year');
    const seasonNumber = document.getElementById('season-number');
    const seasons = @json($seasons->values());

    const selector = document.getElementById('season');
    const form = selector.closest('form');

    const pdfInfo = document.getElementById('pdf-info');
    const pdfStatus = document.getElementById('pdf-status');
    const importsInfo = document.getElementById('imports-info');

    function updateSeasonInfo() {
        const season = seasons[selector.value];
        seasonYear.value = season.year;
        seasonNumber.value = season.season;
        pdfInfo.textContent = season.pdf;

        if (season.pdf_exists) {
            pdfStatus.textContent = '✓ Disponible';
            pdfStatus.className =
                'mt-1 font-medium text-green-600';
        } else {
            pdfStatus.textContent = '✗ No disponible';
            pdfStatus.className =
                'mt-1 font-medium text-red-600';
        }

        importsInfo.textContent =
            `${season.imports_count} configuraciones`;

        form.querySelector('button[type="submit"]').disabled =
            !season.pdf_exists ||
            season.imports_count === 0;
    }

    selector.addEventListener('change', updateSeasonInfo);

    updateSeasonInfo();
</script>
@endif

@endsection
