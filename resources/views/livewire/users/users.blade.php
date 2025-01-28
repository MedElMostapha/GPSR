<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\On;

new class extends Component {
    public $users;
    public $columns = ['name','specialite', 'email','roles','isValidated']; // Define the columns here
    public $columnLabels = [
        'name' => 'Nom', // Custom label for 'name'
        'email' => 'Adresse Email', // Custom label for 'email'
        'isValidated' => 'Etat', // Custom label for 'isValidated'
        'roles' => 'Fonction', // Custom label for 'roles'
    ];
    public $booleanColumns = [
        'isValidated' => [
        'true' => ['text' => 'Activé', 'class' => 'bg-green-100 text-green-800'],
        'false' => ['text' => 'Desactivé', 'class' => 'bg-red-100 text-red-800'],
        ], // Custom text for boolean column
    ];

    public function mount(){
       $this->users = User::with('roles:name')->get()->map(function ($user) {
        $user->roles = $user->roles->pluck('name')->join(', '); // Join the roles with a comma
        return $user;
        });

    }

    // Handle edit action
    public function edit($id): void
    {
        // Logic to handle edit
        $this->dispatch('open-edit-modal', id: $id);
    }

    // Handle delete action
    public function delete($id): void
    {
        // Logic to handle delete
        $this->dispatch('open-delete-modal', id: $id);
    }
    #[On('view')]
    public function view($id): void
    {
        $this->redirect(route('show', $id));
    }
};
?>

<div>
    <!-- Pass the $columns, $columnLabels, and $actions variables to the data-table component -->
    <livewire:datatable :columns="$columns"
        :data="$users"
        :actions="[ 'delete', 'view']"
        :booleanColumns="$booleanColumns"
        :columnLabels="$columnLabels" />
</div>