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
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
        <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">

     <style>
        /* Add this to your CSS file or <style> tag */
            .step-content {
                opacity: 0;
                transition: opacity 0.3s ease-in-out;
            }

            .step-content.active {
                opacity: 1;
            }
      </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex bg-gray-100">
            <!-- Sidebar -->
            <livewire:layout.sidebare class="fixed h-full" />

            <!-- Main Content -->
            <div class="flex-1 ml-62 h-screen  overflow-y-auto">
                <!-- Page Header -->
                @if (isset($header))
                    <header class="fixed top-0  w-full bg-white shadow z-10  ">
                        <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6  lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="pt-[40px] pl-40 px-4 "> <!-- Adjust padding-top based on the height of the fixed header -->
                    {{ $slot }}
                </main>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
        <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
      

    </body>
</html>
