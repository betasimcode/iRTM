
<div class="space-y-6"
     x-data="{
        view: 'list',
        selectedMember: null
     }">

     <div x-show="view === 'list'" class="grid grid-cols-4 w-auto gap-4">

       @forelse($members as $user)

       <div 
       class="bg-gray-200/50 border border-gray-300 dark:bg-gray-800 dark:border-gray-600 p-4 rounded-xl flex items-center justify-between cursor-pointer hover:bg-gray-300/40 transition"
       @click="
           selectedMember = {{ $user->id }};
           view = 'detail';
       "
   >

               {{-- INFO --}}
               <div class="flex items-center gap-4">

                   <img src="{{ asset('storage/'.$user->iracing_helmet_path) }}"
                        class="w-20 rounded">

                   <div>
                       <p class="text-gray-800 dark:text-gray-100 font-bold">
                           {{ $user->name }}
                       </p>

                       <p class="text-xs text-gray-500 dark:text-gray-400">
                           {{ $user->iracing_name }}
                       </p>

                       <p class="text-xs text-blue-500 dark:text-blue-300">
                           {{ $user->cars_count ?? 0 }} cars
                       </p>
                   </div>

               </div>

               {{-- EDIT MODE --}}
               

           </div>

       @empty

           <p class="text-gray-400">No members yet</p>

       @endforelse

   </div>
    <div x-show="view === 'detail'">

        <button 
            @click="
                selectedMember = null;
                view = 'list';
            "
            class="mb-4 text-sm text-blue-400"
        >
            ← Back to members
        </button>

        @foreach($members as $user)
            <div x-show="selectedMember === {{ $user->id }}">
                @include('team.members.show', ['user' => $user])
            </div>
        @endforeach

    </div>
</div>