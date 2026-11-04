<x-app-layout>

    <div class="max-w-3xl mx-auto py-8">

        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            {{ __('ui.session_results') }}
        </h2>

        <form method="POST"
              action="{{ route('race_sessions.update', $race_session->id) }}"
              class="bg-white shadow rounded-lg p-6 space-y-6">

            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('ui.initial_fuel') }}
                </label>
                <input type="number"
                       step="0.01"
                       name="fuel_start"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('ui.final_fuel') }}
                </label>
                <input type="number"
                       step="0.01"
                       name="fuel_end"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('ui.best_lap_seconds') }}
                </label>
                <input type="number"
                       step="0.001"
                       name="lap_time"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('ui.incidents') }}
                </label>
                <input type="number"
                       name="incidents"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('ui.track_temperature') }}
                </label>
                <input type="number"
                       step="0.1"
                       name="track_temp"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex justify-end">
                <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow">
                    {{ __('ui.save_results') }}
                </button>
            </div>

        </form>

    </div>

</x-app-layout>
