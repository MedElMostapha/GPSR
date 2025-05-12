<?php

use Livewire\Volt\Component;
use App\Models\Paiement;
new class extends Component {
    public $paiements;
    public $columns=['user_name','user_email','role_name','total_prix'];
    public $columnLabels =
    [
    'user_name' => 'Nom', // Custom label for 'name'
    'user_email' => 'Adresse Email', // Custom label for 'email'
    'role_name' => 'Fonction', // Custom label for 'isValidated'
    'total_prix' => 'Montant', // Custom label for 'isValidated'
    ];

    public $enabledFilters =
    [
    'user_name',
    'user_email',
    ];

    public $total=0;

    public function mount(){
        if(auth()->user()->hasRole('admin')){

            $this->getPaiements();
        }
    }

    public function getPaiements()
    {
        $paiementsData = Paiement::getPaiementsGroupedByUser(); // Retrieve the array
        $this->total = $paiementsData['total_paiement'];
        $this->paiements = $paiementsData['paiements_grouped']; // Access the correct key
    }


}; ?>

<div>

    <livewire:datatable :data="$paiements"
        :columns="$columns"
        :enableSearch="true"
        :pdfEnabled='true'
        :excelEnabled='true'
        :total="$total"
        :columnLabels="$columnLabels"
        :staticTexts="['total_prix' => 'MRO']" />

    <livewire:publicationchart />
</div>