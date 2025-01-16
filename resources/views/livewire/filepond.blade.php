<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

new class extends Component {
    use WithFileUploads; // For handling file uploads

    public $file; // Property to hold the uploaded file
    public string $label = 'Upload your file'; // Default label
    public bool $hasError = false; // Property to indicate if an error exists
    public string $errorMessage = ''; // Error message
    public string $location; // Storage location
    public string $objet; // Object identifier

    protected $rules = [
        'file' => 'required|file|mimes:pdf,doc,docx|max:10240', // PDF, DOC, DOCX, max 10MB
    ];

    protected $messages = [
        'file.required' => 'The file is required.',
        'file.mimes' => 'The file must be in PDF, DOC, or DOCX format.',
        'file.max' => 'The file must not exceed 10 MB.',
    ];

    public function saveFile($uploadedFilename)
    {
        // Validate the uploaded file
        $this->validate();

        // Move the file to permanent storage
        $filePath = $this->file->store($this->location, 'public');

        // Dispatch an event to notify that the file has been uploaded and processed
        $this->dispatch('file-uploaded', ['filePath' => $filePath, 'objet' => $this->objet]);

        // Clear the temporary file
        $this->reset('file');

        // Optionally, you can return a success message or perform other actions
        session()->flash('message', 'File uploaded successfully!');
    }

    #[On('file-required')]
    public function fileRequired()
    {
        $this->hasError = true;
        $this->errorMessage = 'The file is required.';
    }
};
?>

<div>
    <div wire:ignore x-data x-init="
        // Register the FilePond image preview plugin
        FilePond.registerPlugin(FilePondPluginImagePreview);

        // Initialize FilePond with image preview
        FilePond.setOptions({
            allowImagePreview: true, // Enable image preview
            instantUpload: true, // Enable instant upload
            credits: false,
            server: {
                process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                    // Use Livewire's upload method
                    @this.upload('file', file, (uploadedFilename) => {
                        load(uploadedFilename); // Notify FilePond that the upload is complete

                        // Call the saveFile method to move the file to permanent storage
                        @this.call('saveFile', uploadedFilename);
                    }, () => {
                        error('Upload failed'); // Notify FilePond that the upload failed
                    }, (event) => {
                        progress(event.detail.progress); // Update upload progress
                    });

                    return {
                        abort: () => {
                            abort(); // Allow FilePond to abort the upload
                        },
                    };
                },
                revert: (filename, load) => {
                    @this.removeUpload('file', filename, () => {
                        load(); // Notify FilePond that the file has been removed
                    });
                },
            },
        });

        // Initialize FilePond
        const pond = FilePond.create($refs.input);
    ">
        <!-- Label -->
        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
        </label>

        <!-- File input for single file upload -->
        <input type="file" id="file" x-ref="input" wire:model="file" />

        <!-- Message d'erreur -->
        @if ($hasError)
            <span class="text-red-500 text-sm mt-2">{{ $errorMessage }}</span>
        @endif
    </div>

    <!-- Display a success message if the file is uploaded -->
    @if (session()->has('message'))
        <div class="mt-4 p-2 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <!-- Display an error message if no file was uploaded -->
    @if (session()->has('error'))
        <div class="mt-4 p-2 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif
</div>