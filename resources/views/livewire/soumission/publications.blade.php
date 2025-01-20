<?php
use App\Models\Publication;
use Livewire\Volt\Component;
use Carbon\Carbon;
use \App\Models\User;
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
        if(auth()->user()->hasRole('admin')){
            
            $this->publications = Publication::whereNotIsArchivedAndIsPublished()->latest()->get();
        }else{
            
            $this->publications = auth()->user()->publications()->whereNotIsArchived()->latest()->get();
        }

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
        $publication->publication_date =Carbon::now()->locale('fr_FR');

        $publication->save();
        $this->publications();
    }

    public function archiver($publicationId){
        $publication = Publication::find($publicationId);
        $publication->isArchived = true;
        $publication->isPublished = false;
        $publication->save();
        $this->publications();
    }
    public function viewProfile($rowId): void
    {
        $user = User::find($rowId);

        $this->redirect(route('show', ['user' => $user]), navigate: true);
    }
    public function modifier($publicationId){
        $publication = Publication::find($publicationId);
        $this->redirect(route('modifier-publication', ['publication' => $publication]), navigate: true);
    }
};
?>

<div class="">
    @if($publications->isNotEmpty())
    <div>
    @if ($viewingFileUrl)
        <div class="bg-white p-6 w-full rounded-lg ">
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
                            @elseif($publication->isPublished)
                                <span class="bg-green-200 text-green-600 text-xs px-2 py-1 rounded-full transition duration-150">
                                    <i class="fas fa-globe"></i> En ligne
                                </span>
                            @else
                            <span class="bg-blue-200 text-blue-600 text-xs px-2 py-1 rounded-full transition duration-150">
                                <i class="fas fa-ban"></i> <!-- Icône de cercle barré -->
                                Non publié
                            </span>

                            @endif
                        </div>
        
                        <!-- Actions (Edit, Archive, Delete) -->
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <!-- Edit Icon -->
                            <button wire:click="modifier({{ $publication->id }})" class="text-blue-500 hover:text-blue-600 transition duration-150">
                                <i class="fas fa-edit"></i>
                            </button>
        
                            <!-- Archive Icon -->
                            <button wire:click="archiver({{ $publication->id }})" class="text-yellow-500 hover:text-yellow-600 transition duration-150">
                                <i class="fas fa-archive"></i>
                            </button>
        
                            <!-- Delete Icon -->
                            <button onclick="confirmDeletion({{ $publication->id }})" class="text-red-500 hover:text-red-600 transition duration-150">
                                <i class="fas fa-trash"></i>
                            </button>
                            
                            @if (!$publication->isPublished)
                            <button  wire:click="publier({{ $publication->id }})"  class="btn btn-xs bg-blue-500 border-none text-white hover:bg-green-600">
                                <i class="fas fa-paper-plane"></i>
                                Publier
                            </button>
                            @endif
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
                        <div x-data="{ showMore: false }" class="text-sm text-gray-600">
                            <span class="font-semibold">Description :</span>
                            <div class="w-full bg-gray-100 pl-2 pr-2 pt-2 pb-2 rounded-lg">
                                <p x-show="!showMore" class="text-sm text-gray-600 whitespace-pre-line">
                                    {{ Str::words($publication->abstract, 15, '...') }}
                                </p>
                                <p x-show="showMore" class="text-sm text-gray-600 whitespace-pre-line">
                                    {{ $publication->abstract }}
                                </p>
                                <button 
                                    @click="showMore = !showMore" 
                                    class="text-blue-500 hover:text-blue-700 focus:outline-none mt-2"
                                >
                                    <span x-text="showMore ? 'Lire moins' : 'Lire plus'"></span>
                                </button>
                            </div>
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


                            @if($publication->isPublished)
                             <!-- Publication Date -->
                             <div class="text-[10px] bg-blue-500 w-fit px-2 py-1 rounded-[5px]   text-gray-600">
                                <span class="font-semibold text-white">Date de publication :</span>
                                <span class="text-black">{{ \Carbon\Carbon::parse($publication->publication_date)->locale('fr')->isoFormat('LL') }}</span>
                            </div>
                            @endif
                             <!-- Created At -->
                             <div class=" text-gray-500">
                                <div class="text-[10px] float-end">

                                    <span class="font-semibold">Créé :</span>
                                    {{ $publication->created_at->locale('fr')->diffForHumans() }}
                                </div>

                                @if(auth()->user()->hasRole('admin'))
                                <div class="text-[10px] float-start">
                                <span class="font-semibold">Publié par :</span>
                                    <span wire:click.prevent="viewProfile({{ $publication->user->id }})" class="text-blue-500 hover:text-blue-700 cursor-pointer">{{ $publication->user->name }}</span>
                                </div>
                                @endif
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
                    Aucune publication disponible
                </span>
            </div>
        </div>
    @endif
</div>

<script>
    function confirmDeletion(publicationId) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action est irréversible !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            // Dispatch the Livewire event with the publication ID
            Livewire.dispatch('deletePublication', { publicationId: publicationId });
            Swal.fire({
                title: 'Supprimé !',
                text: 'La publication a été supprimée.',
                icon: 'success',
                showConfirmButton: false,
                timer: 1500
                
            });
        }
    });
}
</script>
