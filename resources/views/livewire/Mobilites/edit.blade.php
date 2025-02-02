<?php

use Livewire\Volt\Component;
use App\Models\Mobilite;
use Livewire\Attributes\On;
new class extends Component {

    public $mobilite;
    public $viewingFileUrl = null;


    public $labo_accueil;
    public $type = 'nationale'; // Valeur par défaut
    public $ville;
    public $pays;
    public $file;
    public $file_name;
    public $date_debut;
    public $date_fin;

    public function mount(Mobilite $mobilite)
    {
        $this->mobilite=$mobilite;
        $this->labo_accueil = $mobilite->labo_accueil;
        $this->type = $mobilite->type;
        $this->ville = $mobilite->ville;
        $this->pays = $mobilite->pays;
        $this->file = $mobilite->file;
        $this->date_debut = $mobilite->date_debut;
        $this->date_fin = $mobilite->date_fin;
        $this->file_name = $mobilite->file_name;
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
        $this->file_name = $mobilite->file_name;

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


        $this->mobilite->update($validated);
        session()->flash('success', 'Les informations de la mobilité ont été mises à jour avec succès.');

    }
    #[On('file-uploaded')]
    public function handleFileUpload($event)
    {
        $this->file = $event['filePath'];
        $this->file_name = $event['fileName'];

    }

    public function removeFile()
    {
        // Clear the file and file name
        $this->file = null;
        $this->file_name = null;

        // If editing an existing mobilite, update the database
        if ($this->mobilite) {
            $this->mobilite->update([
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
            $mobilite->update(['isValidated' => !$etat,'date_validation'=>now()]);
            $this->mobilite=$mobilite; // Refresh the list
        }
    }
}; ?>

<div class="p-4 relative"
    x-data="{ type: 'nationale' }">
    <a href="{{ route('mobilite') }}"
        wire:navigate
        class="text-blue-500 hover:text-blue-700">
        <i class="fas fa-arrow-left mr-2"></i>
        {{ __('Retour') }}
    </a>
    <!-- Initialiser avec 'nationale' par défaut -->
    <h1 class="text-2xl font-bold mb-4">{{__('Modifier et visualiser une mobilité')}}</h1>

    <!-- Validation Button and Status -->
    <div class="absolute top-4 right-4 flex items-center space-x-4">
        <!-- Validation Status -->
        @if ($mobilite->isValidated == 1)
        <span class="bg-green-200 text-green-600 text-xs px-2 py-1 rounded-full transition duration-150">
            <i class="fas fa-check"></i> Demande acceptée
        </span>
        @else
        <span class="bg-yellow-200 text-yellow-600 text-xs px-2 py-1 rounded-full transition duration-150">
            <i class="fas fa-clock"></i> Demande en attente
        </span>
        @endif

        <!-- Validate/Reject Button (Admin Only) -->
        @if(auth()->user()->hasRole('admin'))
        @if ($mobilite->isValidated == 1)
        <button wire:click="validateMobilite({{ $mobilite->id }})"
            class="btn btn-xs bg-red-500 border-none text-white hover:bg-red-600">
            <i class="fas fa-times"></i>
            {{__('Rejeter')}}
        </button>
        @else
        <button wire:click="validateMobilite({{ $mobilite->id }})"
            class="btn btn-xs bg-green-500 border-none text-white hover:bg-green-600">
            <i class="fas fa-check"></i>
            {{__('Valider')}}
        </button>
        @endif
        @endif
    </div>

    <!-- Success Message -->
    @if (session()->has('success'))
    <div x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 10000)"
        class="absolute top-16 right-4 bg-green-500 text-white p-4 rounded-md mb-4 flex items-center justify-between">
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
            <span>{{ session('success') }}</span>
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
    <form wire:submit.prevent="updateMobilite"
        class="grid grid-cols-1 sm:grid-cols-2 gap-4"
        enctype="multipart/form-data">
        <!-- Title Input -->
        <div>
            <label for="labo_accueil"
                class="block text-sm font-medium">Lab d'accueil</label>
            <input type="text"
                id="labo_accueil"
                wire:model.defer="labo_accueil"
                class="input input-bordered bg-white w-full" />
            @error('labo_accueil')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Type Input -->
        <div>
            <label for="type"
                class="block text-sm font-medium">Type</label>
            <select name="type"
                id="type"
                wire:model="type"
                x-model="type"
                class="input input-bordered bg-white w-full">
                <option value="nationale">Nationale</option>
                <option value="internationale">Internationale</option>
            </select>
            @error('type')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Ville Input (visible seulement si "Nationale" est sélectionné) -->
        <div x-show="type === 'nationale'">
            <label for="ville"
                class="block text-sm font-medium">Ville</label>
            <input type="text"
                id="ville"
                wire:model.defer="ville"
                class="input input-bordered bg-white w-full" />
            @error('ville')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Pays Input (visible seulement si "Internationale" est sélectionné) -->
        <div x-show="type === 'internationale'">
            <label for="pays"
                class="block text-sm font-medium">Pays</label>
            <input type="text"
                id="pays"
                wire:model.defer="pays"
                class="input input-bordered bg-white w-full" />
            @error('pays')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Date de debut Input -->
        <div>
            <label for="date_debut"
                class="block text-sm font-medium">Date de debut</label>
            <input type="date"
                id="date_debut"
                wire:model.defer="date_debut"
                class="input input-bordered bg-white w-full" />
            @error('date_debut')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Date de fin Input -->
        <div>
            <label for="date_fin"
                class="block text-sm font-medium">Date de fin</label>
            <input type="date"
                id="date_fin"
                wire:model.defer="date_fin"
                class="input input-bordered bg-white w-full" />
            @error('date_fin')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- File Upload Input -->
        @if ($file_name)
        <div>
            <label class="block text-sm font-medium text-gray-700">Rapport de mobilité</label>
            <div class="flex items-center space-x-2 bg-gray-200 p-2 rounded-md">
                <span class="text-gray-600 text-sm">{{ $file_name }}</span>
                <button type="button"
                    wire:click="removeFile"
                    class="text-red-500 hover:text-red-600">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        @else
        <!-- Upload New File -->
        <div>
            <livewire:inputfile label="Rapport de mobilité"
                :location="'mobilites'"
                :objet="'mobilites'" />
        </div>
        @endif

        <!-- Buttons -->
        <div class="flex justify-end sm:col-span-2">
            <button type="reset"
                class="btn-sm rounded bg-red-600 border-none text-white hover:bg-red-500 mr-2">{{__('Reset')}}</button>

            <x-primary-button type="submit"
                class="btn-sm"
                wire:loading.attr="disabled">
                <span wire:loading.remove
                    wire:target="updateMobilite"
                    class="mr-2">{{ __('Mettre à jour') }}</span>
                <x-mary-loading class="loading"
                    wire:loading
                    wire:target="updateMobilite">
                </x-mary-loading>
            </x-primary-button>
        </div>
    </form>
</div>