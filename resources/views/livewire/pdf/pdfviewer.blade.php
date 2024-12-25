<?php

use Livewire\Volt\Component;

new class extends Component {
    public $fileUrl;

    public function mount($fileUrl)
    {
        $this->fileUrl = $fileUrl;
    }

    public function render(): mixed
    {
        return <<<HTML
        <div>
            <iframe src="{$this->fileUrl}" style="width: 100%; height: 100vh;" frameborder="0"></iframe>
        </div>
        HTML;
    }
};
