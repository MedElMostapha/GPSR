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
x-data="{ 
    open: window.innerWidth >= 768, 
    openSubmenu: {{ request()->routeIs('soumission') ? 'true' : 'false' }}  // Check if soumission route is active
}"
@resize.window="open = window.innerWidth >= 768"
:class="{ 'max-w-62 z-50 ': open, 'max-w-16 z-50': !open }"
class="fixed top-0 left-0 h-screen bg-white shadow-lg transition-all duration-300 border-r border-gray-200"
>
<div class="flex flex-col h-full  ">
    <!-- Logo -->
    <div class="shrink-0 flex items-center justify-center  bg-white transition-all duration-300">
        <a href="{{ route('dashboard') }}" wire:navigate>
            <x-dash-logo  class="block fill-current text-gray-800 transition-all duration-300" />
        </a>
    </div>
    <hr class="border-t-2 border-gray-200 ">
    <!-- Navigation Links -->
    <nav class="flex-1 px-4 py-4   overflow-y-auto ">
        <ul class="space-y-3">
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

            <li x-data="{ openSubmenu: {{ request()->routeIs('soumission') ? 'true' : 'false' }} }">
                <!-- Publications Link with Submenu Toggle -->
                <div class="flex items-center justify-between space-x-5">
                    <x-nav-link
                        :href="route('publication')"
                        :active="request()->routeIs('publication')"
                        wire:navigate
                        class="flex items-center space-x-2"
                    >
                        <i class="fas fa-book"></i>
                        <span x-show="open" class="transition-all duration-200">{{ __('Publications') }}</span>
                    </x-nav-link>
                    <!-- Dropdown Icon -->
                    <i :class="openSubmenu ? 'fas fa-chevron-down' : 'fas fa-chevron-left'" class="transition-all duration-200  max-w-2  " @click="openSubmenu = !openSubmenu"></i>
                </div>

                <!-- Soumission Submenu -->
                <ul x-show="openSubmenu" x-transition class="pl-1 space-y-1 ">
                    <li>
                        <x-nav-link
                            :href="route('soumission')"
                            :active="request()->routeIs('soumission')"
                            wire:navigate
                            class="flex items-center space-x-1"
                        >
                            <i class="fas fa-upload"></i>
                            <span x-show="open" class="transition-all duration-200">{{ __('Soumission') }}</span>
                        </x-nav-link>
                    </li>
                </ul>
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


            
        </ul>
    </nav>

    <!-- User Information -->
    <div class="px-4 py-2 border-t border-gray-200 transition-all duration-300" :class="{ 'px-4': open, 'px-2': !open }">
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
        <span x-show="open" class="mt-2 w-full text-left text-sm text-gray-600 hover:text-gray-800 transition-all duration-300">
            <button wire:click="logout" class="btn-sm text-white bg-red-600 hover:bg-red-500 rounded border-none flex items-center flex-1">
                <i class="fas fa-sign-out-alt"></i>
                <span class="text-xs">{{ __('Log Out') }}</span>
            </button>
        </span>
    </div>
</div>
</aside>









