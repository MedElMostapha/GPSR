<?php

use Livewire\Volt\Component;
use App\Models\Mobilite; // Assurez-vous d'importer le modèle Mobilite
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Carbon\Carbon;
new class extends Component {
    use WithFileUploads; // Pour gérer les uploads de fichiers

    // Propriétés du formulaire
    public $lab;
    public $type = 'nationale'; // Valeur par défaut
    public $ville;
    public $pays;
    public $file;
    public $fileName;
    public $location='mobilites';
    public $date_debut;
    public $date_fin;


    // Règles de validation
    protected $rules = [
    'lab' => 'required|string|max:255',
    'type' => 'required|in:nationale,internationale',
    'ville' => 'nullable|string|max:255',
    'pays' => 'nullable|string|max:255',
    'date_debut' => 'required|date',
    'date_fin' => 'required|date|after_or_equal:date_debut',
];

    // Messages d'erreur personnalisés
    protected $messages = [
    'lab.required' => 'Le lab d\'accueil est obligatoire.',
    'type.required' => 'Le type de mobilité est obligatoire.',
    'date_debut.required' => 'La date de debut est obligatoire.',
    'date_fin.required' => 'La date de fin est obligatoire.',

];

    #[On('file-uploaded')]
    public function handleFileUpload($event)
    {
        $this->file = $event['filePath'];
        $this->fileName = $event['fileName'];
        // dd($this->file,$this->fileName);

    }

    // Fonction pour créer une mobilité
    public function createPublication()
    {

        // if($this->file == null){
        //     $this->dispatch("file-required");
        //     return;
        // }
        // Valider les données du formulaire
        $this->validate();
        // dd($this->file, $this->lab, $this->type, $this->ville, $this->pays);

        // Enregistrer le fichier et obtenir son chemin

        // Créer une nouvelle mobilité dans la base de données
        auth()->user()->mobilites()->create([
            'labo_accueil' => $this->lab,
            'type' => $this->type,
            'ville' => $this->type === 'nationale' ? $this->ville : null,
            'pays' => $this->type === 'internationale' ? $this->pays : null,
            'file' => $this->file,
            'file_name' => $this->fileName,
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin,
            'date_creation' => Carbon::now(),

        ]);

        // Réinitialiser le formulaire
        $this->reset(['lab', 'type', 'ville', 'pays', 'file', 'date_debut', 'date_fin']);

        // Afficher un message de succès
        session()->flash('message', 'Mobilité créée avec succès !');

        // // Rediriger vers la page de gestion des mobilités
        $this->redirect(route('mobilite'), navigate: true);
        $this->dispatch('mobiliteCreated',[
        'message' => 'Publication created successfully!'
        ]);
    }
};
?>

<div class="p-4"
    x-data="{ type: 'nationale' }">
    <!-- Initialiser avec 'nationale' par défaut -->
    <h1 class="text-2xl font-bold mb-4">Demander une mobilité</h1>

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

    <!-- Form -->
    <form wire:submit.prevent="createPublication"
        class="grid grid-cols-1 sm:grid-cols-2 gap-4"
        enctype="multipart/form-data">
        <!-- Title Input -->
        <div>
            <label for="lab"
                class="block text-sm font-medium">Lab d'accueil</label>
            <input type="text"
                id="lab"
                wire:model.defer="lab"
                class="input input-bordered bg-white w-full" />
            @error('lab')
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
        <livewire:inputfile label="Rapport de mobilité"
            :location="'mobilites'"
            :objet="'mobilites'" /> <!-- Progress Bar -->



        <!-- Buttons -->
        <div class="flex justify-end sm:col-span-2">
            <button type="reset"
                class="btn-sm rounded bg-red-600 border-none text-white hover:bg-red-500 mr-2">Reset</button>

            <x-primary-button type="button"
                class="btn-sm"
                wire:click="createPublication"
                wire:loading.attr="disabled">
                <span wire:loading.remove
                    wire:target="createPublication"
                    class="mr-2">{{ __('Ajouter') }}</span>
                <x-mary-loading class="loading"
                    wire:loading
                    wire:target="createPublication">
                </x-mary-loading>
            </x-primary-button>
        </div>
    </form>
</div>
