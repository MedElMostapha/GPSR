<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{

    public string $bio = '';
    public string $about = '';
    public string $facebook = '';
    public string $twitter = '';
    public string $linkedin = '';
    public string $github = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        
        $this->facebook = Auth::user()->facebook?Auth::user()->facebook:'';
        $this->twitter = Auth::user()->twitter?Auth::user()->twitter:'';
        $this->linkedin = Auth::user()->linkedin?Auth::user()->linkedin:'';
        $this->bio = Auth::user()->bio?Auth::user()->bio:'';
        $this->about = Auth::user()->about?Auth::user()->about:'';
        $this->github = Auth::user()->github?Auth::user()->github:'';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'facebook' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:255'],
            'github' => ['nullable', 'url', 'max:255'],
        ]);


        $user->fill($validated);

      
        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
   
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informations supplementaires') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Modifiez vos informations supplementaires") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        

        <!-- Bio Field -->
        <div>
            
            <x-input-label for="bio" :value="__('Bio')" />
            <x-text-input wire:model="bio" id="bio" name="bio" type="text" class="mt-1 block w-full" autocomplete="bio" />
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
          
        </div>

        <!-- About Field -->
        <div>
            <x-input-label for="about" :value="__('About')" />
            <textarea wire:model="about" id="about" name="about" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3"></textarea>
            <x-input-error class="mt-2" :messages="$errors->get('about')" />
            
            
            
        </div>

        <!-- Facebook Field -->
        <div>
            <x-input-label for="facebook" :value="__('Facebook')" />
            <x-text-input wire:model="facebook" id="facebook" name="facebook" type="text" class="mt-1 block w-full" autocomplete="facebook" />
            <x-input-error class="mt-2" :messages="$errors->get('facebook')" />
            
          
        </div>

        <!-- Twitter Field -->
        <div>
            <x-input-label for="twitter" :value="__('Twitter')" />
            <x-text-input wire:model="twitter" id="twitter" name="twitter" type="text" class="mt-1 block w-full" autocomplete="twitter" />
            <x-input-error class="mt-2" :messages="$errors->get('twitter')" />
           
        </div>

        <!-- LinkedIn Field -->
        <div>
            <x-input-label for="linkedin" :value="__('LinkedIn')" />
            <x-text-input wire:model="linkedin" id="linkedin" name="linkedin" type="text" class="mt-1 block w-full" autocomplete="linkedin" />
            <x-input-error class="mt-2" :messages="$errors->get('linkedin')" />
           
        </div>

        <!-- GitHub Field -->
        <div>
            <x-input-label for="github" :value="__('GitHub')" />
            <x-text-input wire:model="github" id="github" name="github" type="text" class="mt-1 block w-full" autocomplete="github" />
            <x-input-error class="mt-2" :messages="$errors->get('github')" />
           
        </div>

        <!-- Save Button and Action Message -->
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>

            <x-action-message class="me-3 text-green-400" on="profile-updated">
                {{ __('Enregistré.') }}
            </x-action-message>
        </div>
    </form>
</section>
