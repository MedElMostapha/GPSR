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

new #[Layout('layouts.guest')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $specialite = '';
    public string $password = '';
    public string $password_confirmation = '';
    public $attestation; // For file upload
    public string $selectedRole = ''; // Bind to the selected role
    public $roles; // Holds available roles
    public int $currentStep = 1;

    /**
     * Mount the component and load available roles.
     */
    public function mount()
    {
        // Load all roles except 'admin'
        $this->roles = Role::where('name', '!=', 'admin')->pluck('name', 'id');
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
            'attestation' => 'nullable|file|mimes:pdf|max:10240', // File optional
            'selectedRole' => ['required', 'exists:roles,name'], // Ensure valid role ID
        ]);

        // Hash the password
        $validated['password'] = Hash::make($validated['password']);

        $filePath = null;

        // Handle file upload if provided
        if ($this->attestation) {
            $filePath = $this->attestation->store('attestations', 'public');
        }

        // Create the user
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'specialite' => $this->specialite,
            'password' => $validated['password'],
            'attestation' => $filePath,
        ]);

        // Assign the selected role to the user
        $user->assignRole($this->selectedRole);

        // Fire the registered event
        event(new Registered($user));

        // Log in the user
        Auth::login($user);

        // Redirect to the dashboard
        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }


public function setStep(int $step): void
{
    $this->currentStep = $step;
}

public function nextStep(): void
{
    $this->currentStep++;
}

public function previousStep(): void
{
    $this->currentStep--;
}
};



?>
<div>
    <!-- Steps Navigation -->
    <div class="steps w-full">
        <button 
            class="step {{ $currentStep === 1 ? 'step-primary' : '' }}" 
            wire:click="setStep(1)">
            {{-- <span class=" text-sm">

                {{ __('Personal Details') }}
            </span> --}}
        </button>
        <button 
            class="step {{ $currentStep === 2 ? 'step-primary' : '' }}" 
            wire:click="setStep(2)">
            {{-- <span class=" text-sm">

                {{ __('Account Details') }}
            </span> --}}
        </button>
        <button 
            class="step {{ $currentStep === 3 ? 'step-primary' : '' }}" 
            wire:click="setStep(3)">
            {{-- <span class=" text-sm">

                {{ __('Role & Attestation') }}
            </span> --}}
        </button>
        <button 
            class="step {{ $currentStep === 4 ? 'step-primary' : '' }}" 
            wire:click="setStep(4)">
            {{-- <span class=" text-sm">

                {{ __('Role & Attestation') }}
            </span> --}}
        </button>
    </div>

    <!-- Step Content -->
    <form wire:submit.prevent="register" class="mt-4">
        <!-- Step 1: Personal Details -->
        @if ($currentStep === 1)
            <div>
                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Nom')" />
                    <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Specialite -->
                <div class="mt-4">
                    <x-input-label for="specialite" :value="__('Specialité')" />
                    <x-text-input wire:model="specialite" id="specialite" class="block mt-1 w-full" type="text" name="specialite" required autocomplete="specialite" />
                    <x-input-error :messages="$errors->get('specialite')" class="mt-2" />
                </div>
            </div>
        @endif

        <!-- Step 2: Account Details -->
        @if ($currentStep === 2)
            <div>
                <!-- Email -->
               

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Mot de passe')" />
                    <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
                    <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
            </div>
        @endif

        <!-- Step 3: Role & Attestation -->
        @if ($currentStep === 3)
            <div>

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="telephone" :value="__('Telephone')" />
                    <x-text-input wire:model="telephone" id="telephone" class="block mt-1 w-full" type="text" name="telephone" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('telephone')" class="mt-2" />
                </div>
               
            </div>
        @endif

        @if ($currentStep === 4)

            <div>
                 <!-- Roles -->
                 <div>
                    <x-input-label for="role" :value="__('Fonction')" />
                    <select wire:model="selectedRole" id="role" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="" disabled>{{ __('Selectionner une fonction') }}</option>
                        @foreach ($roles as $id => $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('selectedRole')" class="mt-2" />
                </div>

                <!-- Attestation -->
                <div class="mt-4" x-show="$wire.selectedRole === 'doctorant'" x-cloak>
                    <label for="attestation" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Attestation') }}</label>
                    <input type="file" id="attestation" wire:model="attestation" class="block w-full py-2 px-4 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" />
                    @error('attestation') 
                        <span class="text-red-500 text-sm mt-2">{{ $message }}</span> 
                    @enderror
                </div>
            </div>
            
        @endif

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-4">
            @if ($currentStep > 1)
                <x-secondary-button wire:click="previousStep">
                    {{ __('Retoure') }}
                </x-secondary-button>
            @endif

            @if ($currentStep < 3)
                <x-primary-button wire:click="nextStep">
                    {{ __('Suivant') }}
                </x-primary-button>
            @else
                <x-primary-button type="submit">
                    {{ __('Enregistrer') }}
                    <x-spiner />

                </x-primary-button>
            @endif
        </div>
    </form>
</div>





