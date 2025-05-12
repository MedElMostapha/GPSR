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
    public $filterautorizee = [
            'isValidated'

    ];

    public $filterByselect=[
        'isValidated'


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
#[On('delete')]
public function delete($id)
{
    // Use SweetAlert to confirm deletion
    $this->js("
    Swal.fire({
        title: 'Are you sure?',
        text: 'You won\\'t be able to revert this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            Livewire.dispatch('confirmDelete', { id: $id });
        }
    });
");

}

#[On('confirmDelete')]
public function confirmDelete($id)
{
    // Fetch the publication
    $user = User::find($id);

    // Ensure the publication exists
    if (!$user) {
        $this->js("
            Swal.fire({
                title: 'Error!',
                text: 'user not found or already deleted.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        ");
        return;
    }

    // Delete the user
    $user->delete();

    // Refresh the users list

    // Show success message
    $this->js("
        Swal.fire({
            title: 'Deleted!',
            text: 'Your user has been deleted.',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
        });
    ");


    $this->dispatch('reload');


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
        :enabledFilters="$filterautorizee"
        :selectFilters="$filterByselect"
        :actions="[ 'delete', 'view']"
        :booleanColumns="$booleanColumns"
        :enableSearch='true'
        :columnLabels="$columnLabels" />
</div>