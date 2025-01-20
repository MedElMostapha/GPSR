<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
new class extends Component {
    public $file;
    public $fileName;
    
    #[On('file-uploaded')]
    public function handleFileUpload($event)
    {
        $this->file = $event['filePath'];
        $this->fileName = $event['fileName'];
        // dd($this->file,$this->fileName);
        
    }
    public function save(){

        try {
            $user=Auth::user();
            $user->image=$this->file;
            $user->save();
            $this->dispatch('photo-updated');
            session()->flash('message', 'Image de profile modifiée avec succès.');
            $this->reaset('file','fileName');
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
        }
       
        
    }
}; ?>

<div>
    <div class="max-w-md  ">

        @if(session()->has('message'))
        <div class="mt-2 p-2 bg-green-100 border border-green-400 text-green-700 rounded text-center text-xs">
            {{ session('message') }}
        </div>
        @elseif(session()->has('error'))
        <div class="mt-2 p-2 bg-red-100 border border-red-400 text-red-700 rounded text-center text-xs">
            {{ session('error') }}
        </div>
        @endif

        <livewire:inputfile label="Image de profile" location="profiles" objet="profile" :fileType="['jpg','jpeg','png']" />
        <button class="bg-black hover:bg-gray-700 btn-sm mt-2 text-white font-bold py-2 px-4 rounded" wire:click="save">Save</button>
    </div>

</div>
