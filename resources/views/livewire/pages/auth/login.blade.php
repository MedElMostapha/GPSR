<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;


    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();


            $user = $this->form->user();
            if($user->isValidated){
                
                $this->form->authenticate();
    
                Session::regenerate();
    
                $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            }else{
                session()->flash('message', 'Desolé, votre compte n\'est pas encore validé. Veuillez contacter l\'administrateur.');
            }    
            
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    @if (session()->has('message'))
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, 10000)" 
        class="bg-yellow-500 text-white p-4 rounded-md mb-4 flex items-center justify-between"
    >
        <div class="flex items-center">
            <!-- Icon -->
            <i class="fas fa-info-circle mr-2"></i>
            <span>{{ session('message') }}</span>
        </div>
        <button @click="show = false" class="text-white bg-transparent hover:bg-yellow-700 rounded-full p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <!-- Login Button with Loading Spinner -->
            <x-primary-button class="ms-3" wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>{{ __('Log in') }}</span>
                <x-mary-loading wire:loading>
                    {{-- <x-icon name="loading" class="animate-spin h-5 w-5" /> <!-- Mary UI spinner --> --}}
                </x-mary-loading>
            </x-primary-button>

            <a href="{{ route('register') }}" class="ms-3 text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" wire:navigate>
                {{ __('Register') }}
            </a>
        </div>
    </form>
</div>
