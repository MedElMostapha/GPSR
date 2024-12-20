<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<aside
    x-data="{ open: window.innerWidth >= 768 }"
    @resize.window="open = window.innerWidth >= 768"
    :class="{ 'w-32': open, 'w-16': !open }"
    class="fixed top-0 left-0 h-screen bg-white  shadow-lg overflow-y-auto transition-all duration-300 border-r border-gray-200"
>
    <div class="flex flex-col h-full">
        <!-- Toggle Button -->
        {{-- <button
            @click="open = !open"
            class="absolute top-4 right-[3px] bg-gray-200 hover:bg-gray-300 rounded-full p-2 focus:outline-none focus:ring"
        >
            <i class="fas" :class="open ? 'fa-chevron-left' : 'fa-chevron-right'"></i>
        </button> --}}

        <!-- Logo -->
        <div
            class="shrink-0 flex items-center justify-center py-9 bg-gray-100 transition-all duration-300"
        >
            <a href="{{ route('dashboard') }}" wire:navigate>
                <x-application-logo
                    :class="{ 'h-9 w-auto': open, 'h-6 w-6': !open }"
                    class="block fill-current text-gray-800 transition-all duration-300"
                />
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-2">
            <ul class="space-y-1">
                <li>
                    <x-nav-link
                        :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                        wire:navigate
                        class="flex items-center space-x-2"
                    >
                        <i class="fas fa-home"></i>
                        <span x-show="open" class="transition-all duration-300">{{ __('Dashboard') }}</span>
                    </x-nav-link>
                </li>
                <li>
                    <x-nav-link
                        :href="route('profile')"
                        :active="request()->routeIs('profile')"
                        wire:navigate
                        class="flex items-center space-x-2"
                    >
                        <i class="fas fa-user"></i>
                        <span x-show="open" class="transition-all duration-300">{{ __('Profile') }}</span>
                    </x-nav-link>
                </li>
                <!-- Add more list items for additional pages -->
            </ul>
        </nav>

        <!-- User Information -->
        <div
            class="px-4 py-2 border-t border-gray-200 transition-all duration-300"
            :class="{ 'px-4': open, 'px-2': !open }"
        >
            <div
                x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                x-text="name"
                x-show="open"
                x-on:profile-updated.window="name = $event.detail.name"
                class="font-medium text-base text-gray-800 transition-all duration-300"
            ></div>
            <div x-show="open" class="font-medium text-xs mb-2 text-gray-500 transition-all duration-300">
                {{ auth()->user()->email }}
            </div>
            <button
                wire:click="logout"
                class="mt-2 w-full text-left text-sm text-gray-600 hover:text-gray-800 transition-all duration-300 flex items-center"
                x-show="!open"
            >
                <i class="fas fa-sign-out-alt"></i>
            </button>
            <span x-show="open" class="mt-2 w-full  text-left text-sm text-gray-600 hover:text-gray-800 transition-all duration-300">
                <x-mary-button wire:click="logout" class="btn-sm text-white  bg-red-600 hover:bg-red-500 border-none flex items-center  flex-1">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class=" text-xs">{{ __('Log Out') }}</span>
                </x-mary-button>
                
            </span>
        </div>
    </div>
</aside>





