<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    public $users = []; // Initialize users as an empty array
    public $isModalOpen = false; // Control modal visibility
    public $name, $email, $password, $specialite, $attestation, $image; // Form fields
    public $search = ''; // Search term
    public $sortField = 'name'; // Default sort field
    public $sortDirection = 'asc'; // Default sort direction
    public $currentPage = 1; // Current page for pagination
    public $itemsPerPage = 10; // Items per page
    public $filteredUsers = []; // Filtered users
    public $totalPages = 1; // Total pages

    public function mount() {
        $this->getUsers(); // Fetch users when the component mounts
    }

    public function getUsers() {
        $query = User::with('roles')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $this->users = $query->get(); // Fetch all users with their roles
    }

    public function updatedSearch() {
        $this->getUsers(); // Refresh the users list when search term changes
    }

    public function toggleValidation($userId) {
        $user = User::find($userId); // Find the user by ID
        $user->isValidated = !$user->isValidated; // Toggle the validation status
        $user->save(); // Save the changes
        $this->getUsers(); // Refresh the users list
    }

    // Open the modal
    public function openModal() {
        $this->isModalOpen = true;
    }

    // Close the modal
    public function closeModal() {
        $this->isModalOpen = false;
        $this->resetForm(); // Reset form fields
    }

    // Reset form fields
    public function resetForm() {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->specialite = '';
        $this->attestation = '';
        $this->image = '';
    }

    // Save a new user
    public function saveUser() {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'isValidated' => true, // Default to validated
        ]);
        $user->assignRole('admin');

        $this->closeModal(); // Close the modal
        $this->getUsers(); // Refresh the users list
    }

    // Sort users by field
    public function sort($field) {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->getUsers(); // Refresh the users list
    }

    // Pagination methods
    public function nextPage() {
        $this->currentPage++;
    }

    public function previousPage() {
        $this->currentPage--;
    }

    public function updatedItemsPerPage() {
        $this->currentPage = 1; // Reset to the first page when items per page changes
    }

    // Method to calculate total pages
    public function totalPages() {
        $this->totalPages = ceil(count($this->users) / $this->itemsPerPage);
    }
};
?>

<div x-data="{ isModalOpen: false }">
    <!-- Add User Button -->
    <div class="mb-4">
        <button @click="isModalOpen = true" class="px-4 py-2 bg-blue-500 text-white rounded-lg">
            Ajouter un admin
        </button>
    </div>

    <!-- Modal for Adding User -->
    <div x-show="isModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg w-1/3">
            <h2 class="text-xl font-bold mb-4">Add Admin User</h2>
            <form wire:submit.prevent="saveUser">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input wire:model="name" type="text" id="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input wire:model="email" type="email" id="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input wire:model="password" type="password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm">
                </div>

                <div class="flex justify-end">
                    <button type="button" @click="isModalOpen = false" class="mr-2 px-4 py-2 bg-gray-500 text-white rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="mb-4">
        <input wire:model.debounce.300ms="search" type="text" placeholder="Search users..." class="px-4 py-2 border rounded-lg">
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th wire:click="sort('name')" class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600 uppercase tracking-wider cursor-pointer">
                        Name
                        <span wire:ignore>
                            @if ($sortField === 'name')
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </span>
                    </th>
                    <th wire:click="sort('email')" class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600 uppercase tracking-wider cursor-pointer">
                        Email
                        <span wire:ignore>
                            @if ($sortField === 'email')
                                {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                            @endif
                        </span>
                    </th>
                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600 uppercase tracking-wider">
                        Specialite
                    </th>
                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600 uppercase tracking-wider">
                        Role
                    </th>
                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600 uppercase tracking-wider">
                        Etat
                    </th>
                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="hover:bg-gray-100">
                        <td class="px-6 py-4 border-b border-gray-200">{{ $user->name }}</td>
                        <td class="px-6 py-4 border-b border-gray-200">{{ $user->email }}</td>
                        <td class="px-6 py-4 border-b border-gray-200">{{ $user->specialite }}</td>
                        <td class="px-6 py-4 border-b border-gray-200">{{ $user->roles->pluck('name')->join(', ') }}</td>
                        <td class="px-6 py-4 border-b border-gray-200">
                            <span class="px-2 py-1 text-sm font-semibold rounded-full {{ $user->isValidated ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->isValidated ? 'Activé' : 'Desactivé' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 border-b border-gray-200">
                            <!-- Toggle Switch -->
                            <div wire:click="toggleValidation({{ $user->id }})" 
                                 class="w-10 h-6 flex items-center rounded-full p-1 cursor-pointer {{ $user->isValidated ? 'bg-green-500' : 'bg-red-500' }}">
                                <div class="bg-white w-4 h-4 rounded-full shadow-md transform transition-transform {{ $user->isValidated ? 'translate-x-4' : 'translate-x-0' }}"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-between items-center mt-4">
        <div>
            <span>Showing</span>
            <select wire:model="itemsPerPage" class="mx-2 px-2 py-1 border rounded-lg">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <span>entries</span>
        </div>
        <div>
            <button wire:click="previousPage" {{ $currentPage === 1 ? 'disabled' : '' }} class="px-4 py-2 bg-gray-200 rounded-lg">Previous</button>
            <span class="mx-2">{{ $currentPage }}</span>
            <button wire:click="nextPage" {{ $currentPage >= $totalPages ? 'disabled' : '' }} class="px-4 py-2 bg-gray-200 rounded-lg">Next</button>
        </div>
    </div>
</div>