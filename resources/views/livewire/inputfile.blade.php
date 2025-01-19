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
    public function fileRequired()
    {
        $this->hasError = true;
        $this->errorMessage = 'Le rapport est obligatoire.';
    }

    public function uploadFile()
    {
        $this->validate();

        // Enregistrer le fichier et obtenir son chemin
        $filePath = $this->file->store($this->location, 'public');

        // Extraire le nom du fichier
        $fileName = $this->file->getClientOriginalName();

        // Émet un événement pour notifier que le fichier a été uploadé et traité
        $this->dispatch('file-uploaded', [
            'filePath' => $filePath,
            'fileName' => $fileName,
            'objet' => $this->objet,
        ]);

        // Réinitialiser le champ après upload
        $this->reset('file');

        // Afficher un message de succès
        session()->flash('message', 'Fichier téléchargé avec succès.');
    }

    public function resetFile()
    {
        $this->reset('file');
        $this->hasError = false;
        $this->errorMessage = '';
    }
};
?>

<div>
    <div 
        class="w-full flex flex-col items-center space-y-4" 
        x-data="{ 
            uploaded: false, 
            progress: 0, 
            fileName: '', 
            fileSize: '', 
            fileUrl: '', 
            uploadedFileName: '', 
            isDragging: false,
            isLoading: false,
            filePreview: null,
            isSuccess: false,
            fileType: '' // Add fileType to store the file extension
        }"
        x-on:livewire-upload-start="
            isLoading = true;
            progress = 0; // Reset progress when upload starts
        "
        x-on:livewire-upload-finish="
            isLoading = false; 
            uploaded = true; 
            isSuccess = true; // Set success state
            fileUrl = $event.detail.filePath;
            uploadedFileName = $event.detail.fileName;
        "
        x-on:livewire-upload-error="
            isLoading = false;
            isSuccess = false; // Reset success state on error
        "
        x-on:livewire-upload-progress="
            progress = $event.detail.progress; // Update progress
        "
        x-on:dragover.prevent="isDragging = true"
        x-on:dragleave.prevent="isDragging = false"
        x-on:drop.prevent="
            isDragging = false;
            const files = $event.dataTransfer.files;
            if (files.length) {
                isLoading = true; // Manually set isLoading to true
                fileName = files[0].name;
                fileSize = (files[0].size / 1024).toFixed(2) + ' KB'; // Convert to KB
                filePreview = URL.createObjectURL(files[0]);
                fileType = fileName.split('.').pop().toLowerCase(); // Extract file extension
                $wire.upload('file', files[0], {
                    progress: (value) => {
                        progress = value; // Update progress dynamically
                    }
                }).then(() => {
                    isLoading = false;
                }).catch(() => {
                    isLoading = false;
                    progress = 0;
                });
            }
        "
    >
        <!-- Drag and Drop Zone -->
        <div class="w-full ">
            <span class="block text-sm font-medium float-start">

                {{$label}}
            </span>
        </div>
        <div 
            class="relative w-full h-20 bg-gray-100 border-2 border-dashed rounded-lg flex items-center justify-center transition-all duration-300"
            :class="{ 
                'border-blue-500 bg-blue-50': isDragging, 
                'border-gray-300': !isDragging,
                'border-green-500 bg-green-50': isSuccess
            }"
        >
            <!-- Loading Spinner with Percentage and File Info -->
            <template x-if="isLoading">
                <div class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90 rounded-lg p-2">
                    <!-- Circular Progress and File Info -->
                    <div class="flex items-center space-x-4">
                        <!-- Circular Progress -->
                        <div class="relative w-12 h-12">
                            <svg class="w-full h-full" viewBox="0 0 36 36">
                                <path 
                                    class="text-gray-200" 
                                    stroke="currentColor" 
                                    stroke-width="2" 
                                    fill="none" 
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                                <path 
                                    class="text-blue-500" 
                                    stroke="currentColor" 
                                    stroke-width="2" 
                                    stroke-dasharray="100, 100" 
                                    :stroke-dashoffset="100 - progress" 
                                    fill="none" 
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                            </svg>
                            <!-- Percentage Text -->
                            <span class="absolute inset-0 flex items-center justify-center text-xs font-medium text-blue-500" x-text="`${progress}%`"></span>
                        </div>
                        <!-- File Name and Size -->
                        <div class="text-left">
                            <span x-text="fileName" class="text-sm text-gray-900 truncate block"></span>
                            <span x-text="fileSize" class="text-xs text-gray-500"></span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- File Preview -->
            <template x-if="filePreview && !isLoading">
                <div class="absolute inset-0 flex items-center justify-between bg-white bg-opacity-90 rounded-lg p-2">
                    <!-- File Icon based on file extension -->
                    <div class="flex items-center space-x-2">
                        <template x-if="fileType === 'pdf'">
                            <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                        </template>
                        <template x-if="fileType === 'doc' || fileType === 'docx'">
                            <i class="fas fa-file-word text-blue-500 text-2xl"></i>
                        </template>
                        <template x-if="fileType === 'xls' || fileType === 'xlsx'">
                            <i class="fas fa-file-excel text-green-500 text-2xl"></i>
                        </template>
                        <template x-if="fileType === 'ppt' || fileType === 'pptx'">
                            <i class="fas fa-file-powerpoint text-orange-500 text-2xl"></i>
                        </template>
                        <template x-if="fileType === 'txt'">
                            <i class="fas fa-file-alt text-gray-500 text-2xl"></i>
                        </template>
                        <template x-if="!['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'].includes(fileType)">
                            <i class="fas fa-file text-gray-500 text-2xl"></i>
                        </template>
                        <span x-text="fileName" class="text-sm text-gray-900 truncate"></span>
                    </div>
                    <!-- Remove Button (X Icon) -->
                    <button 
                        type="button" 
                        class="text-gray-400 hover:text-red-500"
                        x-on:click="
                            filePreview = null;
                            fileName = '';
                            fileSize = '';
                            isSuccess = false;
                            $wire.resetFile(); // Call the resetFile method
                        "
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </template>

            <!-- Upload Icon and Instructions -->
            <template x-if="!filePreview && !isLoading">
                <div class="text-center flex flex-col items-center">
                    <svg 
                        class="h-6 w-6 text-gray-400 mb-1" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24" 
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <p class="text-xs text-gray-600">
                        <span class="font-medium text-blue-600 hover:text-blue-500">Cliquez pour télécharger</span>
                        ou glissez-déposez un fichier
                    </p>
                </div>
            </template>

            <!-- Hidden File Input -->
            <input 
                type="file" 
                id="file" 
                wire:model="file" 
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                x-on:change="
                    fileName = $event.target.files[0].name;
                    fileSize = ($event.target.files[0].size / 1024).toFixed(2) + ' KB'; // Convert to KB
                    filePreview = URL.createObjectURL($event.target.files[0]);
                    fileType = fileName.split('.').pop().toLowerCase(); // Extract file extension
                "
            />
        </div>

        <!-- Error Message -->
        @if ($hasError)
            <span class="text-red-500 text-xs mt-2 text-center block">{{ $errorMessage }}</span>
        @endif
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="mt-2 p-2 bg-green-100 border border-green-400 text-green-700 rounded text-center text-xs">
            {{ session('message') }}
        </div>
    @endif
</div>