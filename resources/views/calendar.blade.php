<x-app-layout>

    <div class="max-w-7xl mx-auto py-8">

        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            Calendario
        </h1>

        <div id="calendar" class="bg-white shadow rounded-xl p-4"></div>

        {{-- Modal --}}
        <div id="modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.5); align-items:center; justify-content:center; z-index:9999;">
            <div class="bg-white rounded-lg p-6 w-96">

                <h3 id="modalTitle" class="text-xl font-semibold text-gray-800 mb-4"></h3>

                <form method="POST" id="sessionForm" class="space-y-4">

                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="scheduled_at" id="date">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Coche</label>
                        <select name="car_id" id="car" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($cars as $car)
                                <option value="{{ $car->id }}">{{ $car->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Circuito</label>
                        <select name="circuit_id" id="circuit" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($circuits as $circuit)
                                <option value="{{ $circuit->id }}">{{ $circuit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vueltas</label>
                        <input type="number" name="laps_done" id="laps" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="10">
                    </div>

                    <div>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow">
                            Guardar
                        </button>
                        <button type="button" id="closeModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-5 py-2 rounded-lg shadow">
                            Cancelar
                        </button>
                    </div>

                    <hr class="my-4">

                    <button type="button" id="openResult" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow" style="display:none;">
                        Registrar resultados
                    </button>

                </form>
            </div>
        </div>

    </div>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function(){

            const modal = document.getElementById('modal');
            const form = document.getElementById('sessionForm');
            const openResultBtn = document.getElementById('openResult');
            let currentSessionId = null;

            function openCreate(date){
                modal.style.display='flex';
                document.getElementById('modalTitle').innerText='Nueva sesión';
                form.action='/race_sessions';
                document.getElementById('formMethod').value='POST';
                document.getElementById('date').value=date;
                document.getElementById('laps').value=10;
                openResultBtn.style.display='none';
                currentSessionId = null;
            }

            function openEdit(info){
                modal.style.display='flex';
                document.getElementById('modalTitle').innerText='Editar sesión';
                currentSessionId = info.event.id;
                form.action='/race_sessions/'+currentSessionId;
                document.getElementById('formMethod').value='PUT';

                const e = info.event.extendedProps;
                document.getElementById('date').value=info.event.startStr;
                document.getElementById('laps').value=e.laps_done;
                document.getElementById('car').value=e.car_id;
                document.getElementById('circuit').value=e.circuit_id;

                openResultBtn.style.display='inline-block';
            }

            document.getElementById('closeModal').onclick = () => modal.style.display='none';

            openResultBtn.addEventListener('click', function(){
                if(currentSessionId){
                    window.location.href = '/race_sessions/' + currentSessionId + '/edit';
                }
            });

            const calendar = new FullCalendar.Calendar(document.getElementById('calendar'),{
                initialView:'dayGridMonth',
                events:@json($events),
                dateClick:(info) => openCreate(info.dateStr),
                eventClick:(info) => openEdit(info)
            });

            calendar.render();
        });
    </script>

</x-app-layout>
