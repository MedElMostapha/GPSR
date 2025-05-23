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

        $this->js("localStorage.removeItem('isTabable')");

        $this->redirect('/', navigate: true);
    }
}; ?>

<aside x-data="{
        open: window.innerWidth >= 768,
        openSubmenuPublications: {{ request()->routeIs('soumission') ? 'true' : 'false' }},
        openSubmenuMobilites: {{ request()->routeIs('mobilite-create') ? 'true' : 'false' }}
    }"
    @resize.window="open = window.innerWidth >= 768"
    :class="{ 'max-w-62': open, 'max-w-16': !open }"
    class="fixed top-0 left-0 h-screen bg-white shadow-lg transition-all duration-300 border-r border-gray-200 z-50">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="shrink-0 flex items-center justify-center bg-white transition-all duration-300">
            <a href="{{ route('dashboard') }}"
               >
                <x-dash-logo class="block fill-current text-gray-800 transition-all duration-300" />
            </a>
        </div>
        <hr class="border-t-2 border-gray-200">

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-4 overflow-y-auto">
            <ul class="space-y-3">
                <!-- Dashboard Link -->
                <li>
                    <x-nav-link :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                        class="flex items-center space-x-2">
                        <i class="fas fa-home"></i>
                        <span x-show="open"
                            class="transition-all duration-300">{{ __('Dashboard') }}</span>
                    </x-nav-link>
                </li>

                <!-- Publications Link with Submenu -->
                <li>
                    <div class="flex items-center justify-between space-x-5">
                        <x-nav-link :href="route('publication')"
                            :active="request()->routeIs('publication')"
                            class="flex items-center space-x-2">
                            <i class="fas fa-book"></i>
                            <span x-show="open"
                                class="transition-all duration-200">{{ __('Publications') }}</span>
                        </x-nav-link>
                        @if(!auth()->user()->hasRole('admin'))

                        <!-- Dropdown Icon -->
                        <i :class="openSubmenuPublications ? 'fas fa-chevron-down' : 'fas fa-chevron-left'"
                            class="transition-all duration-200 cursor-pointer"
                            @click="openSubmenuPublications = !openSubmenuPublications"></i>
                        @endif
                    </div>

                    @if(!auth()->user()->hasRole('admin'))

                    <!-- Soumission Submenu -->
                    <ul x-show="openSubmenuPublications"
                        x-transition
                        class="pl-6 space-y-2 mt-2">
                        <li>
                            <x-nav-link :href="route('soumission')"
                                :active="request()->routeIs('soumission')"
                                class="flex items-center space-x-2">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                <span x-show="open"
                                    class="transition-all duration-200">{{ __('ajouter') }}</span>
                            </x-nav-link>
                        </li>
                    </ul>
                    @endif
                </li>

                <!-- Mobilités Link with Submenu -->
                <li>
                    <div class="flex items-center justify-between space-x-5">
                        <x-nav-link :href="route('mobilite')"
                            :active="request()->routeIs('mobilite')"
                            class="flex items-center space-x-2">
                            <i class="fas fa-car"></i>
                            <span x-show="open"
                                class="transition-all duration-200">{{ __('Mobilités') }}</span>
                        </x-nav-link>
                        @if(!auth()->user()->hasRole('admin'))

                        <!-- Dropdown Icon -->
                        <i :class="openSubmenuMobilites ? 'fas fa-chevron-down' : 'fas fa-chevron-left'"
                            class="transition-all duration-200 cursor-pointer"
                            @click="openSubmenuMobilites = !openSubmenuMobilites"></i>
                        @endif
                    </div>

                    @if(!auth()->user()->hasRole('admin'))

                    <!-- Mobilités Submenu -->
                    <ul x-show="openSubmenuMobilites"
                        x-transition
                        class="pl-6 space-y-2 mt-2">
                        <li>
                            <x-nav-link :href="route('mobilite-create')"
                                :active="request()->routeIs('mobilite-create')"
                                class="flex items-center space-x-2">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                <span x-show="open"
                                    class="transition-all duration-200">{{ __('demander') }}</span>
                            </x-nav-link>
                        </li>
                    </ul>
                    @endif
                </li>




                <li>
                    <x-nav-link :href="route('archive')"
                        :active="request()->routeIs('archive')"
                        class="flex items-center space-x-2">
                        <i class="fas fa-archive"></i>
                        <span x-show="open"
                            class="transition-all duration-300">{{ __('Archive') }}</span>
                    </x-nav-link>
                </li>
                @if (auth()->user()->hasRole('admin'))

                <li>
                    <x-nav-link :href="route('paiement')"
                        :active="request()->routeIs('paiement')"
                        class="flex items-center space-x-2">
                        <i class="fas fa-credit-card"></i>
                        <span x-show="open"
                            class="transition-all duration-300">{{ __('Paiement') }}</span>
                    </x-nav-link>
                </li>
                @endif

                <!-- Profile Link -->
                <li>
                    <x-nav-link :href="route('profile')"
                        :active="request()->routeIs('profile')"
                        class="flex items-center space-x-2">
                        <i class="fas fa-user"></i>
                        <span x-show="open"
                            class="transition-all duration-300">{{ __('Profile') }}</span>
                    </x-nav-link>
                </li>


                @if(auth()->user()->hasRole('admin'))
                <!-- Profile Link -->
                <li>
                    <x-nav-link :href="route('users')"
                        :active="request()->routeIs('users')"
                        class="flex items-center space-x-2">
                        <i class="fa-solid fa-user-gear"></i>
                        <span x-show="open"
                            class="transition-all duration-300">{{ __('Users') }}</span>
                    </x-nav-link>
                </li>
                @endif

            </ul>
        </nav>

        <!-- User Information -->
        <div class="px-4 py-2 border-t border-gray-200 transition-all duration-300"
            :class="{ 'px-4': open, 'px-2': !open }">
            <div x-data="{ name: '{{ auth()->user()->name }}' }"
                x-text="name"
                x-show="open"
                x-on:profile-updated.window="name = $event.detail.name"
                class="font-medium text-base text-gray-800 transition-all duration-300"></div>
            <div x-show="open"
                class="font-medium text-xs mb-2 text-gray-500 transition-all duration-300">
                {{ auth()->user()->email }}
            </div>
            <button wire:click="logout"
                class="mt-2 w-full text-left text-sm text-gray-600 hover:text-gray-800 transition-all duration-300 flex items-center"
                x-show="!open">
                <i class="fas fa-sign-out-alt"></i>
            </button>
            <span x-show="open"
                class="mt-2 w-full text-left text-sm text-gray-600 hover:text-gray-800 transition-all duration-300">
                <button wire:click="logout"
                    class="btn-sm text-white bg-red-600 hover:bg-red-500 rounded border-none flex items-center flex-1">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="text-xs">{{ __('Log Out') }}</span>
                </button>
            </span>
        </div>
    </div>
</aside>