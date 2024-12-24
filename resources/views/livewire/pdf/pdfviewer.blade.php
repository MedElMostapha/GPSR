<?php

use Livewire\Volt\Component;

new class extends Component {
    public $fileUrl;

        public function mount($fileUrl)
        {
            $this->fileUrl = $fileUrl;
        }

        
}; ?>

<div wire:ignore>
    <div id="pspdfkit-container" style="height: 100vh; width: 100%;">
    </div>
</div>

<script>
    document.addEventListener('livewire:load', () => {
        const container = document.getElementById('pspdfkit-container');

        PSPDFKit.load({
            container,
            document: @js($fileUrl), // Pass the PDF URL to PSPDFKit
            baseUrl: "{{ asset('pspdfkit') }}/" // Update to match your PSPDFKit asset path
        }).then(instance => {
            console.log('PSPDFKit loaded', instance);
        }).catch(error => {
            console.error('Failed to load PSPDFKit', error);
        });
    });
</script>

