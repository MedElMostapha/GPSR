<?php
use App\Models\Publication;
use Livewire\Volt\Component;

new class extends Component {
    public $publications;
    public $visibleCount = 6; // Number of publications to show initially
    public $viewingFileUrl = null;
    public $loading = false; // Track loading state
    protected $listeners = ['deletePublication'];

    public function mount()
    {

        // Fetch publications for the authenticated user
        $this->publications();
    }

    public function publications(){
        $this->publications = auth()->user()->publications()->where('isArchived', true)->latest()->get();

    }

    public function viewFile($fileUrl)
    {
        $this->viewingFileUrl = $fileUrl;
    }

    // Method to load more publications
    public function loadMore()
    {
        $this->loading = true; // Set loading to true when starting to load more
        $this->visibleCount += 6; // Increase the count by 6 when "Show More" is clicked
        
        // Simulate a delay (you can remove this part in production)
        // sleep(1);
        
        $this->loading = false; // Set loading to false after publications are loaded
    }

        public function deletePublication($publicationId)
    {
        // Find the publication by ID
        $publication = Publication::find($publicationId);

        // Check if the publication exists
        if ($publication) {
            // Delete the publication
            $publication->delete();

            // Refresh the publications list
            $this->publications();
        }
    }
    public function publier($publicationId){
        $publication = Publication::find($publicationId);
        $publication->isPublished = true;
        $publication->save();
        $this->publications();
    }

    public function desarchiver($publicationId){
        $publication = Publication::find($publicationId);
        $publication->isArchived = false;
        $publication->save();
        $this->publications();
    }
};
?>

<div class="p-6 bg-gray-100 min-h-screen flex items-center justify-center">
    @if($publications->isNotEmpty())
        <div>
            @if ($viewingFileUrl)
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <button class="mb-4 px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300 transition duration-150 ease-in-out" 
                        wire:click="$set('viewingFileUrl', null)">
                        <i class="fas fa-arrow-left"></i> Retour
                    </button>
                    @livewire('pdf.pdfviewer', ['fileUrl' => $viewingFileUrl])
                </div>
            @else
                <!-- Publications list -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($publications->take($visibleCount) as $publication)
                        <div class="card bg-white shadow-lg rounded-lg p-5 hover:shadow-xl z-0 transition duration-200 relative">
                            <!-- Status and Actions -->
                            <div class="flex justify-center mb-4">
                                <!-- Status Badge -->
                                <div class="flex items-center space-x-2 absolute top-2 left-2">
                                    @if ($publication->isArchived)
                                        <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full transition duration-150">
                                            <i class="fas fa-archive"></i> Archivée
                                        </span>
                                    @else
                                        <span class="bg-green-200 text-green-600 text-xs px-2 py-1 rounded-full transition duration-150">
                                            <i class="fas fa-globe"></i> En ligne
                                        </span>
                                    @endif
                                </div>
                
                                <!-- Actions (Edit, Archive, Delete) -->
                                <div class="absolute top-2 right-2 flex space-x-2">
                                    <!-- Archive Icon -->
                                    <button wire:click="desarchiver({{ $publication->id }})" class="text-yellow-500 hover:text-yellow-600 transition duration-150">
                                        <i class="fas fa-recycle"></i>
                                    </button>
                                </div>
                            </div>
                
                            <!-- Content -->
                            <div class="space-y-4">
                                <!-- Title -->
                                <div class="mb-2">
                                    <span class="text-sm text-gray-500 font-medium uppercase tracking-wider">Titre</span>
                                    :
                                    <span class="font-bold text-lg bg-gray-200 px-4 py-1 rounded text-gray-800 mt-1">{{ $publication->title }}</span>
                                </div>
                            
                                <div class="flex gap-4">
                                    <!-- Section 1 -->
                                    <div class="flex-1 space-y-2">
                                        <!-- Journal -->
                                        <div class="text-sm text-gray-600">
                                            <span class="font-semibold">Journal :</span> {{ $publication->journal }}
                                        </div>
                                    </div>
                                </div>
                            
                                <!-- Abstract (on a single line) -->
                                <div class="text-sm text-gray-600 truncate">
                                    <span class="font-semibold">Description :</span>
                                    <div class="w-full bg-gray-100 pl-2 pr-2 pt-0 rounded-lg h-20 ">
                                        <p class="text-sm text-gray-600 whitespace-pre-line">
                                            {{ Str::words($publication->abstract, 15, '...') }}
                                        </p>
                                    </div>
                                </div>
                            
                                <!-- File Section -->
                                <div class="mt-2 space-y-2">
                                    @if ($publication->file_path)
                                        <div class="relative">
                                            <!-- Badge for "Consulter" -->
                                            @if(!$publication->isArchived)
                                            <div   class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full hover:bg-blue-600 transition duration-150 cursor-pointer"
                                                wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $publication->file_path)) }}')">
                                                <i class="fas fa-eye"></i>
                                            </div>
                                            @endif
                            
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
                                            @if(!$publication->isArchived)
                                            <!-- Badge for "Consulter" -->
                                            <div class="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full hover:bg-green-600 transition duration-150 cursor-pointer"
                                                wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $publication->rib)) }}')">
                                                <i class="fas fa-eye"></i>
                                            </div>
                                            @endif
                            
                                            <!-- File Name -->
                                            <div class="bg-gray-100 p-2 rounded-lg border border-gray-200">
                                                <p class="text-xs text-gray-600 truncate">
                                                    RIB : {{$publication->rib_name }}
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


                                    @if($publication->isPublished && !$publication->isArchived)
                                     <!-- Publication Date -->
                                     <div class="text-[10px] bg-blue-500 w-fit px-2 py-1 rounded-[5px]   text-gray-600">
                                        <span class="font-semibold text-white">Date de publication :</span>
                                        <span class="text-black">{{ \Carbon\Carbon::parse($publication->publication_date)->format('d/m/Y') }}</span>
                                    </div>
                                    @endif
                                     <!-- Created At -->
                                     <div class="text-[10px] float-end text-gray-500">
                                        <span class="font-semibold">Créé :</span>
                                        {{ $publication->created_at->locale('fr')->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- Show More Button -->
                @if ($publications->count() > $visibleCount)
                    <div class="text-center mt-6">
                        <!-- Hide the Show More button if loading is true -->
                        <x-secondary-button type="button" wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore">
                            <span wire:loading.remove wire:target="loadMore">{{ __('Plus') }}</span>
                            <x-mary-loading class=" loading-bars" wire:loading wire:target="loadMore">
                                {{-- <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div> --}}
                            </x-mary-loading>
                        </x-secondary-button>
                    </div>
                @endif
            @endif
        </div>
    @else
        <div class="text-center mt-6 h-full w-full border p-10 bg-white rounded-lg shadow-lg max-w-2xl">
            <div class="text-gray-500 text-lg">
                <i class="fas fa-box-open text-4xl mb-4"></i> 
                <span class="block text-2xl">
                    Aucune archive disponible
                </span>
            </div>
        </div>
    @endif
</div>


