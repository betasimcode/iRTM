<div>

    <h3 class="text-[var(--text)] text-lg font-semibold mb-4">
        Series del equipo
    </h3>

    @if($team->series->isEmpty())

        <div class="bg-[var(--card)] border border-[var(--border)] rounded-xl p-6 text-[var(--text)]">
            No estás inscrito en ninguna serie.
        </div>

    @else

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">

        @foreach($team->series as $serie)

            @php
                $teamCar = $serie->teamCar($team->id);
                $canEdit = auth()->user()->isTeamDirector() && $serie->status !== 'active';
            @endphp

            <div class="bg-[var(--card)] shadow-md border border-[var(--border)] rounded p-5 space-y-4">

               
                <div>
                    <h4 class="text-[var(--text-title)] font-semibold">
                        {{ $serie->name }}
                    </h4>

                    <p class="text-[var(--value-info)] text-sm">
                        {{ $serie->season_year }} - S{{ $serie->season_number }}
                    </p>
                </div>

                <div class="text-sm">

                    
                        <p class="text-[var(--text)]">
                            @forelse($teamCars as $teamCar)

                            <p class="text-[var(--text)]">
                                🚗 {{ $teamCar->car->name }}
                        
                                @if($teamCar->number)
                                    #{{ $teamCar->number }}
                                @endif
                            </p>
                        
                        @empty
                        
                            <p class="text-gray-400">
                                No hay coches registrados
                            </p>
                        
                        @endforelse
                        </p>

                        <p class="text-yellow-400">
                            ⚠ No asignado
                        </p>
    

                </div>

               
               
                <div class="pt-2">

                    @if($canEdit)

                        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-2 rounded-lg">
                            Configurar coche
                        </button>

                    @else

                        <div class="text-gray-500 text-xs">
                            🔒 Configuración bloqueada
                        </div>

                    @endif

                </div>

            </div>

        @endforeach

    </div>

    @endif

</div>