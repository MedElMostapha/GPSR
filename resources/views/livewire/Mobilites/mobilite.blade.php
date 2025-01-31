<?php

use Livewire\Volt\Component;
use App\Models\Mobilite;
use Livewire\Attributes\On;
use \App\Models\User;
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

    public $columns=[ 'labo_accueil','date_debut','date_fin','type' ,'isValidated','date_creation'];
    public $booleanColumns=[
                            'isValidated'=>[
                                'true'=>['text'=>'Validé','class'=>'bg-green-100 text-green-800'],
                                'false'=>['text'=>'No validé','class'=>'bg-red-100 text-red-800']
                            ]
                        ];
    public $columnLabels = [
                        'labo_accueil' => 'Lab d\'accueil', // Custom label for 'name'
                        'date_debut' => 'Date de debut', // Custom label for 'email'
                        'date_fin' => 'Date de fin', // Custom label for 'email'
                        'isValidated'=>'Validation',

                        ];

                     public $selectFilters = [
                        'date_creation',
                        ];
                        public $enabledFilters = [
                        'date_creation',

                        ];

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
        // Redirect to the 'pdf' route with the fileUrl parameter
        return $this->redirect(route('pdf', ['fileUrl' => $fileUrl]), navigate: true);
    }

    public function loadMore()
    {
        $this->loading = true; // Set loading to true when starting to load more
        $this->visibleCount += 6; // Increase the count by 6 when "Show More" is clicked

    }


    #[On('delete')]
    public function deleteMobilitie($id)
    {
        // Find the publication by ID
        $mobilitie = Mobilite::find($id);

        // Check if the publication exists
        if ($mobilitie) {
            // Delete the publication
            $mobilitie->delete();

            // Refresh the publications list
            $this->mobilites();
        }
    }






    public function validateMobilite($mobiliteId)
    {
        $mobilite = Mobilite::find($mobiliteId);

        $etat = $mobilite->isValidated;

        if ($mobilite) {
            $mobilite->update(['isValidated' => !$etat,'date_validation'=>now()]);
            $this->mobilites(); // Refresh the list
        }
    }

    public function viewProfile($rowId): void
    {
        $user = User::find($rowId);

        $this->redirect(route('show', ['user' => $user]), navigate: true);
    }

    #[On('view')]
    public function modifier($id){
        $mobilite = Mobilite::find($id);
        $this->redirect(route('mobilite-edit', ['mobilite' => $mobilite]), navigate: true);
    }
};
?>

<div x-data="{
    isTabableMobilite: localStorage.getItem('isTabableMobilite') === 'true',
    toggleDisplay() {
        this.isTabableMobilite = !this.isTabableMobilite;
        localStorage.setItem('isTabableMobilite', this.isTabableMobilite);
    }
}"
    class="relative">

    <!-- Toggle Button -->
    <button @click="toggleDisplay"
        class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-1 px-3 rounded-md transition-all duration-300 ease-in-out transform hover:scale-105 flex items-center space-x-2 text-sm z-50">
        <!-- Icon and Text -->
        <span x-show="!isTabableMobilite">
            <i class="fas fa-th-large"></i> <!-- Grid Icon -->
        </span>
        <span x-show="isTabableMobilite">
            <i class="fas fa-table"></i> <!-- Table Icon -->
        </span>
        <span x-text="isTabableMobilite ? 'Table' : 'Grille'"></span>
    </button>


    <div x-show="!isTabableMobilite"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="mt-4">


        @if($mobilites->isNotEmpty())
        <div>


            <!-- moibilites list -->
            <div class="grid grid-cols-2 gap-6">
                @foreach ($mobilites->take($visibleCount) as $mobilite)
                <div
                    class="card bg-white shadow-lg rounded-lg p-5 hover:shadow-xl z-0 transition duration-200 relative">
                    <!-- Status and Actions -->

                    <div class="flex justify-center mb-4">
                        <!-- Status Badge -->
                        <div class="flex items-center space-x-2 absolute top-2 left-2">
                            @if ($mobilite->isValidated == 1)
                            <span
                                class="bg-green-200 text-green-600 text-xs px-2 py-1 rounded-full  transition duration-150 ">
                                <i class="fas fa-check"></i> Demande acceptée
                            </span>
                            @else
                            <span
                                class="bg-yellow-200 text-yellow-600 text-xs px-2 py-1 rounded-full  transition duration-150 ">
                                <i class="fas fa-clock"></i> Demande en attente
                            </span>
                            @endif
                        </div>

                        <!-- Actions (Edit, Delete, Validate/Reject) -->
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <!-- Edit Icon -->
                            <button wire:click="modifier({{ $mobilite->id }})"
                                class="text-blue-500 hover:text-blue-600 transition duration-150">
                                <i class="fas fa-edit"></i>
                            </button>

                            <!-- Delete Icon -->
                            <button onclick="confirmDeletion({{ $mobilite->id }})"
                                class="text-red-500 hover:text-red-600 transition duration-150">
                                <i class="fas fa-trash"></i>
                            </button>

                            @if(auth()->user()->hasRole('admin'))
                            <!-- Validate/Reject Button -->
                            @if ($mobilite->isValidated == 1)
                            <button wire:click="validateMobilite({{ $mobilite->id }})"
                                class="btn btn-xs bg-red-500 border-none text-white hover:bg-red-600">
                                <i class="fas fa-times"></i>
                                Rejeter
                            </button>
                            @else
                            <button wire:click="validateMobilite({{ $mobilite->id }})"
                                class="btn btn-xs bg-green-500 border-none text-white hover:bg-green-600">
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
                            <span class="text-sm text-gray-500 font-medium uppercase tracking-wider">Labo
                                d'accueil</span>
                            :
                            <!-- Labo d'accueil Name -->
                            <span class="font-bold text-lg bg-gray-200 px-4 py-1 rounded text-gray-800 mt-1">{{
                                $mobilite->labo_accueil }}</span>
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
                                    {{
                                    \Carbon\Carbon::parse($mobilite->date_debut)->diffInDays(\Carbon\Carbon::parse($mobilite->date_fin))
                                    }} jours
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
                            <span wire:click.prevent="viewProfile({{ $mobilite->user->id }})"
                                class="text-blue-500 hover:text-blue-700 cursor-pointer">{{ $mobilite->user->name
                                }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>



            <!-- Show More Button -->
            @if ($mobilites->count() > $visibleCount)
            <div class="text-center mt-6">
                <x-secondary-button type="button"
                    wire:click="loadMore"
                    wire:loading.attr="disabled"
                    wire:target="loadMore">
                    <span wire:loading.remove
                        wire:target="loadMore">{{ __('Plus') }}</span>
                    <x-mary-loading class="loading-bars"
                        wire:loading
                        wire:target="loadMore">
                    </x-mary-loading>
                </x-secondary-button>
            </div>
            @endif

            <!-- Edit Modal -->
            <!-- Edit Modal -->


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

    <div x-show="isTabableMobilite"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="mt-4">

        <livewire:datatable :data="$mobilites"
            :columns="$columns"
            :selectFilters="$selectFilters"
            :enabledFilters="$enabledFilters"
            :booleanColumns="$booleanColumns"
            :actions="[ 'view', 'delete' ]"
            :enableSearch="true"
            :columnLabels="$columnLabels" />
    </div>




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
