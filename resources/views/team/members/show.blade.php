<div class="bg-gray-200 dark:bg-gray-800 rounded-xl p-6 space-y-6"
     x-data="{ editingHelmet: false }">

    {{-- HEADER --}}
    <div class="flex items-center gap-6">

        {{-- HELMET BLOCK --}}
        <div>

            {{-- VIEW MODE --}}
            <div x-show="!editingHelmet" class="flex flex-col items-center gap-2">

                <img src="{{ asset('storage/'.$user->iracing_helmet_path) }}"
                     class="w-24 h-24 rounded object-cover">

                @if(auth()->user()->role === 'admin' || auth()->user()->driver_role === 'team_owner')
                    <button 
                        @click="editingHelmet = true"
                        class="text-blue-400 text-xs"
                    >
                        ✎ Edit
                    </button>
                @endif

            </div>

            {{-- EDIT MODE --}}
            <form method="POST"
                  action="{{ route('team.members.updateHelmet', $user) }}"
                  enctype="multipart/form-data"
                  class="flex flex-col items-center gap-2"
                  x-show="editingHelmet"
                  x-cloak
            >
                @csrf
                @method('PUT')

                <input type="file"
                       name="iracing_helmet"
                       class="text-xs text-gray-300">

                <div class="flex gap-2">
                    <button class="text-green-400 text-sm">✔</button>

                    <button type="button"
                            @click="editingHelmet = false"
                            class="text-gray-400 text-sm">
                        ✕
                    </button>
                </div>

            </form>

        </div>

        <div class="space-y-4 mt-6">

            <h3 class="text-sm text-gray-400">Series Assignments</h3>
        
            @foreach($series as $s)
        
                <div class="bg-gray-300 dark:bg-gray-900 p-4 rounded space-y-2">
        
                    <p class="text-xs text-gray-500">
                        {{ $s->name }}
                    </p>
        
                    {{-- drivers en esa serie --}}
                    @foreach($user->teamCars->where('pivot.series_id', $s->id) as $car)
        
                        <div class="flex justify-between text-sm">
        
                            <span>
                                {{ $car->car->name }} 
                                (#{{ $car->number }})
                            </span>
        
                            <span class="text-blue-400">
                                {{ $car->pivot->role }}
                            </span>
        
                        </div>
        
                    @endforeach
        
                </div>
        
            @endforeach
        
        </div>

        <form method="POST" action="/team-car/driver" class="flex gap-2 mt-4">
            @csrf
        
            <input type="hidden" name="user_id" value="{{ $user->id }}">
        
            {{-- SERIE --}}
            <select name="series_id" class="bg-gray-900 text-white px-2 py-1 rounded">
                @foreach($series as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        
            {{-- COCHE --}}
            <select name="team_car_id" class="bg-gray-900 text-white px-2 py-1 rounded">
                @foreach($teamCars as $car)
                    <option value="{{ $car->id }}">
                        {{ $car->car->name }} #{{ $car->number }}
                    </option>
                @endforeach
            </select>
        
            {{-- ROLE --}}
            <select name="role" class="bg-gray-900 text-white px-2 py-1 rounded">
                <option value="driver1">Driver 1</option>
                <option value="driver2">Driver 2</option>
                <option value="driver3">Driver 3</option>
                <option value="reserve">Reserve</option>
            </select>
        
            <button class="bg-green-600 px-3 py-1 rounded text-white">
                Assign
            </button>
        
        </form>

        {{-- USER INFO --}}
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                {{ $user->name }}
            </h2>

            <p class="text-gray-500">
                {{ $user->iracing_name }}
            </p>

            <p class="text-sm text-blue-400 mt-1">
                {{ ucfirst(str_replace('_',' ', $user->driver_role)) }}
            </p>
        </div>

    </div>

    {{-- GRID INFO --}}
    <div class="grid grid-cols-2 gap-6">

        <div class="space-y-4">
            <div class="bg-gray-300 dark:bg-gray-900 p-4 rounded">
                <p class="text-xs text-gray-500">Status</p>
                <p class="text-sm">{{ $user->current_status ?? 'offline' }}</p>
            </div>

            <div class="bg-gray-300 dark:bg-gray-900 p-4 rounded">
                <p class="text-xs text-gray-500">Email</p>
                <p class="text-sm">{{ $user->email }}</p>
            </div>

            <div class="bg-gray-300 dark:bg-gray-900 p-4 rounded">
                <p class="text-xs text-gray-500">Cars assigned</p>
                <p class="text-sm">{{ $user->cars_count ?? 0 }}</p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-gray-300 dark:bg-gray-900 p-4 rounded">
                <p class="text-xs text-gray-500">Series</p>
                <p class="text-sm">{{ $user->series_count ?? 0 }}</p>
            </div>

            <div class="bg-gray-300 dark:bg-gray-900 p-4 rounded">
                <p class="text-xs text-gray-500">iRacing ID</p>
                <p class="text-sm">{{ $user->iracing_user_id }}</p>
            </div>
        </div>

    </div>

    <div class="space-y-3">

        <h3 class="text-sm text-gray-400">Assigned Cars</h3>
    
        @forelse($user->teamCars as $car)
    
            <div class="flex items-center justify-between bg-gray-300 dark:bg-gray-900 p-3 rounded">
    
                <div class="flex items-center gap-3">
                    <img src="{{ asset('storage/'.$car->image_path) }}"
                         class="w-12 h-8 object-cover rounded">
    
                    <span class="text-sm">
                        {{ $car->car->name }} 
                        @if($car->number) #{{ $car->number }} @endif
                    </span>
                </div>
    
                {{-- REMOVE --}}
                <form method="POST" action="/team-car/driver">
                    @csrf
                    @method('DELETE')
    
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input type="hidden" name="team_car_id" value="{{ $car->id }}">
    
                    <button class="text-red-400 text-sm">✕</button>
                </form>
    
            </div>
    
        @empty
    
            <p class="text-gray-400 text-sm">No cars assigned</p>
    
        @endforelse
    
    </div>

    @if(auth()->user()->driver_role === 'team_owner')

    <div class="mt-4">

        <form method="POST" action="/team-car/driver" class="flex gap-2">
            @csrf

            <input type="hidden" name="user_id" value="{{ $user->id }}">

            <select name="team_car_id"
                    class="bg-gray-900 text-white px-3 py-2 rounded">

                <option value="">Assign car...</option>

                @foreach($teamCars as $car)
                    <option value="{{ $car->id }}">
                        {{ $car->car->name }} 
                        @if($car->number) #{{ $car->number }} @endif
                    </option>
                @endforeach

            </select>

            <button class="bg-green-600 px-3 py-2 rounded text-white">
                Add
            </button>

        </form>

    </div>

    @endif

    {{-- ACTIONS --}}
    @if(auth()->user()->role === 'admin' || auth()->user()->driver_role === 'team_owner')

    <div class="flex gap-4 pt-4 border-t border-gray-300 dark:border-gray-700">

        {{-- CHANGE ROLE --}}
        <form method="POST"
              action="{{ route('team.members.update', $user) }}">
            @csrf
            @method('PUT')

            <select name="driver_role"
                    class="bg-gray-900 text-white px-3 py-2 rounded">

                <option value="driver" @selected($user->driver_role === 'driver')>
                    Driver
                </option>

                <option value="team_director" @selected($user->driver_role === 'team_director')>
                    Director
                </option>

            </select>

            <button class="ml-2 bg-blue-600 px-3 py-2 rounded text-white">
                Update
            </button>
        </form>

        {{-- REMOVE --}}
        <form method="POST"
              action="{{ route('team.members.destroy', $user) }}">
            @csrf
            @method('DELETE')

            <button class="bg-red-600 px-3 py-2 rounded text-white">
                Remove
            </button>
        </form>

    </div>

    @endif

</div>