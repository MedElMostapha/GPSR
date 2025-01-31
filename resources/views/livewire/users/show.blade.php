<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Publication;
use App\Mail\CompteActive;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

new class extends Component {
    public $user;
    public $profile;
    public $isValidated;
    public $viewingFileUrl = null;

    public function mount($user){
        $this->user = $user;
        $this->profile = User::with('roles')->find($user);
        $this->isValidated = $this->profile->isValidated;
    }

    public function toggleValidation(User $user)
{
    // Toggle the validation status
    $isValidated = $user->isValidated;
    $user->update(['isValidated' => !$isValidated]);

    // Send email only if the user is activated (changing from false to true)
    if (!$isValidated) {
        Mail::to($user->email)->queue(new CompteActive($user));
    }
}


public function viewFile($fileUrl)
{
// Redirect to the 'pdf' route with the fileUrl parameter
return $this->redirect(route('pdf', ['fileUrl' => $fileUrl]), navigate: true);
}

    // Obtenir les statistiques des publications par mois

}; ?>

<div class="min-h-screen bg-gray-100 p-4">
    <!-- Carte de profil -->
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-lg p-6 relative">

        <!-- Bouton de retour -->
        <div class="mb-4">
            <button onclick="window.history.back()"
                class="text-blue-500 hover:text-blue-700 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour
            </button>
        </div>

        <!-- Bouton d'activation/désactivation -->
        <div class="absolute top-6 right-6 flex items-center">
            <div class="mr-2">
                <!-- Loading indicator -->
                <x-mary-loading wire:target="toggleValidation({{ $profile->id }})"
                    wire:loading></x-mary-loading>
            </div>
            <label class="flex items-center cursor-pointer">
                <div class="relative">
                    <!-- Hide the input and toggle button when loading -->
                    <div wire:loading.remove
                        wire:target="toggleValidation({{ $profile->id }})">
                        <!-- Hidden checkbox -->
                        <input type="checkbox"
                            class="sr-only"
                            wire:model="isValidated"
                            wire:click="toggleValidation({{ $profile->id }})">
                        <!-- Toggle button background -->
                        <div class="w-10 h-6 rounded-full shadow-inner transition-colors duration-200
                                {{ $isValidated ? 'bg-green-500' : 'bg-red-500' }}
                                hover:{{ $isValidated ? 'bg-green-600' : 'bg-red-600' }}">
                        </div>
                        <!-- Toggle button circle -->
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow-md transform transition-transform duration-200
                                {{ $isValidated ? 'translate-x-4' : 'translate-x-0' }}">
                        </div>
                    </div>
                </div>
                <!-- Label text -->
                <span class="ml-2 text-sm font-medium text-gray-700">
                    {{ $isValidated ? 'Activé' : 'Désactivé' }}
                </span>
            </label>
        </div>

        <!-- En-tête du profil -->
        <div class="flex items-center space-x-6">
            <!-- Photo de profil -->
            <div class="flex-shrink-0">
                @if($profile->image)
                <img class="h-24 w-24 rounded-full object-cover"
                    src="{{ asset('storage/' . str_replace('public/', '', $profile->image)) }}"
                    alt="{{ $profile->name }}">
                @else
                <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-3xl text-gray-600">
                    {{ substr($profile->name, 0, 1) }}
                </div>
                @endif
            </div>
            <!-- Informations du profil -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $profile->name }}</h1>
                <p class="text-gray-600">{{ $profile->email }}</p>
                <p class="text-gray-600">{{ $profile->bio ?? 'Aucune biographie disponible.' }}</p>
                <div class="mt-2">
                    <span class="inline-block bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded-full">{{
                        $profile->roles->map(fn ($role) => $role->name)->implode(', ') }}</span>
                </div>
            </div>
        </div>

        <!-- Séparateur -->
        <hr class="my-6 border-t border-gray-200">

        <!-- Section "À propos de moi" -->
        <div class="mt-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-user-circle mr-2 text-blue-500"></i>
                À propos de moi
            </h2>
            <p class="mt-2 text-gray-600">{{ $profile->about ?? 'Aucune information supplémentaire fournie.' }}</p>
        </div>

        <!-- Séparateur -->
        <hr class="my-6 border-t border-gray-200">

        <!-- Section des liens sociaux -->
        <div class="mt-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-share-alt mr-2 text-blue-500"></i>
                Liens sociaux
            </h2>
            <div class="mt-2 space-x-4">

                @if($profile->website)
                <a href="{{ $profile->website }}"
                    class="text-blue-500 hover:text-blue-700">
                    <i class="fas fa-globe"></i> Website
                </a>
                @endif
                @if($profile->facebook)
                <a href="{{ $profile->facebook }}"
                    class="text-blue-500 hover:text-blue-700">
                    <i class="fab fa-facebook"></i> Facebook
                </a>
                @endif
                @if($profile->twitter)
                <a href="{{ $profile->twitter }}"
                    class="text-blue-500 hover:text-blue-700">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
                @endif
                @if($profile->linkedin)
                <a href="{{ $profile->linkedin }}"
                    class="text-blue-500 hover:text-blue-700">
                    <i class="fab fa-linkedin"></i> LinkedIn
                </a>
                @endif
                @if($profile->github)
                <a href="{{ $profile->github }}"
                    class="text-blue-500 hover:text-blue-700">
                    <i class="fab fa-github"></i> GitHub
                </a>
                @endif
            </div>
        </div>

        <!-- Séparateur -->
        <hr class="my-6 border-t border-gray-200">

        <!-- Section des informations de contact -->
        <div class="mt-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-envelope mr-2 text-blue-500"></i>
                Informations de contact
            </h2>
            <div class="mt-2 text-gray-600 space-y-2">
                <p><span class="font-medium">Email:</span> {{ $profile->email }}</p>
                <p><span class="font-medium">Téléphone:</span> {{ $profile->phone ?? 'Non fourni' }}</p>
            </div>
        </div>

        <!-- Séparateur -->
        <hr class="my-6 border-t border-gray-200">

        <!-- Section des informations supplémentaires -->
        <div class="mt-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                Informations supplémentaires
            </h2>
            <div class="mt-2 text-gray-600 space-y-2">
                @if ($profile->attestation)
                <div class="relative">
                    <span><span class="font-medium">Attestation:</span></span>
                    <!-- Badge pour "Consulter" -->
                    <div class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full hover:bg-blue-600 transition duration-150 cursor-pointer"
                        wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $profile->attestation)) }}')">
                        <i class="fas fa-eye"></i>
                    </div>

                    <!-- Nom du fichier -->
                    <div class="bg-gray-100 p-2 rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-600 truncate">
                            Fichier : {{ $profile->attestation_fileName }}
                        </p>
                    </div>
                </div>
                @else
                <p><span class="font-medium">Attestation:</span> Non disponible</p>
                @endif

                <p><span class="font-medium">Spécialité:</span> {{ $profile->specialite }}</p>

                @if ($profile->identity_copy)
                <div class="relative">
                    <span><span class="font-medium">Copie d'identité:</span></span>
                    <!-- Badge pour "Consulter" -->
                    <div class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full hover:bg-blue-600 transition duration-150 cursor-pointer"
                        wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $profile->identity_copy)) }}')">
                        <i class="fas fa-eye"></i>
                    </div>

                    <!-- Nom du fichier -->
                    <div class="bg-gray-100 p-2 rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-600 truncate">
                            Fichier : {{ $profile->identitity_fileName }}
                        </p>
                    </div>
                </div>
                @else
                <p><span class="font-medium">Copie d'identité:</span> Non disponible</p>
                @endif
            </div>

            <!-- Nombre de publications -->
            <div class="mt-2 text-gray-600 space-y-2">
                <p><span class="font-medium">Nombre de publications:</span> {{ $profile->numberOfPublicationsPublished()
                    }}</p>
            </div>
        </div>

        <!-- Séparateur -->
        <hr class="my-6 border-t border-gray-200">


    </div>
</div>
