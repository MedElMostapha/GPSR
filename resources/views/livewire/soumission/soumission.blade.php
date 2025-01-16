<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;

new class extends Component
{
    use WithFileUploads;

    public $title = '';
    public $abstract = '';
    public $publication_date = '';
    public $journal = '';
    public $articleFile; // For article file upload
    public $ribFile; // For RIB file upload
    public $objects = ['article', 'rib'];

    #[On('file-uploaded')]
    public function handleFileUpload($event)
    {
        if ($event['objet'] == "article") {
            $this->articleFile = $event['filePath'];
            // dd($this->articleFile);
        } elseif ($event['objet'] == "rib") {
            $this->ribFile = $event['filePath'];
            // dd($this->ribFile);
        }
    }

    public function createPublication()
    {
        // Validate data
        $this->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'publication_date' => 'required|date',
            'journal' => 'required|string|max:255',
        ]);

        // Handle file upload
        if ($this->articleFile == null) {
            $this->dispatch("file-required");
            return;
        }

        if ($this->ribFile == null) {
            $this->dispatch("file-required");
            return;
        }

        // Create the publication
        $publication = \App\Models\Publication::create([
            'title' => $this->title,
            'abstract' => $this->abstract,
            'publication_date' => $this->publication_date,
            'journal' => $this->journal,
            'user_id' => auth()->id(),
            'file_path' => $this->articleFile, // Store article file path
            'rib' => $this->ribFile, // Store RIB file path
        ]);

        // Flash success message to session
        session()->flash('message', 'Publication created successfully!');

        // Reset input fields
        $this->resetExcept(['indexation']);

        $this->dispatch('publicationCreated', [
            'message' => 'Publication created successfully!'
        ]);

        // Redirect to publications page    
        $this->redirect(route('publication', absolute: false), navigate: true);
    }
};
?>
<div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Soumission d'une publication</h1>

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
        @foreach ($objects as $index => $objet)
            <div wire:key="{{ $objet }}-{{ $index }}">
                <livewire:inputfile 
                    wire:key="{{ $objet }}-{{ $index }}" 
                    label="{{ $objet }}" 
                    location="{{ $objet }}" 
                    objet="{{ $objet }}" 
                />  
            </div>
        @endforeach

        

        <!-- Buttons -->
        <div class="flex justify-end sm:col-span-2">
            <button type="reset" class="btn-sm rounded bg-red-600 border-none text-white hover:bg-red-500 mr-2">Reset</button>
            <x-primary-button type="submit" class="btn-sm" wire:loading.attr="disabled" wire:target="createPublication">
                <span wire:loading.remove wire:target="createPublication">{{ __('Ajouter') }}</span>
                <x-mary-loading wire:loading wire:target="createPublication"></x-mary-loading>
            </x-primary-button>
        </div>
    </form>
</div>

