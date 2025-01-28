<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\Publication;
use Carbon\Carbon;
new class extends Component
{
    use WithFileUploads;
    public $publication;

    public $title = '';
    public $abstract = '';
    public $publication_date = '';
    public $journal = '';
    public $articleFile; // For article file upload
    public $file_name;
    public $ribFile; // For RIB file upload
    public $rib_name;
    public $objects = ['Article', 'Rib'];


    public function mount(Publication $publication)
    {
        $this->publication = $publication;
        $this->title = $publication->title;
        $this->abstract = $publication->abstract;
        $this->publication_date = $publication->publication_date;
        $this->journal = $publication->journal;
        $this->articleFile = $publication->file_path;
        $this->file_name = $publication->file_name;
        $this->ribFile = $publication->rib;
        $this->rib_name = $publication->rib_name;
    }

    #[On('file-uploaded')]
    public function handleFileUpload($event)
    {
        if ($event['objet'] == "Article") {
            $this->articleFile = $event['filePath'];
            $this->file_name = $event['fileName'];

        } elseif ($event['objet'] == "Rib") {
            $this->ribFile = $event['filePath'];
            $this->rib_name = $event['fileName'];
            // dd($this->ribFile);
        }
    }

    public function editPublication()
    {
        // Validate data
        $this->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
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

        // dd($this->file_name, $this->rib_name);

        // Create the publication
        $publication = $this->publication->update([
            'title' => $this->title,
            'abstract' => $this->abstract,
            'journal' => $this->journal,
            'user_id' => auth()->id(),
            'file_path' => $this->articleFile, // Store article file path
            'rib' => $this->ribFile, // Store RIB file path
            'file_name' => $this->file_name,
            'rib_name' => $this->rib_name
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
    public function viewFile($fileUrl)
    {
        // Redirect to the 'pdf' route with the fileUrl parameter
        return $this->redirect(route('pdf', ['fileUrl' => $fileUrl]), navigate: true);
    }
    public function publier($publicationId){
        $publication = Publication::find($publicationId);
        $publication->isPublished = true;
        $publication->publication_date = Carbon::now();
        $publication->save();
        $this->publication=$publication;
    }
};
?>
<div class="p-4">
    <div class="flex items-center mb-4">

        <a href="{{ route('publication', absolute: false) }}"
            wire:navigate>
            <i class="fas fa-arrow-left"></i>
            {{__('Retour')}}

        </a>
    </div>
    <h1 class="text-2xl font-bold mb-4">Modifier et visualiser une publication</h1>

    <!-- Success message -->
    @if (session()->has('message'))
    <div x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 10000)"
        class="bg-green-500 text-white p-4 rounded-md mb-4 flex items-center justify-between">
        <div class="flex items-center">
            <!-- Icon -->
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6 mr-2"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('message') }}</span>
        </div>
        <button @click="show = false"
            class="text-white bg-transparent hover:bg-gray-700 rounded-full p-1">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif
    @if($publication->isPublished)
    <span class="bg-green-200 text-green-600 text-xs px-2 py-1 rounded-full transition duration-150">
        <i class="fas fa-globe"></i> En ligne
    </span>
    @else
    <span class="bg-blue-200 text-blue-600 text-xs px-2 py-1 rounded-full transition duration-150">
        <i class="fas fa-ban"></i> Non publié
    </span>
    @endif

    @if (!$publication->isPublished)
    <button wire:click="publier({{ $publication->id }})"
        class="btn btn-xs bg-blue-500 border-none text-white hover:bg-green-600">
        <i class="fas fa-paper-plane"></i>
        Publier
    </button>
    @endif

    <!-- Form -->
    <form wire:submit.prevent="editPublication"
        class="space-y-4"
        enctype="multipart/form-data">
        <!-- Title and Journal in one row at the top -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Title Input -->
            <div>
                <label for="title"
                    class="block text-sm font-medium">Titre</label>
                <input type="text"
                    id="title"
                    wire:model.defer="title"
                    class="input input-bordered bg-white w-full" />
                @error('title')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Journal Input -->
            <div>
                <label for="journal"
                    class="block text-sm font-medium">Journal</label>
                <input type="text"
                    id="journal"
                    wire:model.defer="journal"
                    class="input input-bordered bg-white w-full" />
                @error('journal')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Abstract in the middle -->
        <div>
            <label for="abstract"
                class="block text-sm font-medium">Description</label>
            <textarea id="abstract"
                wire:model.defer="abstract"
                class="textarea bg-white textarea-bordered w-full"></textarea>
        </div>
        <!-- File Section -->
        <div class="mt-2 space-y-2">
            @if ($publication->file_path)
            <div class="relative">
                <!-- Badge for "Consulter" -->
                <div class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full hover:bg-blue-600 transition duration-150 cursor-pointer"
                    wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $publication->file_path)) }}')">
                    <i class="fas fa-eye"></i>
                </div>

                <!-- File Name -->
                <div class="bg-gray-100 p-2 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-600 truncate">
                        Fichier : {{ $publication->file_name }}
                    </p>
                </div>
            </div>
            @else
            <p class="text-xs text-red-600">
                <span class="flex items-center space-x-1 bg-red-100 px-3 py-1 rounded-full w-fit">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Fichier manquant</span>
                </span>
            </p>
            @endif

            @if ($publication->rib)
            <div class="relative">
                <!-- Badge for "Consulter" -->
                <div class="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full hover:bg-green-600 transition duration-150 cursor-pointer"
                    wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $publication->rib)) }}')">
                    <i class="fas fa-eye"></i>
                </div>

                <!-- File Name -->
                <div class="bg-gray-100 p-2 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-600 truncate">
                        RIB : {{ $publication->rib_name }}
                    </p>
                </div>
            </div>
            @else
            <p class="text-xs text-red-600">
                <span class="flex items-center space-x-1 bg-red-100 px-3 py-1 rounded-full w-fit">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>RIB manquant</span>
                </span>
            </p>
            @endif

            @if($publication->isPublished)
            <!-- Publication Date -->
            <div class="text-[10px] bg-blue-500 w-fit px-2 py-1 rounded-[5px] text-gray-600">
                <span class="font-semibold text-white">Date de publication :</span>
                <span class="text-black">{{
                    \Carbon\Carbon::parse($publication->publication_date)->locale('fr')->isoFormat('LL')
                    }}</span>
            </div>
            @endif

            <!-- Created At -->
            <div class="text-gray-500">
                <div class="text-[10px] float-end">
                    <span class="font-semibold">Créé :</span>
                    {{ $publication->created_at->locale('fr')->diffForHumans() }}
                </div>

                @if(auth()->user()->hasRole('admin'))
                <div class="text-[10px] float-start">
                    <span class="font-semibold">Publié par :</span>
                    <span wire:click.prevent="viewProfile({{ $publication->user->id }})"
                        class="text-blue-500 hover:text-blue-700 cursor-pointer">{{
                        $publication->user->name
                        }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- File Upload Inputs in one row at the bottom -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ($objects as $index => $objet)
            <div wire:key="{{ $objet }}-{{ $index }}">
                <livewire:inputfile wire:key="{{ $objet }}-{{ $index }}"
                    label="{{ $objet }}"
                    location="{{ $objet }}"
                    objet="{{ $objet }}" />
            </div>
            @endforeach
        </div>

        <!-- Buttons -->
        <div class="flex justify-end">
            <button type="reset"
                class="btn-sm rounded bg-red-600 border-none text-white hover:bg-red-500 mr-2">Reset</button>
            <x-primary-button type="submit"
                class="btn-sm"
                wire:loading.attr="disabled"
                wire:target="createPublication">
                <span wire:loading.remove
                    wire:target="createPublication">{{ __('Ajouter') }}</span>
                <x-mary-loading wire:loading
                    wire:target="createPublication"></x-mary-loading>
            </x-primary-button>
        </div>
    </form>
</div>