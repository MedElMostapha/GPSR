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
    public $impact_factor = null;
    public $indexation = 'Scopus';
    public $file; // For file upload
    public $authors = [
        ['name' => '', 'email' => '']
    ]; // Authors array

    public function createPublication()
    {
        // Validate data
        $this->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'publication_date' => 'required|date',
            'journal' => 'required|string|max:255',
            'impact_factor' => 'nullable|numeric',
            'indexation' => 'required|in:Scopus,Web of Science,Other',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // File validation
            'authors' => 'required|array|min:1',
            'authors.*.name' => 'required|string|max:255',
            'authors.*.email' => 'nullable|email',
        ]);

        // Handle file upload
        $filePath = null;
        if ($this->file) {

            
            $filePath = $this->file->store('publications', 'public');
        
        }

        // Create the publication
        $publication = \App\Models\Publication::create([
            'title' => $this->title,
            'abstract' => $this->abstract,
            'publication_date' => $this->publication_date,
            'journal' => $this->journal,
            'impact_factor' => $this->impact_factor,
            'indexation' => $this->indexation,
            'user_id' => auth()->id(),
            'file_path' => $filePath, // Store file path if file uploaded
        ]);

        // Add authors to the publication
        foreach ($this->authors as $author) {
            $publication->authors()->create($author);
        }

        // Reset input fields
        $this->resetExcept(['indexation']);
    }

   
};
?>
<div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Publications</h1>

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
            <label for="abstract" class="block text-sm font-medium">Abstract</label>
            <textarea id="abstract" wire:model.defer="abstract" class="textarea bg-white textarea-bordered w-full"></textarea>
        </div>

        <!-- Publication Date Input -->
        <div>
            <label for="publication_date" class="block text-sm font-medium">Publication Date</label>
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

        <!-- Impact Factor Input -->
        <div>
            <label for="impact_factor" class="block text-sm font-medium">Impact Factor</label>
            <input type="number" id="impact_factor" wire:model.defer="impact_factor" class="input input-bordered bg-white w-full" step="0.01" />
        </div>

        <!-- Indexation Select -->
        <div>
            <label for="indexation" class="block text-sm font-medium">Indexation</label>
            <select id="indexation" wire:model.defer="indexation" class="select select-bordered bg-white w-full">
                <option value="Scopus">Scopus</option>
                <option value="Web of Science">Web of Science</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <!-- File Upload Input -->
        <div>
            <label for="file" class="block text-sm font-medium">File</label>
            <input type="file" id="file" wire:model="file" class="input input-bordered bg-white w-full" />
            @error('file') 
                <span class="text-red-500 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Authors Input -->
        <div class="sm:col-span-2">
            <label for="authors" class="block text-sm font-medium">Auteurs</label>
            <div>
                @foreach ($authors as $index => $author)
                    <div class="flex items-center gap-4 mb-2">
                        <input type="text" wire:model.defer="authors.{{ $index }}.name" class="input input-bordered bg-white w-full" placeholder="Author Name" />
                        <input type="email" wire:model.defer="authors.{{ $index }}.email" class="input input-bordered bg-white w-full" placeholder="Author Email (optional)" />
                    </div>
                    @error("authors.{$index}.name") 
                        <span class="text-red-500 text-sm">{{ $message }}</span> 
                    @enderror
                    @error("authors.{$index}.email") 
                        <span class="text-red-500 text-sm">{{ $message }}</span> 
                    @enderror
                @endforeach
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end sm:col-span-2">
            <button type="reset" class="btn btn-secondary mr-2">Reset</button>
            <button type="submit" class="btn btn-primary">Create</button>
        </div>
    </form>



</div>
