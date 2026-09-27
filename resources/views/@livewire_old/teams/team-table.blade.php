<div>

    <div class="flex justify-between mb-6">
        <h2 class="text-xl font-semibold">Teams</h2>

        <button wire:click="openCreate"
            class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-sm">
            Create Team
        </button>
    </div>

    <table class="w-full bg-gray-800 rounded-xl overflow-hidden">
        <thead class="bg-gray-700 text-sm text-gray-300">
            <tr>
                <th class="p-4 text-left">Logo</th>
                <th>Name</th>
                <th>Banner</th>
                <th class="text-right pr-4">Actions</th>
            </tr>
        </thead>
        <tbody class="text-xl">
            @foreach($teams as $team)
                <tr class="border-t border-gray-700">
                    <td class="p-4">
                        @if($team->logo_url)
                            <img src="{{ $team->logo_url }}" class="h-10 rounded">
                        @endif
                    </td>

                    <td><h4>{{ $team->name }}</h4></td>

                    <td>
                        @if($team->banner_url)
                            <img src="{{ $team->banner_url }}" class="h-12 rounded" width=256px>
                        @endif
                    </td>

                    <td class="text-right pr-4 space-x-2">
                        <button wire:click="openEdit({{ $team->id }})"
                            class="text-blue-400">
                            Edit
                        </button>

                        <button wire:click="delete({{ $team->id }})"
                            onclick="return confirm('Delete this team?')"
                            class="text-red-400">
                            Delete
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-6">
        {{ $teams->links() }}
    </div>

    {{-- MODAL --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center">
            <div class="bg-gray-800 p-6 rounded-xl w-96">

                <h3 class="text-lg mb-4">
                    {{ $editMode ? 'Edit Team' : 'Create Team' }}
                </h3>

                <div class="space-y-3">

                    <input wire:model="name"
                        placeholder="Team Name"
                        class="w-full bg-gray-700 rounded px-3 py-2">

                    <input type="file" wire:model="logo"
                        class="w-full bg-gray-700 rounded px-3 py-2">

                    <input type="file" wire:model="banner"
                        class="w-full bg-gray-700 rounded px-3 py-2">

                </div>

                <div class="flex justify-end mt-4 space-x-2">
                    <button wire:click="$set('showModal', false)"
                        class="px-4 py-2 bg-gray-600 rounded">
                        Cancel
                    </button>

                    <button wire:click="save"
                        class="px-4 py-2 bg-blue-600 rounded">
                        Save
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
