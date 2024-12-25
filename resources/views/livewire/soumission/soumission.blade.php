<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $title = '';
    public $abstract = '';
    public $publication_date = '';
    public $journal = '';
    public $file; // For file upload
    public $rib; // For file upload

    public function createPublication()
{
    // Validate data
    $this->validate([
        'title' => 'required|string|max:255',
        'abstract' => 'nullable|string',
        'publication_date' => 'required|date',
        'journal' => 'required|string|max:255',
        'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // File validation
        'rib' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // File validation
    ]);

    // Handle file upload
    $filePath = null;
    
    if ($this->file) {
        $filePath = $this->file->store('publications', 'public');
    }

    $rib = null;
    if ($this->rib) {
        $rib = $this->rib->store('rib', 'public');
    }

    // Create the publication
    $publication = \App\Models\Publication::create([
        'title' => $this->title,
        'abstract' => $this->abstract,
        'publication_date' => $this->publication_date,
        'journal' => $this->journal,
        'user_id' => auth()->id(),
        'file_path' => $filePath, // Store file path if file uploaded
        'rib' => $rib, // Store file path if file uploaded
    ]);

    // Flash success message to session
    session()->flash('message', 'Publication created successfully!');

    // Reset input fields
    $this->resetExcept(['indexation']);
}


   
};
?>
<div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Publications</h1>

    <!-- Success message -->
    @if (session()->has('message'))
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => show = false, 10000)" 
            class="bg-green-500 text-white p-4 rounded-md mb-4 flex items-center justify-between"
        >
            <div class="flex items-center">
                <!-- Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('message') }}</span>
            </div>
            <button @click="show = false" class="text-white bg-transparent hover:bg-gray-700 rounded-full p-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    <!-- Form -->
    <form wire:submit.prevent="createPublication" class="grid grid-cols-1 sm:grid-cols-2 gap-4" enctype="multipart/form-data">
        <!-- Title Input -->
        <div>
            <label for="title" class="block text-sm font-medium">Title</label>
            <input type="text" id="title" wire:model.defer="title" class="input input-bordered bg-white w-full" />
            @error('title') 
                <span class="text-red-500 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Abstract Input -->
        <div>
            <label for="abstract" class="block text-sm font-medium">Resumé</label>
            <textarea id="abstract" wire:model.defer="abstract" class="textarea bg-white textarea-bordered w-full"></textarea>
        </div>

        <!-- Publication Date Input -->
        <div>
            <label for="publication_date" class="block text-sm font-medium">Date de publication</label>
            <input type="date" id="publication_date" wire:model.defer="publication_date" class="input input-bordered bg-white w-full" />
            @error('publication_date') 
                <span class="text-red-500 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Journal Input -->
        <div>
            <label for="journal" class="block text-sm font-medium">Journal</label>
            <input type="text" id="journal" wire:model.defer="journal" class="input input-bordered bg-white w-full" />
            @error('journal') 
                <span class="text-red-500 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <!-- File Upload Input -->
        <div>
            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Article</label>
            <input 
                type="file" 
                id="file" 
                wire:model="file" 
                class="block w-full py-2 px-4 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
            />
            @error('file') 
                <span class="text-red-500 text-sm mt-2">{{ $message }}</span> 
            @enderror
        </div>
    
        <!-- RIB File Input -->
        <div>
            <label for="rib" class="block text-sm font-medium text-gray-700 mb-2">RIB</label>
            <input 
                type="file" 
                id="rib" 
                wire:model="rib" 
                class="block w-full py-2 px-4 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
            />
            @error('rib') 
                <span class="text-red-500 text-sm mt-2">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Buttons -->
        <div class="flex justify-end sm:col-span-2">
            <button type="reset" class="btn btn-secondary mr-2">Reset</button>
            <button type="submit" class="btn btn-primary">Create</button>
        </div>
    </form>
</div>

