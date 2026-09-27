<div x-show="editingMember"
     class="fixed inset-0 bg-black/60 flex items-center justify-center">

    <div class="bg-gray-900 p-6 rounded-xl w-80">

        <h2 class="text-white mb-4">Edit Role</h2>

        <select x-model="editRole"
                class="w-full bg-gray-800 text-white p-2 rounded">

            <option value="driver">Driver</option>
            <option value="team_director">Director</option>
        </select>

        <div class="flex justify-end gap-2 mt-4">

            <button @click="editingMember = null"
                    class="text-gray-400">
                Cancel
            </button>

            <button @click="updateMember"
                    class="bg-blue-600 px-3 py-1 rounded">
                Save
            </button>

        </div>

    </div>
</div>