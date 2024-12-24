<?php

use Livewire\Volt\Component;

new class extends Component {
    public $publications;
    public $viewingFileUrl = null;

    public function mount()
    {
        // Fetch publications for the authenticated user
        $this->publications = \App\Models\Publication::where('user_id', auth()->id())->latest()->get();
    }

    public function viewFile($fileUrl)
    {
        $this->viewingFileUrl = $fileUrl;
    }

    
};

?>

<div class="p-4">
    @if ($viewingFileUrl)
        @livewire('pdf.pdfviewer', ['fileUrl' => $viewingFileUrl])
    @else
        <h1 class="text-2xl font-bold mb-4">Publications</h1>

        <!-- Publications list -->
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($publications as $publication)
                <div class="card bg-white shadow-md rounded-lg p-4">
                    <h2 class="font-bold text-lg">{{ $publication->title }}</h2>
                    <p class="text-sm text-gray-600">{{ $publication->journal }}</p>
                    <p class="text-sm text-gray-600">{{ $publication->publication_date }}</p>
                    <p class="text-sm text-gray-600">Impact Factor: {{ $publication->impact_factor ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-600">Indexation: {{ $publication->indexation }}</p>
                    <p class="text-sm mt-2">{{ $publication->abstract }}</p>

                    @if ($publication->file_path)
                        <a href="#" class="text-blue-500 text-sm mt-2 underline" wire:click.prevent="viewFile('{{ asset('storage/' . str_replace('public/', '', $publication->file_path)) }}')">
                            View File
                        </a>
                    @endif

                    <div class="mt-4">
                        <h3 class="text-sm font-bold">Authors:</h3>
                        <ul class="list-disc list-inside">
                            @foreach ($publication->authors as $author)
                                <li>{{ $author->name }} ({{ $author->email ?? 'N/A' }})</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No publications found.</p>
            @endforelse
        </div>
    @endif
</div>

