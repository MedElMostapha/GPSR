<?php

use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
new #[Layout('layouts.guest')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $specialite = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $selectedRole = ''; // Bind to the selected role
    public $roles; // Holds available roles
    public $identity_copy; // For identity card upload
    public $identity_fileName;
    public $attestation; // For attestation upload
    public $attestation_fileName;
    public int $currentStep = 1;
    public bool $loading = false; // Add a loading state
    public int $uploadProgress = 0; // Track upload progress

    /**
     * Mount the component and load available roles.
     */
    public function mount()
    {
        // Load all roles except 'admin'
        $this->roles();
    }


    public function roles(){
        $this->roles = Role::where('name', '!=', 'admin')->pluck('name', 'id');

    }

    #[On('file-uploaded')]
    public function handleFileUpload($event)
    {
        if($this->identity_copy == null && $event['objet']== 'identite'){
            $this->identity_copy = $event['filePath'];
            $this->identity_fileName = $event['fileName'];

        }

        if($this->attestation == null && $event['objet']== 'attestation'){
            $this->attestation = $event['filePath'];
            $this->attestation_fileName = $event['fileName'];

        }

        // dd($this->file);
        
    }

    /**
     * Register a new user.
     */
    public function register(): void
    {
        // Validate input fields
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'specialite' => ['required', 'string', 'max:255'],
            'selectedRole' => ['required', 'exists:roles,name'], // Ensure valid role ID
        ]);

        if($this->selectedRole === 'doctorant' && $this->attestation == null){
            $this->dispatch("file-required");
            return;
            
        }

        if($this->identity_copy == null){
            $this->dispatch("file-required");
            return;
        }

        // Set loading state to true
        $this->loading = true;

        // Store uploaded files
     
        // Hash the password
        $validated['password'] = Hash::make($validated['password']);

        // Create the user
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'specialite' => $this->specialite,
            'password' => $validated['password'],
            'identity_copy' => $this->identity_copy,
            'identitity_fileName' => $this->identity_fileName,
            'attestation' => $this->attestation,
            'attestation_fileName' => $this->attestation_fileName

        ]);

        // Assign the selected role to the user
        $user->assignRole($this->selectedRole);

        // Fire the registered event
        event(new Registered($user));

        // Redirect to the login page
        $this->redirect(route('login', absolute: false), navigate: true);
    }

    public function setStep(int $step): void
{
    $this->currentStep = $step;
    $this->dispatch('step-changed'); // Emit event for step change
}

public function nextStep(): void
{
    // Validate the current step before proceeding
    if ($this->currentStep === 1) {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'specialite' => ['required', 'string', 'max:255'],
        ]);
    } elseif ($this->currentStep === 2) {
        $this->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);
    } elseif ($this->currentStep === 3) {
        $this->validate([
            'selectedRole' => ['required', 'exists:roles,name'],
        ]);
    } elseif ($this->currentStep === 4) {
        if($this->selectedRole === 'doctorant' && $this->attestation == null){
            $this->dispatch("file-required");
            return;
            
        }

        if($this->identity_copy == null){
            $this->dispatch("file-required");
            return;
        }
    }

    // Only proceed to the next step if validation passes
    if ($this->currentStep < 4) {
        $this->currentStep++;
        $this->dispatch('step-changed'); // Emit event for step change
    }
}

public function previousStep(): void
{
    $this->currentStep--;
    $this->dispatch('step-changed'); // Emit event for step change
}

    

    /**
     * Update the upload progress.
     */
    public function updateUploadProgress($progress)
    {
        $this->uploadProgress = $progress;
    }
}
?>
<div >
    <!-- Steps Navigation -->
    {{-- <div class="steps w-full">
        <button class="step {{ $currentStep === 1 ? 'step-primary' : '' }}" wire:click="setStep(1)"></button>
        <button class="step {{ $currentStep === 2 ? 'step-primary' : '' }}" wire:click="setStep(2)"></button>
        <button class="step {{ $currentStep === 3 ? 'step-primary' : '' }}" wire:click="setStep(3)"></button>
        <button class="step {{ $currentStep === 4 ? 'step-primary' : '' }}" wire:click="setStep(4)"></button>
    </div> --}}

    <!-- Step Content -->
    <form wire:submit.prevent="register" class="mt-4">
        <!-- Step 1: Personal Details -->
        <div class="step-content {{ $currentStep === 1 ? 'active' : '' }}">
            @if ($currentStep === 1)
                <div>
                    <!-- Name -->
                    <div>
                        <x-input-label for="name" :value="__('Nom')" />
                        <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
                        @error('name') <span class="text-red-500 text-sm mt-2">{{ $message }}</span> @enderror
                    </div>

                    <!-- Specialite -->
                    <div class="mt-4">
                        <x-input-label for="specialite" :value="__('Specialité')" />
                        <x-text-input wire:model="specialite" id="specialite" class="block mt-1 w-full" type="text" name="specialite" required autocomplete="specialite" />
                        @error('specialite') <span class="text-red-500 text-sm mt-2">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif
        </div>

        <!-- Step 2: Account Details -->
        <div class="step-content {{ $currentStep === 2 ? 'active' : '' }}">
            @if ($currentStep === 2)
                <div>
                    <!-- Email -->
                    <div class="mt-4">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
                        @error('email') <span class="text-red-500 text-sm mt-2">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <x-input-label for="password" :value="__('Mot de passe')" />
                        <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                        @error('password') <span class="text-red-500 text-sm mt-2">{{ $message }}</span> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mt-4">
                        <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
                        <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                        @error('password_confirmation') <span class="text-red-500 text-sm mt-2">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif
        </div>

        <!-- Step 3: Role & Attestation -->
        <div class="step-content {{ $currentStep === 3 ? 'active' : '' }}">
            @if ($currentStep === 3)
                <div>
                    <div class="mt-4">
                        <x-input-label for="role" :value="__('Rôle')" />
                        <select wire:model="selectedRole" id="role" class="input block w-full mt-1 bg-white text-black border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="" disabled>{{ __('Sélectionner un rôle') }}</option>
                            @foreach ($roles as $id => $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                        @error('selectedRole') <span class="text-red-500 text-sm mt-2">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif
        </div>

        <!-- Step 4: Identity & Attestation -->
        <div class="step-content {{ $currentStep === 4 ? 'active' : '' }}">
            @if ($currentStep === 4)
                <div>
                    <!-- Identity Copy -->
                    <livewire:inputfile label="Copie de la carte d'identité"  :location="'identities'" :objet="'identite'" />        <!-- Progress Bar -->


                    <!-- Attestation (only for doctorant) -->
                    @if ($selectedRole === 'doctorant')
                        <div class="mt-4">
                            <livewire:inputfile label="Attestation"  :location="'attestations'" :objet="'attestation'" />        <!-- Progress Bar -->

                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-4">
            @if ($currentStep > 1)
                <x-secondary-button wire:click="previousStep">{{ __('Retour') }}</x-secondary-button>
            @endif

            @if ($currentStep < 4)
                <x-primary-button wire:click="nextStep">{{ __('Suivant') }}</x-primary-button>
            @else
                <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="register">
                    <span wire:loading.remove wire:target="register">{{ __('S\'inscrire') }}</span>
                    <x-mary-loading wire:loading wire:target="register">
                        {{-- <div class="animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full"></div> --}}
                    </x-mary-loading>
                </x-primary-button>
            @endif
        </div>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" wire:navigate>
            {{ __('Deja inscrit?') }}
        </a>
    </div>
</div>

