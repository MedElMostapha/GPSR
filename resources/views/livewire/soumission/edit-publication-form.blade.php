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
    public $motif="";
    public $type='';


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
        $this->type=$publication->type;
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
            'type' => 'required|string|max:255',
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
            'rib_name' => $this->rib_name,
            'type'=>$this->type,
            'prix'=>$this->type =='WEB SCIENCE' ? 4000 : 2000,

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

    public function valider($publicationId){
        $publication = Publication::find($publicationId);
        $publication->isAccepted = true;
        $publication->save();
        $this->publication=$publication;
    }

    public function refuser(){
        $this->publication->isAccepted = false;
        $this->publication->motifs = $this->motif;
        $this->publication->save();
    }
};
?>
<div class="p-4 relative">
    <!-- Back Button -->
    <div class="flex items-center mb-4">
        <a href="{{ route('publication', absolute: false) }}"
            wire:navigate
            class="text-blue-500 hover:text-blue-700 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            {{ __('Retour') }}
        </a>
    </div>

    <!-- Page Title -->
    <h1 class="text-2xl font-bold mb-4">{{__('Modifier et visualiser une publication')}}</h1>

    <!-- Top Right Section -->
    <div class="absolute top-4 right-4 flex items-center space-x-2">

        <!-- Publication Status -->
        @if($publication->isPublished && !$publication->isAccepted && empty($publication->motifs))
        <span class="bg-yellow-200 text-yellow-600 text-xs px-2 py-1 rounded-md transition duration-150">
            <i class="fas fa-clock"></i> {{__('En cours de revision')}}
        </span>
        @elseif($publication->isPublished && $publication->isAccepted)
        <span class="bg-green-200 text-green-600 text-xs px-2 py-1 rounded-md transition duration-150">
            <i class="fas fa-globe"></i> {{__('En ligne')}}
        </span>
        @elseif($publication->isPublished && !$publication->isAccepted && !empty($publication->motifs))
        <span class="bg-red-200 text-red-600 text-xs px-2 py-1 rounded-md transition duration-150">
            <i class="fas fa-times"></i> {{__('Refusée')}}
            par motif : {{ $publication->motifs }}

        </span>
        @endif
        @if(!$publication->isPublished )
        <button wire:click="publier({{ $publication->id }})"
            class="btn btn-xs bg-blue-500 border-none text-white hover:bg-blue-600">
            <i class="fas fa-paper-plane"></i>{{__('Publier')}}
        </button>
        @endif

        @if (auth()->user()->hasRole('admin') && $publication->isPublished && !$publication->isAccepted)

        <div x-data="{ open: false, selectedId: null, motif: '' }">


            @if($publication->isPublished && auth()->user()->hasRole('admin'))
            <!-- Boutons Valider / Refuser -->
            <button wire:click="valider({{ $publication->id }})"
                class="btn btn-xs bg-green-500 hover:bg-green-600 text-white border-none transition duration-150">
                <i class="fas fa-check"></i> Valider
            </button>

            <button @click="open = true; selectedId = {{ $publication->id }}"
                class="btn btn-xs bg-red-500 hover:bg-red-600 text-white border-none transition duration-150">
                <i class="fas fa-times"></i> Refuser
            </button>
            @endif


            <!-- Modal Alpine.js -->
            <div x-show="open"
                class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50"
                x-cloak>

                <div class="bg-white p-5 rounded-lg shadow-lg w-1/3">
                    <h2 class="text-lg font-semibold text-gray-700">Motif du refus</h2>

                    <form action=""
                        wire:submit='refuser'>

                        <!-- Textarea pour saisir le motif -->
                        <textarea x-model="motif"
                            wire:model.defer="motif"
                            class="w-full border p-2 mt-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Expliquez la raison du refus...">
                        </textarea>

                        <div class="mt-4 flex justify-end">
                            <button @click="open = false"
                                class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-md mr-2">
                                Annuler
                            </button>

                            <button @click="$wire.refuser(selectedId, motif); open = false"
                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md">
                                Confirmer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif








    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
    <div x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 10000)"
        class="bg-green-500 text-white p-4 rounded-md mb-4 flex items-center justify-between">
        <div class="flex items-center">
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

    <!-- Form -->
    <form wire:submit.prevent="editPublication"
        class="space-y-4"
        enctype="multipart/form-data">
        <!-- Title and Journal Inputs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="title"
                    class="block text-sm font-medium text-gray-700">Titre</label>
                <input type="text"
                    id="title"
                    wire:model.defer="title"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
                @error('title')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="journal"
                    class="block text-sm font-medium text-gray-700">Journal</label>
                <input type="text"
                    id="journal"
                    wire:model.defer="journal"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
                @error('journal')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Type Input -->
        <div>
            <label for="type"
                class="block text-sm font-medium">Type</label>
            <select id="type"
                wire:model="type"
                class="input input-bordered bg-white w-full">
                <option value="">Type</option>
                <option value="WEB SCIENCE">WEB SCIENCE</option>
                <option value="SCOPUS">SCOPUS</option>
            </select>
            @error('type')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>



        <!-- Abstract Input -->
        <div>
            <label for="abstract"
                class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="abstract"
                wire:model.defer="abstract"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
        </div>

        <!-- File Section -->
        <div class="mt-2 space-y-2">
            @if ($publication->file_path)
            <div class="relative">
                <div class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full hover:bg-blue-600 transition duration-150 cursor-pointer"
                    wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $publication->file_path)) }}')">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="bg-gray-100 p-2 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-600 truncate">Article : {{ $publication->file_name }}</p>
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
                <div class="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full hover:bg-green-600 transition duration-150 cursor-pointer"
                    wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $publication->rib)) }}')">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="bg-gray-100 p-2 rounded-lg border border-gray-200">
                    <p class="text-xs text-gray-600 truncate">RIB : {{ $publication->rib_name }}</p>
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
        </div>

        <!-- File Upload Inputs -->
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


        @if (!auth()->user()->hasRole('admin'))
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

        @endif
        <!-- Form Buttons -->
    </form>
</div>