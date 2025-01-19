<div>
    <button
        type="button"
        class="btn-sm rounded bg-blue-600 border-none text-white hover:bg-blue-500"
        wire:click="openModal">
        <span class="mr-2">Ajouter un admin</span>
    </button>

    <!-- Modal -->
    @if ($isModalOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg w-1/3">
                <h2 class="text-xl font-bold mb-4">Ajouter un admin </h2>
                <form wire:submit.prevent="saveUser">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input wire:model="name" type="text" id="name"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input wire:model="email" type="text" id="email"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input wire:model="password" type="text" id="password"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                        @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="button" wire:click="closeModal"
                                class="mr-2 px-4 py-2 bg-gray-500 text-white rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
