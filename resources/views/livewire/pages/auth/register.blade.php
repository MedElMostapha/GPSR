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
};

?>
<div wire:ignore>
    <form wire:submit.prevent="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Specialite -->
        <div>
            <x-input-label for="specialite" :value="__('Specialite')" />
            <x-text-input wire:model="specialite" id="specialite" class="block mt-1 w-full" type="text" name="specialite" required autocomplete="specialite" />
            <x-input-error :messages="$errors->get('specialite')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Roles -->
        <div x-data="{ selectedRole: '' }">
            <!-- Role Selection -->
            <div class="mt-4">
                <x-input-label for="role" :value="__('Role')" />
                <select 
                    x-model="selectedRole" 
                    id="role" 
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="" disabled>{{ __('Select a Role') }}</option>
                    @foreach ($roles as $id => $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('selectedRole')" class="mt-2" />
            </div>
        
            <!-- Attestation (Conditional Display) -->
            <div 
                class="mt-4" 
                x-show="selectedRole === 'doctorant'" 
                x-cloak
            >
                <label for="attestation" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Attestation') }}</label>
                <input 
                    type="file" 
                    id="attestation" 
                    wire:model="attestation" 
                    class="block w-full py-2 px-4 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                />
                @error('attestation') 
                    <span class="text-red-500 text-sm mt-2">{{ $message }}</span> 
                @enderror
            </div>
        </div>
        


        <!-- Submit Button -->
        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>



