<?php

use Livewire\Volt\Component;

new class extends Component {

    public $text='';

    public function showText(){
        sleep(2);
        $this->text="hello world";
    }
    //
}; ?>

<div>
    <x-mary-button label="Button" wire:click="showText" icon-right="o-lock-closed" spinner />
    {{ $text}}

</div>
