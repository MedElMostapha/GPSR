<?php

use Livewire\Volt\Component;

new class extends Component {
    public $fileUrl;

    public function mount($fileUrl)
    {
        // Sanitize the file URL
        $this->fileUrl = filter_var($fileUrl, FILTER_SANITIZE_URL);
    }

    public function render(): mixed
    {
        $escapedFileUrl = htmlspecialchars($this->fileUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <div>
            <a href="javascript:history.back()" class="text-blue-500 mb-4 hover:text-blue-700 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                {{ __("Retour") }}
            </a>
            <iframe src="{$escapedFileUrl}" style="width: 100%; height: 100vh;" frameborder="0"></iframe>
        </div>
        HTML;
    }
};