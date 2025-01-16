<?php

use Livewire\Volt\Component;
use App\Models\Mobilite;
new class extends Component {
    public $mobilites;
    public $visibleCount = 6; // Number of publications to show initially
    public $viewingFileUrl = null;
    public $loading = false; // Track loading state
    protected $listeners = ['deleteMobilitie'];

    public function mount()
    {

        // Fetch publications for the authenticated user
        $this->mobilites();
    }

    public function mobilites(){
        $this->mobilites = auth()->user()->mobilites()->latest()->get();

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
        sleep(1);
        
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

    public function deleteMobilitie($mobilitieId)
    {
        // Find the publication by ID
        $mobilitie = Mobilite::find($mobilitieId);

        // Check if the publication exists
        if ($mobilitie) {
            // Delete the publication
            $mobilitie->delete();

            // Refresh the publications list
            $this->mobilites();
        }
    }

}; 
?>

<div class="p-6 bg-gray-100 min-h-screen">
    @if ($viewingFileUrl)
        <div class="bg-white p-6 rounded-lg shadow-md">
            <button class="mb-4 px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300 transition duration-150 ease-in-out" 
                wire:click="$set('viewingFileUrl', null)">
                <i class="fas fa-arrow-left"></i> Retour
            </button>
            @livewire('pdf.pdfviewer', ['fileUrl' => $viewingFileUrl])
            
        </div>
    @else
        <h1 class="text-3xl font-extrabold text-gray-800 mb-6">Mobilités</h1>

        <!-- Publications list -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($mobilites->take($visibleCount) as $mobilite)
                <div class="card bg-white shadow-lg rounded-lg p-5 hover:shadow-xl z-0 transition duration-200 relative">
                    <!-- Top right icons -->
                    <div class="absolute top-2 right-2 flex space-x-2">
                        <!-- Edit Icon -->
                        <button wire:click="editPublication({{ $mobilite->id }})" class="text-blue-500 hover:text-blue-600 transition duration-150">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <!-- Archive Icon -->
                        <button wire:click="archivePublication({{ $mobilite->id }})" class="text-yellow-500 hover:text-yellow-600 transition duration-150">
                            <i class="fas fa-archive"></i>
                        </button>
                        
                        <!-- Delete Icon -->
                        <!-- Delete Icon -->
                        <button onclick="confirmDeletion({{ $mobilite->id }})"  class="text-red-500 hover:text-red-600 transition duration-150">
                            <i class="fas fa-trash"></i>
                        </button>

                    </div>
                    
                    <!-- Publication Content -->
                    <h2 class="font-bold text-xl text-gray-800 mb-2">{{ $mobilite->labo_accueil }}</h2>
                    <p class="text-sm text-gray-600">{{ $mobilite->type }}</p>
                    <p class="text-sm text-gray-600">{{ $mobilite->ville }}</p>
                    <p class="text-sm mt-3 text-gray-700 leading-snug">{{ $mobilite->pays }}</p>

                    <div class="mt-4 space-y-3">
                        @if ($mobilite->rapport_mobilite)
                            <div class="flex items-center text-blue-500 text-sm hover:text-blue-600 transition duration-150 cursor-pointer" 
                                wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $mobilite->rapport_mobilite)) }}')">
                                <i class="fas fa-file-pdf mr-2"></i> Voir l'article
                            </div>
                        @endif
                       
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Show More Button -->
        @if ($mobilites->count() > $visibleCount)
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

<script>
    function confirmDeletion(mobiliteId) {
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
            Livewire.dispatch('deleteMobilitie', { mobilitieId: mobiliteId });
            Swal.fire({
                title: 'Supprimé !',
                text: 'La mobilité a été supprimée.',
                icon: 'success',
                showConfirmButton: false,
                timer: 1500
                
            }
                

            );
        }
    });
}
</script>
