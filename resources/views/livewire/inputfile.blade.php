<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
new class extends Component {
    use WithFileUploads; // Pour gérer les uploads de fichiers

    public $file;
    public string $label = 'Téléchargez votre fichier'; // Définir une valeur par défaut pour le label
    public bool $hasError = false; // Propriété pour indiquer si une erreur existe
    public string $errorMessage = ''; 
    public string $location;
    public string $objet;
    protected $rules = [
        'file' => 'required|file|mimes:pdf,doc,docx|max:10240', // PDF, DOC, DOCX, max 10MB
    ];

    protected $messages = [
        'file.required' => 'Le rapport est obligatoire.',
        'file.mimes' => 'Le fichier doit être au format PDF, DOC ou DOCX.',
        'file.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
    ];

    public function updatedFile()
    {
        // Émet un événement dès que le fichier est uploadé
        $this->dispatch('file-uploaded-instant', ['file' => $this->file]);
        $this->uploadFile();

    }

    #[On('file-required')]
    public function fileRequired(){

        $this->hasError = true;
        $this->errorMessage = 'Le rapport est obligatoire.';


    }

    public function uploadFile()
    {
        $this->validate();

        // Enregistrer le fichier et obtenir son chemin
        $filePath = $this->file->store($this->location, 'public');

        // Émet un événement pour notifier que le fichier a été uploadé et traité
        $this->dispatch('file-uploaded', ['filePath' => $filePath,'objet' => $this->objet]);

        // Ajouter ici la logique pour enregistrer les informations dans la base de données, si nécessaire.
        $this->reset('file'); // Réinitialiser le champ après upload
        session()->flash('message', 'Fichier téléchargé avec succès.');
    }
};
?>

<div>
    <div 
        x-data="{ uploaded: false, progress: 0, fileName: '' }"
        x-on:livewire-upload-start="uploaded = true"
        x-on:livewire-upload-finish="uploaded = false"
        x-on:livewire-upload-error="uploaded = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
        x-on:file-uploaded.window="console.log('Fichier uploadé instantanément:', $event.detail)"
    >
        <!-- Label -->
        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
        </label>

        <!-- Custom File Input Container -->
        <div class="relative">
            <!-- Hidden File Input -->
            <input 
                type="file" 
                id="file" 
                wire:model="file" 
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                x-on:change="fileName = $event.target.files[0].name"
            />
            
            <!-- Custom Styled File Input Button -->
            <div class="flex items-center justify-between px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition duration-150 ease-in-out">
                <span class="text-gray-500" x-text="fileName || 'Choisir un fichier'"></span>
                <span class="text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </span>
            </div>
        </div>

        <!-- Display Selected File Name -->
       

        <!-- Error Message -->
        @if ($hasError)
            <span class="text-red-500 text-sm mt-2">{{ $errorMessage }}</span>
        @endif

        <!-- Progress Bar -->
        <div 
            x-show="uploaded"
            class="bg-blue-600 text-xs mt-2 font-medium h-3 text-blue-100 text-center p-0.5 leading-none rounded-full transition-all duration-300" 
            :style="{ width: `${progress}%` }"
        >
            <span class="text-[8px] text-center pb-2" x-text="`${progress}%`"></span>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="mt-4 p-2 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif
</div>