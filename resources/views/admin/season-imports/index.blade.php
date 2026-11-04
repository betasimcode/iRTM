@extends('layouts.app')

@section('content')

@include('admin.admin-competition-header')

<div class="max-w-7xl mx-auto py-8 px-4">



    <div class="mb-8 flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-[var(--text)]">
                {{ __('ui.season_imports') }}
            </h1>

            <p class="mt-2 text-sm text-[var(--text-soft)]">
                {{ __('ui.series_extracted_from_pdfs') }}
            </p>
        </div>

        <a
            href="{{ route('admin.season-imports.create') }}"
            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm
                   font-semibold text-white shadow-sm
                   hover:bg-indigo-700"
        >
            + {{ __('ui.new_configuration') }}
        </a>

    </div>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-300 bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">
                {{ session('success') }}
            </p>
        </div>
    @endif

    @if ($imports->isEmpty())

        <div class="rounded-xl border border-[var(--border)] bg-[var(--card)] p-8 text-center shadow-sm">

            <h2 class="text-lg font-semibold text-[var(--text-title)]">
                {{ __('ui.no_configurations') }}
            </h2>

            <p class="mt-2 text-sm text-[var(--text)] dark:text-gray-400">
                {{ __('ui.create_first_import_configuration') }}
            </p>

            <div class="mt-6">
                <a
                    href="{{ route('admin.season-imports.create') }}"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm
                           font-semibold text-white hover:bg-indigo-700"
                >
                    {{ __('ui.create_configuration') }}
                </a>
            </div>

        </div>

    @else

        <div class="overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-[var(--border)] ">

                    <thead class="border-[var(--border)] bg-[var(--card-title)]">

                        <tr>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[var(--text-card-title)]">
                                {{ __('ui.season') }}
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[var(--text-card-title)]">
                                {{ __('ui.series') }}
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[var(--text-card-title)]">
                                {{ __('ui.pdf_title') }}
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[var(--text-card-title)]">
                                {{ __('ui.pages') }}
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-[var(--border)] ">

                        @foreach ($imports as $import)

                            <tr class="hover:bg-[var(--card-hover)]">

                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="font-semibold text-[var(--text-title)]">
                                        {{ $import->year }} S{{ $import->season }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="font-medium text-[var(--text-title)]">
                                        {{ $import->iracingSeries?->name ?? __('ui.series_not_found') }}
                                    </div>

                                    <div class="mt-1 text-xs text-[var(--text)]">
                                        ID: {{ $import->ir_serie_id }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="max-w-xl text-sm text-[var(--text-soft)]">
                                        {{ $import->title }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="text-sm text-[var(--text-soft)]">
                                        {{ $import->page_start }}

                                        @if ($import->page_end !== $import->page_start)
                                            – {{ $import->page_end }}
                                        @endif
                                    </span>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    @endif

</div>

@endsection
