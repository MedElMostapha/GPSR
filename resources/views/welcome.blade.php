<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Gestion des Publications Scientifiques</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="antialiased bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-gray-200 to-white text-gray-700 shadow">
        <div class="container mx-auto md:flex md:justify-between md:items-center">
            <div class="flex justify-between items-center">
                <a href="/">
                    <x-dash-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
                <button type="button"
                    class="text-gray-200 hover:text-gray-300 focus:outline-none md:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>

            <div class="hidden md:flex items-center">
                @if (Route::has('login'))
                <div class="space-x-4">
                    @auth
                    <a wire:navigate
                        href="{{ url('/dashboard') }}"
                        class="hover:underline">Tableau de bord</a>
                    @else
                    <a wire:navigate
                        href="{{ route('login') }}"
                        class="hover:underline">Se connecter</a>

                    @if (Route::has('register'))
                    <a wire:navigate
                        href="{{ route('register') }}"
                        class="ml-4 hover:underline">S'inscrire</a>
                    @endif
                    @endauth
                </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Section Hero -->
    <header class="bg-white text-black">
        <div class="container mx-auto px-6 py-12 text-center md:text-left">
            <div class="flex flex-col-reverse items-center md:flex-row">
                <div class="w-full md:w-1/2">
                    <h1 class="text-5xl font-bold leading-tight md:text-6xl">Gestions des Publications Scientifiques
                    </h1>
                    <p class="mt-6 text-lg">Suivez, gérez et archivez efficacement vos publications scientifiques et
                        rapports de séjour avec notre application innovante.</p>
                    <div class="mt-8 flex justify-center md:justify-start">
                        <a href="#features"
                            class="px-6 py-3 text-lg font-semibold bg-white text-purple-600 rounded-lg shadow-md hover:bg-gray-100">En
                            savoir plus</a>
                        <a wire:navigate
                            href="{{ route('login') }}"
                            class="ml-4 px-6 py-3 text-lg font-semibold bg-purple-800 text-white rounded-lg shadow-md hover:bg-purple-700">Se
                            connecter</a>
                    </div>
                </div>
            </div>
        </div>
    </header>


    <!-- Section Search Bar -->
    <section class="py-16 bg-gray-100 dark:bg-gray-800">
        <div class="container mx-auto px-6">
            <div class="flex justify-center">
                <div class="w-full max-w-2xl">
                    <h2 class="text-3xl font-bold text-center mb-8">Rechercher des publications</h2>
                    <div class="relative">
                        <input type="text"
                            placeholder="Rechercher par titre, auteur, ou mot-clé..."
                            class="w-full px-6 py-4 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent dark:bg-gray-700 dark:text-gray-200 dark:placeholder-gray-400" />
                        <button
                            class="absolute right-0 top-0 mt-3 mr-4 p-2 text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Fonctionnalités -->
    <section id="features"
        class="py-16 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center">Fonctionnalités</h2>
            <p class="mt-4 text-center text-lg text-gray-600 dark:text-gray-400">Découvrez les avantages de notre
                système.</p>

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div class="p-6 bg-gray-50 rounded-lg shadow-lg dark:bg-gray-700">
                    <h3 class="text-xl font-semibold">Dépôt de publications</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-300">Déposez facilement vos publications avec un suivi
                        complet.</p>
                </div>
                <div class="p-6 bg-gray-50 rounded-lg shadow-lg dark:bg-gray-700">
                    <h3 class="text-xl font-semibold">Calcul automatique des primes</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-300">Calculez vos primes en fonction des critères
                        définis (Impact Factor, Scopus, Web of Science).</p>
                </div>
                <div class="p-6 bg-gray-50 rounded-lg shadow-lg dark:bg-gray-700">
                    <h3 class="text-xl font-semibold">Identification IA</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-300">Identifiez le domaine de la publication et
                        comparez-le aux priorités nationales.</p>
                </div>
                <div class="p-6 bg-gray-50 rounded-lg shadow-lg dark:bg-gray-700">
                    <h3 class="text-xl font-semibold">Gestion de l’historique</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-300">Suivez et archivez l’historique de vos
                        publications.</p>
                </div>
                <div class="p-6 bg-gray-50 rounded-lg shadow-lg dark:bg-gray-700">
                    <h3 class="text-xl font-semibold">Base de données avancée</h3>
                    <p class="mt-4 text-gray-600 dark:text-gray-300">Filtrez par auteur, année, ou indexation (Scopus,
                        Web of Science).</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Témoignages -->
    <section class="py-16 bg-gray-100 dark:bg-gray-800">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center">Ce que disent nos utilisateurs</h2>
            <p class="mt-4 text-center text-lg text-gray-600 dark:text-gray-400">Découvrez les témoignages de nos
                utilisateurs satisfaits.</p>

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div class="p-6 bg-white rounded-lg shadow-lg dark:bg-gray-700">
                    <p class="text-gray-600 dark:text-gray-300">"Cette application m’a permis de mieux organiser mes
                        publications scientifiques. Je recommande fortement !"</p>
                    <div class="mt-4 flex items-center">
                        <img src="https://via.placeholder.com/50"
                            alt="Photo utilisateur"
                            class="w-12 h-12 rounded-full">
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold">Dr. Cheikh Dhib</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Chercheur</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 bg-white rounded-lg shadow-lg dark:bg-gray-700">
                    <p class="text-gray-600 dark:text-gray-300">"Un outil indispensable pour le suivi et l’archivage de
                        mes recherches."</p>
                    <div class="mt-4 flex items-center">
                        <img src="https://via.placeholder.com/50"
                            alt="Photo utilisateur"
                            class="w-12 h-12 rounded-full">
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold">Dr. Maye Haroune</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Enseignante-chercheuse</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 bg-white rounded-lg shadow-lg dark:bg-gray-700">
                    <p class="text-gray-600 dark:text-gray-300">"Simple, rapide et efficace. Merci pour cette solution
                        innovante."</p>
                    <div class="mt-4 flex items-center">
                        <img src="https://via.placeholder.com/50"
                            alt="Photo utilisateur"
                            class="w-12 h-12 rounded-full">
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold">Dr. Tourade Diallo</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Directeur des etudes SupNum</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Footer -->
    <footer class="bg-gray-900 text-gray-200 py-6">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; 2024 Gestion des Publications Scientifiques. Tous droits réservés.</p>
            <div class="mt-4 flex justify-center space-x-6">
                <a href="#"
                    class="hover:text-gray-400">Confidentialité</a>
                <a href="#"
                    class="hover:text-gray-400">Conditions d'utilisation</a>
                <a href="#"
                    class="hover:text-gray-400">Contact</a>
            </div>
        </div>
    </footer>
</body>

</html>