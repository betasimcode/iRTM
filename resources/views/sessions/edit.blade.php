<x-app-layout>

    <div class="max-w-3xl mx-auto py-8">

        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            Registrar resultados de la sesión
        </h2>

        <form method="POST"
              action="{{ route('race_sessions.update', $race_session->id) }}"
              class="bg-white shadow rounded-lg p-6 space-y-6">

            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Combustible inicial
                </label>
                <input type="number"
                       step="0.01"
                       name="fuel_start"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Combustible final
                </label>
                <input type="number"
                       step="0.01"
                       name="fuel_end"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Mejor vuelta (s)
                </label>
                <input type="number"
                       step="0.001"
                       name="lap_time"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Incidentes
                </label>
                <input type="number"
                       name="incidents"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Temperatura pista
                </label>
                <input type="number"
                       step="0.1"
                       name="track_temp"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex justify-end">
                <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow">
                    Guardar resultados
                </button>
            </div>

        </form>

    </div>

</x-app-layout>
