<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex bg-gray-100">
            <!-- Sidebar -->
            <livewire:layout.sidebare class="fixed h-full" />

            <!-- Main Content -->
            <div class="flex-1 ml-32 h-screen overflow-y-auto">
                <!-- Page Header -->
                @if (isset($header))
                    <header class="fixed top-0 left-32 w-full bg-white shadow z-10">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="pt-[88px] px-4 "> <!-- Adjust padding-top based on the height of the fixed header -->
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
