<?php
use App\Models\Publication;
use Livewire\Volt\Component;
use Carbon\Carbon;
use \App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

new class extends Component {
    public $publications;
    public $visibleCount = 6; // Number of publications to show initially
    public $viewingFileUrl = null;
    public $loading = false; // Track loading state
    public $selectedYears = []; // Store selected year(s) for filtering (as an array)
    public $statusFilter = ''; // Store selected status for filtering
    public $searchQuery = ''; // Store search query for filtering
    public $availableYears = []; // Store available years for selection
    public $columns=['title','journal','publication_date','isPublished' ,'type' ,'isAccepted'];
    public $booleanColumns=['isPublished'=>[
                            'true'=> ['text'=>'Publiée', 'class'=>'bg-green-100 text-green-800'],
                            'false'=>['text'=>'Non publiée', 'class'=>'bg-red-100 text-red-800'],],
                            'isAccepted'=>[
                                'true'=>['text'=>'Validé','class'=>'bg-green-100 text-green-800'],
                                'false'=>['text'=>'No validé','class'=>'bg-red-100 text-red-800']
                            ]
                        ];
    public $columnLabels = [
                        'title' => 'Titre', // Custom label for 'name'
                        'publication_date' => 'Date de publication', // Custom label for 'email'
                        'isPublished' => 'Etat', // Custom label for 'isValidated'
                        'isAccepted'=>'Validation',
                        'type'=>'Indexation',

                        ];


    protected $listeners = ['deletePublication'];
    public $selectFilters = [
        'publication_date',
        'isPublished',
        'isAccepted'
    ];
    public $enabledFilters = [
        'publication_date',
        'isPublished',
        'isAccepted'

    ];

    // Sync query parameters with component properties
    protected $queryString = [
        'selectedYears' => ['except' => []],
        'statusFilter' => ['except' => ''],
        'searchQuery' => ['except' => ''],
    ];

    public function mount()
    {
        // Retrieve filters from URL query parameters
        $this->selectedYears = (array) request()->query('year', []);
        $this->statusFilter = request()->query('status', '');
        $this->searchQuery = request()->query('search', '');

        // Fetch available years from publications
        $this->availableYears = $this->getAvailableYears();

        // Fetch publications with applied filters
        $this->publications();
    }



    public function getAvailableYears()
    {
        return Publication::query()
            ->whereNotNull('publication_date')
            ->selectRaw('YEAR(publication_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }

    public function publications()
    {
        $query = auth()->user()->hasRole('admin')
            ? Publication::whereNotIsArchivedAndIsPublished()
            : auth()->user()->publications()->whereNotIsArchived();

        // Apply year filter
        if (!empty($this->selectedYears)) {
            $query->whereIn(DB::raw('YEAR(publication_date)'), $this->selectedYears);
        }

        // Apply status filter
        if (!empty($this->statusFilter)) {
            if ($this->statusFilter === 'published') {
                $query->where('isPublished', true);
            } elseif ($this->statusFilter === 'archived') {
                $query->where('isArchived', true);
            } elseif ($this->statusFilter === 'unpublished') {
                $query->where('isPublished', false)->where('isArchived', false);
            }
        }

        // Apply search filter
        if (!empty($this->searchQuery)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('journal', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('abstract', 'like', '%' . $this->searchQuery . '%');
            });
        }

        $this->publications = $query->latest()->get();

    }

    public function updatedSelectedYears()
    {
        $this->updateUrl();
        $this->publications();
    }

    public function updatedStatusFilter()
    {
        $this->updateUrl();
        $this->publications();
    }

    public function updatedSearchQuery()
    {
        $this->updateUrl();
        $this->publications();
    }

    protected function updateUrl()
    {
        // Update the URL with the current filter values
        $queryParams = [
            'year' => $this->selectedYears,
            'status' => $this->statusFilter,
            'search' => $this->searchQuery,
        ];

        $this->redirect(route('publication', $queryParams), navigate: true);
    }

    // Clear a specific filter
public function clearFilter($filter, $value = null)
    {
    if ($filter === 'selectedYears' && $value !== null) {
    // Remove the specific year from the selectedYears array
    $this->selectedYears = array_diff($this->selectedYears, [$value]);

    } else {
    // Clear the entire filter if no specific value is provided
    $this->$filter = ($filter === 'selectedYears') ? [] : '';
    }

    // Update the URL and refresh the publications
    $this->updateUrl();
    $this->publications();
    }

    public function filterEnFr($filter){
        switch ($filter) {
            case 'published':
                return 'publiée';
                break;
            case 'unpublished':
                return 'non publiée';
                break;

            default:
                return $filter;
                break;
        }
    }

#[On('delete')]
public function deletePublication($id)
{
    // Use SweetAlert to confirm deletion
    $this->js("
    Swal.fire({
        title: 'Are you sure?',
        text: 'You won\\'t be able to revert this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            Livewire.dispatch('confirmDelete', { publicationId: $id });
        }
    });
");

}

#[On('confirmDelete')]
public function confirmDelete($publicationId)
{
    // Fetch the publication
    $publication = Publication::find($publicationId);

    // Ensure the publication exists
    if (!$publication) {
        $this->js("
            Swal.fire({
                title: 'Error!',
                text: 'Publication not found or already deleted.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        ");
        return;
    }

    // Delete the publication
    $publication->delete();

    // Refresh the publications list

    // Show success message
    $this->js("
        Swal.fire({
            title: 'Deleted!',
            text: 'Your publication has been deleted.',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
        });
    ");

    $this->publications();

    $this->dispatch('reload');


}



    public function loadMore(){
        $this->visibleCount += 10;
        $this->publications();
    }

    public function publier($publicationId){
        $publication = Publication::find($publicationId);
        $publication->isPublished = true;
        $publication->publication_date = Carbon::now();
        $publication->save();
        $this->publications();
        $this->availableYears = $this->getAvailableYears();
    }
    public function archiver($publicationId){
        $publication = Publication::find($publicationId);
        $publication->isArchived = true;
        $publication->save();
    }

    public function modifier($publicationId){
        $publication = Publication::find($publicationId);
        $this->redirect(route('modifier-publication', ['publication' => $publication]), navigate: true);

    }

   #[On('view')]
    public function view($id)
    {
        $publication = Publication::find($id);

        // Use Livewire's redirect method to ensure wire:navigate is used
        return $this->redirect(route('modifier-publication', ['publication' => $publication]), navigate: true);
    }



    public function viewFile($fileUrl)
    {
        // Redirect to the 'pdf' route with the fileUrl parameter
        return $this->redirect(route('pdf', ['fileUrl' => $fileUrl]), navigate: true);
    }

    public function viewProfile($userId)
    {
        // Use Livewire's redirect method to ensure wire:navigate is used
        $this->redirect(route('show', $userId), navigate: true);
    }
};
?>

<div x-data="{
    isTabable: localStorage.getItem('isTabable') === 'true',
    toggleDisplay() {
        this.isTabable = !this.isTabable;
        localStorage.setItem('isTabable', this.isTabable);
    }
}"
    class="relative">


    <!-- Toggle Button -->
    <button @click="toggleDisplay"
        class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-1 px-3 rounded-md transition-all duration-300 ease-in-out transform hover:scale-105 flex items-center space-x-2 text-sm z-50">
        <!-- Icon and Text -->
        <span x-show="!isTabable">
            <i class="fas fa-th-large"></i> <!-- Grid Icon -->
        </span>
        <span x-show="isTabable">
            <i class="fas fa-table"></i> <!-- Table Icon -->
        </span>
        <span x-text="isTabable ? 'Table' : 'Grille'"></span>
    </button>

    <div x-show="!isTabable"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="mt-4">


        <!-- Top Bar with Search -->
        <div class="flex justify-between items-center mb-6">
            <!-- Active Filters Section -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Year Filter Tag -->
                @if(!empty($selectedYears))
                @foreach ($selectedYears as $year)
                <div class="flex items-center bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">
                    <span>Année : {{ $year }}</span>
                    <button wire:click="clearFilter('selectedYears', '{{ $year }}')"
                        class="ml-2 text-blue-600 hover:text-blue-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @endforeach
                @endif

                <!-- Status Filter Tag -->
                @if($statusFilter)
                <div class="flex items-center bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full">
                    <span>Statut : {{ $this->filterEnFr($statusFilter) }}</span>
                    <button wire:click="clearFilter('statusFilter')"
                        class="ml-2 text-green-600 hover:text-green-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @endif
            </div>

            <!-- Search Input -->
            <div class="flex-1 sm:flex-none max-w-xs">
                <div class="relative">
                    <input id="searchQuery"
                        type="text"
                        wire:model.change="searchQuery"
                        class="block w-full pl-10 pr-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Rechercher par titre, journal ou description...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <!-- Year Filter Checkboxes -->
            <div x-data="{ open: false, selectedYears: @entangle('selectedYears') }"
                class="flex-1 sm:flex-none">
                <label class="block text-sm font-medium text-gray-700">Filtrer par année</label>
                <div class="relative mt-1">
                    <!-- Dropdown Toggle -->
                    <button @click="open = !open"
                        class="w-full flex justify-between items-center border border-gray-300 rounded-md shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span
                            x-text="selectedYears.length ? `${selectedYears.length} année(s) sélectionnée(s)` : 'Sélectionner des années'"></span>
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                        @click.away="open = false"
                        class="absolute z-10 mt-2 w-full bg-white border border-gray-300 rounded-md shadow-lg">
                        <div class="max-h-60 overflow-y-auto py-1 text-sm text-gray-700">
                            @if(!empty($availableYears))
                            @foreach ($availableYears as $year)
                            <label class="flex items-center px-4 py-2 hover:bg-gray-100">
                                <input type="checkbox"
                                    wire:model.change="selectedYears"
                                    value="{{ $year }}"
                                    @if(in_array($year,$selectedYears))
                                    checked
                                    @endif
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2">{{ $year }}</span>
                            </label>
                            @endforeach
                            @else
                            <div class="px-4 py-2">Aucune année disponible</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>


            <!-- Status Filter Dropdown -->
            <div class="flex-1 sm:flex-none">
                <label for="statusFilter"
                    class="block text-sm font-medium text-gray-700">Filtrer par statut</label>
                <div class="relative mt-1">
                    <select id="statusFilter"
                        wire:model.live="statusFilter"
                        class="block w-full pl-10 pr-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Tous les statuts</option>
                        <option value="published">Publié</option>
                        <option value="unpublished">Non publié</option>
                    </select>
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-filter text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Publications List -->
        @if($publications->isNotEmpty())
        <div>
            @if ($viewingFileUrl)
            <div class="bg-white p-6 w-full rounded-lg ">
                <button
                    class="mb-4 px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300 transition duration-150 ease-in-out"
                    wire:click="$set('viewingFileUrl', null)">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                @livewire('pdf.pdfviewer', ['fileUrl' => $viewingFileUrl])
            </div>
            @else
            <!-- Publications list -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($publications->take($visibleCount) as $publication)
                <div
                    class="card bg-white shadow-lg rounded-lg p-5 hover:shadow-xl z-0 transition duration-200 relative">
                    <!-- Status and Actions -->
                    <div class="flex justify-center mb-4">
                        <!-- Status Badge -->
                        <div class="flex items-center space-x-2 absolute top-2 left-2">
                            @if ($publication->isArchived)
                            <span
                                class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full transition duration-150">
                                <i class="fas fa-archive"></i> Archivée
                            </span>
                            @elseif($publication->isPublished)
                            <span
                                class="bg-green-200 text-green-600 text-xs px-2 py-1 rounded-full transition duration-150">
                                <i class="fas fa-globe"></i> En ligne
                            </span>
                            @else
                            <span
                                class="bg-blue-200 text-blue-600 text-xs px-2 py-1 rounded-full transition duration-150">
                                <i class="fas fa-ban"></i> Non publié
                            </span>
                            @endif
                        </div>

                        <!-- Actions (Edit, Archive, Delete) -->
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <!-- Edit Icon -->
                            <button wire:click="modifier({{ $publication->id }})"
                                class="text-blue-500 hover:text-blue-600 transition duration-150">
                                <i class="fas fa-edit"></i>
                            </button>

                            <!-- Archive Icon -->
                            <button wire:click="archiver({{ $publication->id }})"
                                class="text-yellow-500 hover:text-yellow-600 transition duration-150">
                                <i class="fas fa-archive"></i>
                            </button>

                            <!-- Delete Icon -->
                            <button wire:click="deletePublication({{ $publication->id }})"
                                class="text-red-500 hover:text-red-600 transition duration-150">
                                <i class="fas fa-trash"></i>
                            </button>

                            @if (!$publication->isPublished)
                            <button wire:click="publier({{ $publication->id }})"
                                class="btn btn-xs bg-blue-500 border-none text-white hover:bg-green-600">
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
                            <span class="font-bold text-lg bg-gray-200 px-4 py-1 rounded text-gray-800 mt-1">{{
                                $publication->title }}</span>
                        </div>

                        <!-- Journal -->
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold">Journal :</span> {{ $publication->journal }}
                        </div>

                        <!-- Abstract -->
                        <div x-data="{ showMore: false }"
                            class="text-sm text-gray-600">
                            <span class="font-semibold">Description :</span>
                            <div class="w-full bg-gray-100 pl-2 pr-2 pt-2 pb-2 rounded-lg">
                                <p x-show="!showMore"
                                    class="text-sm text-gray-600 whitespace-pre-line">
                                    {{ Str::words($publication->abstract, 15, '...') }}
                                </p>
                                <p x-show="showMore"
                                    class="text-sm text-gray-600 whitespace-pre-line">
                                    {{ $publication->abstract }}
                                </p>
                                <button @click="showMore = !showMore"
                                    class="text-blue-500 hover:text-blue-700 focus:outline-none mt-2">
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
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Show More Button -->
            @if ($publications->count() > $visibleCount)
            <div class="text-center mt-6">
                <x-secondary-button type="button"
                    wire:click="loadMore"
                    wire:loading.attr="disabled"
                    wire:target="loadMore">
                    <span wire:loading.remove
                        wire:target="loadMore">{{ __('Plus') }}</span>
                    <x-mary-loading class="loading-bars"
                        wire:loading
                        wire:target="loadMore"></x-mary-loading>
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

    <div x-show="isTabable"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="mt-4">

        <livewire:datatable :data="$publications"
            :columns="$columns"
            :pdfEnabled='true'
            :excelEnabled='true'
            :enabledFilters="$enabledFilters"
            :booleanColumns="$booleanColumns"
            :selectFilters="$selectFilters"
            :actions="[ 'view', 'delete' ]"
            :enableSearch="true"
            :columnLabels="$columnLabels" />
    </div>




</div>
{{-- <script>
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
</script> --}}