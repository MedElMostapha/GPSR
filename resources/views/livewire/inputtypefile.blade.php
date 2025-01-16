<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $file;

    
};
?>

<div class="max-w-md mx-auto mt-10" x-data="{ fileName: '' }">
    <div class="flex">
      <!-- File Input -->
      <input
        id="file-upload"
        type="file"
        class="sr-only"
        wire:model="file"
        @change="fileName = $event.target.files[0].name"
      >
      <!-- Custom Input Field -->
      <div class="flex-1 relative rounded-md shadow-sm">
        <input
          type="text"
          readonly
          x-model="fileName"
          class="block w-full pl-3 pr-20 py-2 border border-gray-300 rounded-l-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          placeholder="No file chosen"
        >
        <!-- Upload Button -->
        <label
          for="file-upload"
          class="absolute inset-y-0 right-0 flex items-center px-4 text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 rounded-r-md cursor-pointer"
        >
          Upload
        </label>
      </div>
    </div>
</div>