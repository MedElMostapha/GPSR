<?php

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
        $this->publications = \App\Models\Publication::where('user_id', auth()->id())->latest()->get();
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
        logger("Tentative de suppression de la publication ID: {$publicationId}");

        $publication = \App\Models\Publication::find($publicationId);

        if ($publication && $publication->user_id == auth()->id()) {
            $publication->delete();
            $this->publications = $this->publications->filter(fn ($pub) => $pub->id !== $publicationId);
        }
    }
};
?>

<div class="p-6 bg-gray-100 min-h-screen">
    @if ($viewingFileUrl)
        <div class="bg-white p-6 rounded-lg shadow-md">
            @livewire('pdf.pdfviewer', ['fileUrl' => $viewingFileUrl])
            <button class="mt-4 px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300 transition duration-150 ease-in-out" 
                wire:click="$set('viewingFileUrl', null)">
                Back to Publications
            </button>
        </div>
    @else
        <h1 class="text-3xl font-extrabold text-gray-800 mb-6">Publications</h1>

        <!-- Publications list -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($publications->take($visibleCount) as $publication)
                <div class="card bg-white shadow-lg rounded-lg p-5 hover:shadow-xl z-0 transition duration-200 relative">
                    <!-- Top right icons -->
                    <div class="absolute top-2 right-2 flex space-x-2">
                        <!-- Edit Icon -->
                        <button wire:click="editPublication({{ $publication->id }})" class="text-blue-500 hover:text-blue-600 transition duration-150">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <!-- Archive Icon -->
                        <button wire:click="archivePublication({{ $publication->id }})" class="text-yellow-500 hover:text-yellow-600 transition duration-150">
                            <i class="fas fa-archive"></i>
                        </button>
                        
                        <!-- Delete Icon -->
                        <!-- Delete Icon -->
                        <button onclick="confirmDeletion({{ $publication->id }})" class="text-red-500 hover:text-red-600 transition duration-150">
                            <i class="fas fa-trash"></i>
                        </button>

                    </div>
                    
                    <!-- Publication Content -->
                    <h2 class="font-bold text-xl text-gray-800 mb-2">{{ $publication->title }}</h2>
                    <p class="text-sm text-gray-600">{{ $publication->journal }}</p>
                    <p class="text-sm text-gray-600">{{ $publication->publication_date }}</p>
                    <p class="text-sm mt-3 text-gray-700 leading-snug">{{ Str::limit($publication->abstract, 100) }}</p>

                    <div class="mt-4 space-y-3">
                        @if ($publication->file_path)
                            <div class="flex items-center text-blue-500 text-sm hover:text-blue-600 transition duration-150 cursor-pointer" 
                                wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $publication->file_path)) }}')">
                                <i class="fas fa-file-pdf mr-2"></i> Voir l'article
                            </div>
                        @endif
                        @if ($publication->rib)
                            <div class="flex items-center text-green-500 text-sm hover:text-green-600 transition duration-150 cursor-pointer" 
                                wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $publication->rib)) }}')">
                                <i class="fas fa-file-invoice mr-2"></i> Voir RIB
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Show More Button -->
        @if ($publications->count() > $visibleCount)
            <div class="text-center mt-6">
                <!-- Hide the Show More button if loading is true -->
                    <button wire:click="loadMore" class="btn-primary">
                        <div class="flex items-center space-x-2">
                            <div>
                                <span class="text-sm">Voir plus</span>
                            </div>
                            <div wire:loading class="">
                                <div class="flex justify-center">
                                    <div class="spinner-border animate-spin border-t-2 border-b-2 border-gray-500 rounded-full w-4 h-4"></div>
                                </div>
                            </div>
                        </div>
                    </button>
            </div>
        @endif
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
                console.log('====================================');
                console.log("confirmed");
                console.log('====================================');
                Livewire.emit('deletePublication', publicationId);
                Swal.fire(
                    'Supprimé !',
                    'La publication a été supprimée.',
                    'success'
                );
            }
        });
    }
</script>
