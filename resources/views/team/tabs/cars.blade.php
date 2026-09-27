<div class="space-y-6">

    {{-- 🔵 CREATE --}}
    <form method="POST"
          action="{{ route('team.cars.store') }}"
          enctype="multipart/form-data"
          class="flex gap-3 items-end bg-[var(--card)] border border-[var(--b-card)] p-4 rounded-xl"
    >
        @csrf

        {{-- CAR SELECT --}}
        <select name="car_id"
                class="bg-[var(--input-bg)] border border-[var(--input-border)] px-3 py-2 rounded">

            <option value="">Select car</option>

            @foreach($cars as $car)
                <option value="{{ $car->id }}">
                    {{ $car->name }}
                </option>
            @endforeach

        </select>

        {{-- NUMBER --}}
        <input type="text"
               name="number"
               placeholder="#27"
               class="bg-[var(--input-bg)] border border-[var(--input-border)]  px-3 py-2 rounded w-20">

        {{-- IMAGE --}}
        <input type="file"
               name="image_path"
               class="text-sm text-gray-200">

        {{-- SUBMIT --}}
        <x-ui.button variant="add">
            <x-heroicon-o-plus class="w-4 h-4 mr-2"/> Adquire car
           </x-ui.button>
        {{-- <button class="bg-blue-600 px-4 py-2 rounded text-white hover:bg-blue-500">
            ADD
        </button> --}}

    </form>

    {{-- 🟢 LIST --}}
    <div class="grid grid-cols-2 gap-4">

        @forelse($teamCars as $teamCar)

            <div
                x-data="{ editing: false }"
                class="bg-[var(--card)] border border-[var(--b-card)]  p-4 rounded-xl flex gap-4 items-center"
            >

                {{-- IMAGE --}}
                <img src="{{ $teamCar->display_image }}"
                     class="w-96 object-cover rounded">

                {{-- VIEW MODE --}}
                <div class="flex-1" x-show="!editing">
                    {{-- IMAGE --}}
                    <img src="{{ asset('storage/'.$teamCar->car->logo_path) }}"
                    class="w-24 object-cover rounded">

                    <p class="text-gray-600 dark:text-gray-100 font-bold">
                        {{ $teamCar->car->name }}
                    </p>

                    @if($teamCar->number)
                        <p class="text-blue-400 dark:text-blue-300 text-md font-bold">
                            #{{ $teamCar->number }}
                        </p>
                    @endif

                </div>

                {{-- EDIT MODE --}}
                <form method="POST"
                      action="{{ route('team.cars.update', $teamCar) }}"
                      enctype="multipart/form-data"
                      class="flex-1 flex gap-2 items-center"
                      x-show="editing"
                >
                    @csrf
                    @method('PUT')

                    <input type="text"
                           name="number"
                           value="{{ $teamCar->number }}"
                           class="bg-gray-900 border border-gray-700 text-white px-2 py-1 rounded w-20">

                    <input type="file"
                           name="image_path"
                           class="text-xs text-gray-300">

                    <button class="text-green-400">✔</button>
                </form>

                @foreach($teamCar->drivers as $driver)

                    <div class="text-sm text-gray-600 uppercase">
                        <img title="{{ $driver->name }}" src="{{ asset('storage/'.$driver->iracing_helmet_path) }}"
                        class="w-12 h-12 rounded">
                    </div>
                @endforeach

                {{-- ACTIONS --}}
                <div class="flex justify-end gap-2">

                    <button @click="editing = !editing"
                            class="text-blue-400 hover:text-blue-600 text-sm">
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                <g id="SVGRepo_bgCarrier" stroke-width="0"/>

                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>

                                <g id="SVGRepo_iconCarrier"> <path d="M11 4.00023H6.8C5.11984 4.00023 4.27976 4.00023 3.63803 4.32721C3.07354 4.61483 2.6146 5.07377 2.32698 5.63826C2 6.27999 2 7.12007 2 8.80023V17.2002C2 18.8804 2 19.7205 2.32698 20.3622C2.6146 20.9267 3.07354 21.3856 3.63803 21.6732C4.27976 22.0002 5.11984 22.0002 6.8 22.0002H15.2C16.8802 22.0002 17.7202 22.0002 18.362 21.6732C18.9265 21.3856 19.3854 20.9267 19.673 20.3622C20 19.7205 20 18.8804 20 17.2002V13.0002M7.99997 16.0002H9.67452C10.1637 16.0002 10.4083 16.0002 10.6385 15.945C10.8425 15.896 11.0376 15.8152 11.2166 15.7055C11.4184 15.5818 11.5914 15.4089 11.9373 15.063L21.5 5.50023C22.3284 4.6718 22.3284 3.32865 21.5 2.50023C20.6716 1.6718 19.3284 1.6718 18.5 2.50022L8.93723 12.063C8.59133 12.4089 8.41838 12.5818 8.29469 12.7837C8.18504 12.9626 8.10423 13.1577 8.05523 13.3618C7.99997 13.5919 7.99997 13.8365 7.99997 14.3257V16.0002Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/> </g>

                                </svg>
                    </button>

                    <form method="POST"
                          action="{{ route('team.cars.destroy', $teamCar) }}">
                        @csrf
                        @method('DELETE')

                        <button class="text-red-400 hover:text-red-600 text-sm">
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                <g id="SVGRepo_bgCarrier" stroke-width="0"/>

                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>

                                <g id="SVGRepo_iconCarrier"> <path d="M16 6V5.2C16 4.0799 16 3.51984 15.782 3.09202C15.5903 2.71569 15.2843 2.40973 14.908 2.21799C14.4802 2 13.9201 2 12.8 2H11.2C10.0799 2 9.51984 2 9.09202 2.21799C8.71569 2.40973 8.40973 2.71569 8.21799 3.09202C8 3.51984 8 4.0799 8 5.2V6M10 11.5V16.5M14 11.5V16.5M3 6H21M19 6V17.2C19 18.8802 19 19.7202 18.673 20.362C18.3854 20.9265 17.9265 21.3854 17.362 21.673C16.7202 22 15.8802 22 14.2 22H9.8C8.11984 22 7.27976 22 6.63803 21.673C6.07354 21.3854 5.6146 20.9265 5.32698 20.362C5 19.7202 5 18.8802 5 17.2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/> </g>

                                </svg>
                        </button>
                    </form>

                </div>

            </div>

        @empty

            <p class="text-gray-400">No cars yet</p>

        @endforelse

    </div>

</div>

<script>
    function carsApp() {
        return {

            form: {
                car_id: '',
                number: '',
                image: null
            },

            handleFile(e) {
                this.form.image = e.target.files[0];
            },

            async createCar() {
                let data = new FormData();

                data.append('car_id', this.form.car_id);
                data.append('number', this.form.number);

                if (this.form.image) {
                    data.append('image_path', this.form.image);
                }

                await fetch('/team/cars', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: data
                });

                location.reload();
            },

            async deleteCar(id) {
                if (!confirm('Delete car?')) return;

                await fetch(`/team/cars/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                location.reload();
            },

            editCar(id, number) {
                const newNumber = prompt('New number', number);

                if (!newNumber) return;

                fetch(`/team/cars/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        number: newNumber
                    })
                }).then(() => location.reload());
            }

        }
    }
    </script>

<script>
    function carsApp() {
        return {

            driverToAdd: '',

            async addDriver(teamCarId) {

                await fetch('/team-car-driver', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        team_car_id: teamCarId,
                        user_id: this.driverToAdd,

                    })
                });

                location.reload();
            },

            async removeDriver(teamCarId, userId) {

                await fetch('/team-car-driver', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        team_car_id: teamCarId,
                        user_id: userId,

                    })
                });

                location.reload();
            }
        }
    }
    </script>
