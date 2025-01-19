<?php

use Livewire\Volt\Component;
use App\Models\Mobilite;
use Livewire\Attributes\On;
new class extends Component {
    public $mobilites;
    public $visibleCount = 6; // Number of publications to show initially
    public $viewingFileUrl = null;
    public $loading = false; // Track loading state
    public $editingMobilite = null; // Track the mobilite being edited
    public $editModalOpen = false; // Track if the edit modal is open

    public $labo_accueil;
    public $type = 'nationale'; // Valeur par défaut
    public $ville;
    public $pays;
    public $file;
    public $file_name;
    public $date_debut;
    public $date_fin;
    
    protected $listeners = ['deleteMobilitie'];

    public function mount()
    {
        // Fetch publications for the authenticated user
        $this->mobilites();
    }

    public function mobilites()
    {

        // Fetch publications for the authenticated user
        $this->mobilites = auth()->user()->mobilites()->latest()->get();
        if(auth()->user()->hasRole('admin')){
        $this->mobilites = Mobilite::latest()->get();
        }
    }

    public function viewFile($fileUrl)
    {
        $this->viewingFileUrl = $fileUrl;
    }

    public function loadMore()
    {
        $this->loading = true; // Set loading to true when starting to load more
        $this->visibleCount += 6; // Increase the count by 6 when "Show More" is clicked
        
        // Simulate a delay (you can remove this part in production)
        // sleep(1);
        
    }

    #[On('file-uploaded')]
    public function handleFileUpload($event)
    {
        $this->file = $event['filePath'];
        $this->file_name = $event['fileName'];
        // dd($this->file,$this->fileName);
        
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

    public function editMobilite(Mobilite $mobilite)
    {
        $this->labo_accueil = $mobilite->labo_accueil;
        $this->type = $mobilite->type;
        $this->ville = $mobilite->ville;
        $this->pays = $mobilite->pays;
        $this->file = $mobilite->file;
        $this->date_debut = $mobilite->date_debut;
        $this->date_fin = $mobilite->date_fin;
        $this->editingMobilite = $mobilite;
        $this->file_name = $mobilite->file_name;
        $this->editModalOpen = true; // Open the modal

    }

    public function updateMobilite()
    {

        // dd($this->file);
        // Validate and update the mobilite
        $validated = $this->validate([
            'labo_accueil' => 'nullable:string',
            'type' => 'nullable|in:nationale,internationale',
            'ville' => 'nullable:string',
            'pays' => 'nullable:string',
            'file' => 'nullable|string',
            'file_name' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        // dd($validated);

        $this->editingMobilite->update($validated);

        // Close the modal and refresh the list
        $this->editModalOpen = false;
        $this->mobilites();
    }

    public function closeEditModal()
    {
        $this->editModalOpen = false;
        $this->editingMobilite = null; // Clear the editing mobilite
    }
    public function removeFile()
    {
        // Clear the file and file name
        $this->file = null;
        $this->file_name = null;

        // If editing an existing mobilite, update the database
        if ($this->editingMobilite) {
            $this->editingMobilite->update([
                'file' => null,
                'file_name' => null,
            ]);
        }
    }

    public function validateMobilite($mobiliteId)
        {
            $mobilite = Mobilite::find($mobiliteId);

            $etat = $mobilite->isValidated;

            if ($mobilite) {
                $mobilite->update(['isValidated' => !$etat]);
                $this->mobilites(); // Refresh the list
            }
        }
};
?>

<div >
    @if($mobilites->isNotEmpty())
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

        <!-- moibilites list -->
        <div class="grid grid-cols-2 gap-6">
            @foreach ($mobilites->take($visibleCount) as $mobilite)
                <div class="card bg-white shadow-lg rounded-lg p-5 hover:shadow-xl z-0 transition duration-200 relative">
                    <!-- Status and Actions -->
                    
                    <div class="flex justify-center mb-4">
                        <!-- Status Badge -->
                        <div class="flex items-center space-x-2 absolute top-2 left-2">
                            @if ($mobilite->isValidated == 1)
                                <span class="bg-green-200 text-green-600 text-xs px-2 py-1 rounded-full  transition duration-150 ">
                                    <i class="fas fa-check"></i> Demande acceptée
                                </span>
                            @else
                                <span class="bg-yellow-200 text-yellow-600 text-xs px-2 py-1 rounded-full  transition duration-150 ">
                                    <i class="fas fa-clock"></i> Demande en attente
                                </span>
                            @endif
                        </div>
            
                        <!-- Actions (Edit, Delete, Validate/Reject) -->
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <!-- Edit Icon -->
                            <button wire:click="editMobilite({{ $mobilite->id }})" class="text-blue-500 hover:text-blue-600 transition duration-150">
                                <i class="fas fa-edit"></i>
                            </button>
            
                            <!-- Delete Icon -->
                            <button onclick="confirmDeletion({{ $mobilite->id }})" class="text-red-500 hover:text-red-600 transition duration-150">
                                <i class="fas fa-trash"></i>
                            </button>
            
                            @if(auth()->user()->hasRole('admin'))
                            <!-- Validate/Reject Button -->
                            @if ($mobilite->isValidated == 1)
                                <button wire:click="validateMobilite({{ $mobilite->id }})" class="btn btn-xs bg-red-500 border-none text-white hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                    Rejeter
                                </button>
                            @else
                                <button wire:click="validateMobilite({{ $mobilite->id }})" class="btn btn-xs bg-green-500 border-none text-white hover:bg-green-600">
                                    <i class="fas fa-check"></i>
                                    Valider
                                </button>
                            @endif
                            @endif
                        </div>
                    </div>
                    
            
                    <!-- Content -->
                    <div class="space-y-4">
                        <!-- Labo d'accueil -->
                        <div class="mb-2">
                            <!-- Label for Labo d'accueil -->
                            <span class="text-sm text-gray-500 font-medium uppercase tracking-wider">Labo d'accueil</span>
                            :
                            <!-- Labo d'accueil Name -->
                            <span class="font-bold text-lg bg-gray-200 px-4 py-1 rounded text-gray-800 mt-1">{{ $mobilite->labo_accueil }}</span>
                        </div>
                    
                        <div class="flex gap-4">
                            <!-- Section 1 -->
                            <div class="flex-1 space-y-2">
                                <!-- Type -->
                                <div class="text-sm text-gray-600">
                                    <span class="font-semibold">Type :</span> {{ ucfirst($mobilite->type) }}
                                </div>
                    
                                <!-- Date de début -->
                                <div class="text-sm text-gray-600">
                                    <span class="font-semibold">Date de début :</span>
                                    <span class="text-gray-800 text-[12px]">

                                        {{ \Carbon\Carbon::parse($mobilite->date_debut)->format('d/m/Y') }}
                                    </span>
                                </div>
                    
                                
                            </div>
                    
                            <!-- Section 2 -->
                            <div class="flex-1 space-y-2">
                                <!-- Ville ou Pays selon le type -->
                                @if ($mobilite->type === 'nationale')
                                    <div class="text-sm text-gray-600">
                                        <span class="font-semibold">Ville :</span> {{ $mobilite->ville }}
                                    </div>
                                @elseif ($mobilite->type === 'internationale')
                                    <div class="text-sm text-gray-600">
                                        <span class="font-semibold">Pays :</span> {{ $mobilite->pays }}
                                    </div>
                                @endif
                    
                                <!-- Date de fin -->
                                <div class="text-sm text-gray-600">
                                    <span class="font-semibold">Date de fin :</span>
                                    <span class="text-gray-800 text-[12px]">

                                        {{ \Carbon\Carbon::parse($mobilite->date_fin)->format('d/m/Y') }}
                                    </span>
                                </div>
                    
                                <!-- Durée -->
                                <div class="text-sm text-gray-600">
                                    <span class="font-semibold">Durée :</span>
                                    {{ \Carbon\Carbon::parse($mobilite->date_debut)->diffInDays(\Carbon\Carbon::parse($mobilite->date_fin)) }} jours
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <!-- File Section -->
                    <div class="mt-4 space-y-3">
                        @if ($mobilite->file)
                            <div class="relative">
                                <!-- Badge for "Consulter" -->
                                <div class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full hover:bg-blue-600 transition duration-150 cursor-pointer"
                                    wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $mobilite->file)) }}')">
                                    <i class="fas fa-eye"></i>
                                </div>
            
                                <!-- File Name -->
                                <div class="bg-gray-100 p-3 rounded-lg border border-gray-200">
                                    <p class="text-xs text-gray-600 truncate">
                                        Rapport : {{ $mobilite->file_name }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-red-600">
                                <span class="flex items-center space-x-1 bg-red-100 px-3 py-1 rounded-full w-fit">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>Rapport manquant</span>
                                </span>
                            </p>
                        @endif
                    </div>
                    <!-- Créé -->
                    <div class="text-xs text-gray-500 mt-2  ">
                        <div class="float-right text-[10px]">

                            <span class="font-semibold">Créé :</span>
                            
                            {{ $mobilite->created_at->locale('fr')->diffForHumans() }}
                        </div>

                        @if(auth()->user()->hasRole('admin'))
                        <div class="float-start text-[10px]">
                        <span class="font-semibold">Demandé par :</span>
                            <span>{{ $mobilite->user->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    

        <!-- Show More Button -->
        @if ($mobilites->count() > $visibleCount)
            <div class="text-center mt-6">
                <x-secondary-button type="button" wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore">
                    <span wire:loading.remove wire:target="loadMore">{{ __('Plus') }}</span>
                    <x-mary-loading class="loading-bars" wire:loading wire:target="loadMore">
                    </x-mary-loading>
                </x-secondary-button>
            </div>
        @endif
    @endif

    <!-- Edit Modal -->
    <!-- Edit Modal -->
    @if ($editModalOpen)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Modifier la Mobilité</h2>
            <form wire:submit.prevent="updateMobilite">
                <!-- Labo d'accueil -->
                <div class="mb-4">
                    <label for="labo_accueil" class="block text-sm font-medium text-gray-700">Labo d'accueil</label>
                    <input type="text" wire:model="labo_accueil" id="labo_accueil" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div x-data="{ type: '{{ $type }}' }">
                    <!-- Type -->
                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                        <select x-model="type" wire:model="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="nationale">Nationale</option>
                            <option value="internationale">Internationale</option>
                        </select>
                    </div>
                
                    <!-- Ville -->
                    <div class="mb-4" x-show="type === 'nationale'">
                        <label for="ville" class="block text-sm font-medium text-gray-700">Ville</label>
                        <input type="text" wire:model="ville" id="ville" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                
                    <!-- Pays -->
                    <div class="mb-4" x-show="type === 'internationale'">
                        <label for="pays" class="block text-sm font-medium text-gray-700">Pays</label>
                        <input type="text" wire:model="pays" id="pays" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                
                    <!-- Date de debut -->
                    <div class="mb-4">
                        <label for="date_debut" class="block text-sm font-medium text-gray-700">Date de debut</label>
                        <input type="date" wire:model="date_debut" id="date_debut" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    @error('date_debut') 
                        <span class="text-red-500 text-sm">{{ $message }}</span> 
                    @enderror
                
                    <!-- Date de fin -->
                    <div class="mb-4">
                        <label for="date_fin" class="block text-sm font-medium text-gray-700">Date de fin</label>
                        <input type="date" wire:model="date_fin" id="date_fin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    @error('date_fin') 
                        <span class="text-red-500 text-sm">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- File Section -->
                @if ($file_name)
                    <div class="mb-4">
                        <label for="pays" class="block text-sm font-medium text-gray-700">Rapport de mobilité</label>
                        <div class="flex items-center space-x-2">

                            <div class="flex items-center justify-between space-x-2 bg-gray-200 w-fit rounded-md px-2 py-1">
                                <span class="text-sm text-gray-600">{{ $file_name }}</span>
                                
                            </div>
                            <button type="button" wire:click="removeFile" class="text-red-500 hover:text-red-600">
                                <i class="fas fa-trash text-gray-500"></i>
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Upload New File -->
                    <div class="mb-4">
                        <livewire:inputfile label="Rapport de mobilité" :location="'mobilites'" :objet="'mobilites'" />
                    </div>
                @endif

                <!-- Buttons -->
                <div class="flex justify-end space-x-4">
                    <button type="button" wire:click="closeEditModal" class="px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300 transition duration-150 ease-in-out">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 rounded-md text-white hover:bg-blue-600 transition duration-150 ease-in-out">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    </div>
    @else
    <div class="text-center mt-6 h-full w-full border p-10 bg-white rounded-lg shadow-lg max-w-2xl">
        <div class="text-gray-500 text-lg">
            <i class="fas fa-box-open text-4xl mb-4"></i> 
            <span class="block text-2xl">
                Aucune mobilité disponible
            </span>
        </div>
    </div>
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
                });
            }
        });
    }
</script>