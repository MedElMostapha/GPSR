<div>
    <livewire:mobilite-form />
</div>

<div>
    <!-- Navigation des étapes -->
    <div class="steps w-full">
        <button class="step {{ $currentStep === 1 ? 'step-primary' : '' }}" wire:click=""></button>
        <button class="step {{ $currentStep === 2 ? 'step-primary' : '' }}" wire:click=""></button>
        <button class="step {{ $currentStep === 3 ? 'step-primary' : '' }}" wire:click=""></button>
    </div>

    <!-- Étape 1: Détails personnels -->
    @if ($currentStep === 1)
        <div>
            <div>
                <x-input-label for="labo_accueil" :value="__('Labo d\'accueil')" />
                <x-text-input wire:model="labo_accueil" id="labo_accueil" class="block mt-1 w-full" type="text" required />
                <x-input-error :messages="$errors->get('labo_accueil')" class="mt-2" />
            </div>
        </div>
    @endif

    <!-- Étape 2: Détails du rapport de mobilité -->
    @if ($currentStep === 2)
        <div>
            <div class="mt-4">
                <x-input-label for="rapport_mobilite" :value="__('Rapport de mobilité (fichier PDF)')" />
                <input type="file" wire:model="rapport_mobilite" accept="application/pdf" class="block mt-1 w-full" required />
                <x-input-error :messages="$errors->get('rapport_mobilite')" class="mt-2" />
            </div>
        </div>
    @endif

    <!-- Étape 3: Type de mobilité -->
    @if ($currentStep === 3)
        <div>
            <div class="mt-4">
                <x-input-label for="type" :value="__('Type de mobilité')" />
                <select wire:model="type" id="type" class="block mt-1 w-full" required>
                    <option value="nationale">Nationale</option>
                    <option value="internationale">Internationale</option>
                </select>
                <x-input-error :messages="$errors->get('type')" class="mt-2" />
            </div>

            <!-- Affichage des champs conditionnels -->
            @if ($type === 'nationale')
                <div class="mt-4">
                    <x-input-label for="ville" :value="__('Ville')" />
                    <x-text-input wire:model="ville" id="ville" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('ville')" class="mt-2" />
                </div>
            @else
                <div class="mt-4">
                    <x-input-label for="pays" :value="__('Pays')" />
                    <x-text-input wire:model="pays" id="pays" class="block mt-1 w-full" required />
                    <x-input-error :messages="$errors->get('pays')" class="mt-2" />
                </div>
            @endif
        </div>
    @endif

    <!-- Navigation des étapes -->
    <div class="flex justify-between mt-4">
        @if ($currentStep > 1)
            <x-secondary-button wire:click="previousStep">{{ __('Retour') }}</x-secondary-button>
        @endif

        @if ($currentStep < 3)
            <x-primary-button wire:click="nextStep">{{ __('Suivant') }}</x-primary-button>
        @else
            <x-primary-button wire:click="store">{{ __('Enregistrer') }}</x-primary-button>
        @endif
    </div>
</div>
