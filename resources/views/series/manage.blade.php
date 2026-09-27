@extends('layouts.app')

@section('title', 'Planificar Serie')
@section('page-title', 'Planificación - '.$series->name)

@section('content')

<div class="space-y-10">

    {{-- Crear nueva semana --}}
    <div class="">

        <form method="POST"
      action="{{ route('series.rounds.store', $series) }}"
      class="bg-[var(--bg)] border border-[var(--border)] rounded-2xl p-8 space-y-6">
      <h3 class="text-lg font-semibold text-[var(--text)] mb-6">
        {{ __('ui.addweek') }}
    </h3>

    @csrf

    {{-- Semana --}}
    <input type="number" name="week" placeholder="{{ __('ui.week') }}"
           class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--text-input)]">

    {{-- Fecha inicio --}}
    <input type="date" name="week_start" id="week_start"
    class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--text-input)]">

    {{-- Fecha fin (auto) --}}
    <input type="date" name="week_end" id="week_end"
    class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--text-input)]" readonly>

    {{-- Circuito --}}
    <select name="circuit_id"
    class="w-full bg-[var(--input-bg)] border border-[var(--input-border)] rounded-lg px-4 py-2 text-[var(--text-input)]">

        @foreach($tracks as $track)
            <option value="{{ $track->id }}">
                {{ $track->display_name }} - {{ $track->variant }}
            </option>
        @endforeach
    </select>

    {{-- Tipo de carrera (bloqueado) --}}
    <div>
        <label class="text-[var(--text)/90]">{{ __('ui.racetype') }}</label>
        <input type="text"
               value="{{ $series->iracingSeries->race_type === 'time' ?  __('ui.time')  : __('ui.laps') }}"
               class="w-full bg-[var(--input-dis)] border border-[var(--border)] rounded-lg px-4 py-2 text-[var(--text-soft)]"
               disabled>
    </div>

    {{-- Longitud --}}
    @if($series->iracingSeries->race_type === 'time')

        <div>
            <label class="text-[var(--text)]">{{ __('ui.duration') }}</label>
            <input type="text"
                   value="{{ $series->iracingSeries->race_length }} min"
                   class="w-full bg-[var(--input-dis)] border border-[var(--border)] rounded-lg px-4 py-2 text-[var(--text-soft)]"
               disabled>
        </div>

        {{-- Hidden para enviar valor --}}
        <input type="hidden" name="race_type" value="time">
        <input type="hidden" name="race_length" value="{{ $series->iracingSeries->race_length }}">

    @else

        {{-- Editable solo en laps --}}
        <input type="hidden" name="race_type" value="laps">

        <input type="number" name="race_length"
               placeholder="Vueltas"
               class="w-full bg-[var(--input-dis)] border border-[var(--border)] rounded-lg px-4 py-2 text-[var(--text)]">

    @endif

    <button type="submit"
        class="mt-4 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-[var(--text)]rounded-lg">
        {{ __('ui.addweek') }}
    </button>

</form>

    </div>

<br>
    {{-- Tabla de semanas --}}
    <div class="bg-[var(--bg)] border border-[var(--border)] rounded-2xl overflow-hidden">

        <table class="min-w-full text-sm">

            <thead class="bg-[var(--card)]  border-b border-[var(--border)]">
                <tr class="text-[var(--text)] uppercase text-xs">
                    <th class="px-6 py-4 text-left">{{ __('ui.week') }}</th>
                    <th class="px-6 py-4 text-left">{{ __('ui.start') }}</th>
                    <th class="px-6 py-4 text-left">{{ __('ui.end') }}</th>
                    <th class="px-6 py-4 text-left">{{ __('ui.track') }}</th>
                    <th class="px-6 py-4 text-left">{{ __('ui.format') }}</th>
                    <th class="px-6 py-4 text-right">{{ __('ui.actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-700">

                @foreach($series->rounds->sortBy('week') as $round)

                <tr class="hover:bg-[var(--hover)] hover:text-[var(--text-h)]">

                    <td class="px-6 py-4">
                        {{ $round->week }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $round->week_start }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $round->week_end }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $round->track->display_name }}
                    </td>

                    <td class="px-6 py-4">
                        {{ $round->race_type == 'time'
                            ? $round->race_length.' min'
                            : $round->race_length.' vueltas' }}
                    </td>

                    <td class="px-6 py-4 text-right space-x-3">
                        <button
                            onclick="openEditRound({{ $round->id }})"
                            class="text-blue-400 hover:text-[var(--text-h)]">
                            {{ __('ui.edit') }}
                        </button>
                        <form method="POST"
                              action="{{ route('series.rounds.destroy', $round) }}"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-500 hover:text-red-400">
                                {{ __('ui.delete') }}
                            </button>
                        </form>

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>
<br>
<div class="mt-6">
    <a href="{{ route('series.show', $series) }}"
       class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700
              text-white rounded-lg text-sm font-medium transition shadow">
        {{ __('ui.continue') }}
    </a>
</div>

<div id="editRoundModal"
     class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">

    <div class="bg-gray-900 p-6 rounded-lg w-96">

        <h2 class="text-lg font-semibold mb-4">{{ __('ui.editround') }}</h2>

        <form id="editRoundForm" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="block text-sm">{{ __('ui.track') }}</label>
                <select name="circuit_id" id="edit_circuit" class="w-full bg-gray-800 p-2 rounded">
                       @foreach($tracks as $track)
                            <option value="{{ $track->id }}">
                                {{ $track->display_name }} - {{ $track->variant }}
                            </option>
                        @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="block text-sm">{{ __('ui.week') }}</label>
                <input type="number" name="week" id="edit_week"
                       class="w-full bg-gray-800 p-2 rounded">
            </div>

            <div class="mb-3">
                <label class="block text-sm">{{ __('ui.start') }}</label>
                <input type="date" name="week_start" id="edit_start"
                       class="w-full bg-gray-800 p-2 rounded">
            </div>

            <div class="mb-3">
                <label class="block text-sm">{{ __('ui.end') }}</label>
                <input type="date" name="week_end" id="edit_end"
                       class="w-full bg-gray-800 p-2 rounded readonly">
            </div>

            <div class="mb-3">
                <label class="block text-sm">{{ __('ui.racetype') }}</label>
                <select name="race_type" id="edit_type" class="w-full bg-gray-800 p-2 rounded">
                    <option value="laps">{{ __('ui.laps') }}</option>
                    <option value="time">{{ __('ui.time') }}</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm">{{ __('ui.length') }}</label>
                <input type="number" name="race_length" id="edit_length"
                       class="w-full bg-gray-800 p-2 rounded">
            </div>

            <div class="flex justify-between">
                <button type="button"
                        onclick="closeEditRound()"
                        class="px-3 py-2 bg-gray-700 rounded">
                    {{ __('ui.cancel') }}
                </button>

                <button type="submit"
                        class="px-3 py-2 bg-blue-600 rounded">
                    {{ __('ui.update') }}
                </button>
            </div>

        </form>
    </div>
</div>


<script>

function openEditRound(id){

    const rounds = @json($series->rounds);

    const round = rounds.find(r => r.id === id);

    const modal = document.getElementById("editRoundModal");

    document.getElementById("editRoundForm").action = "/rounds/" + id;

    document.getElementById("edit_week").value = round.week;
    document.getElementById("edit_start").value = round.week_start;
    document.getElementById("edit_end").value = round.week_end;

    document.getElementById("edit_circuit").value = round.circuit_id;

    document.getElementById("edit_type").value = round.race_type;
    document.getElementById("edit_length").value = round.race_length;

    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeEditRound(){
    document.getElementById("editRoundModal").classList.add("hidden");
}

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const start = document.getElementById('week_start');
    const end = document.getElementById('week_end');

    start.addEventListener('change', function () {
        if (!this.value) return;

        // 🔥 Parse manual (evita bug de timezone)
        const parts = this.value.split('-');
        const date = new Date(parts[0], parts[1] - 1, parts[2]);

        date.setDate(date.getDate() + 7);

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        end.value = `${year}-${month}-${day}`;
    });
});
</script>

@endsection
