<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;


final class UserTable extends PowerGridComponent
{
    public string $tableName = 'user-table-netdp2-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    #[On('user-added')]
    public function datasource(): ?Builder
    {

        return User::query()
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('users.*', 'roles.name as role');
    }



    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name')
            ->add('email')
            ->add('specialite')
            ->add('isValidated')
            ->add('role');
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable(),

            Column::make('Specialite', 'specialite')
                ->sortable(),







            Column::make('Role', 'role'),
            Column::add()
                ->field('isValidated')
                ->title('Etat')
                ->toggleable(
                    trueLabel: 'Yes',
                    falseLabel: 'No'

                ),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    #[On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }
    #[On('delete')]
    public function delete($rowId): void
    {
        // Trouver l'utilisateur à supprimer
        $user = User::find($rowId);

        if (!$user) {
            $this->js("
            Swal.fire({
                title: 'Error!',
                text: 'User not found.',
                icon: 'error',
            });
        ");
            return;
        }

        // Afficher une boîte de dialogue SweetAlert2 pour confirmation
        $this->js("
        Swal.fire({
            title: 'Etes-vous sûr?',
            text: 'Cette action est irréversible!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('confirmDelete', { rowId: $user->id });
            }
        });
    ");
    }


    #[On('confirmDelete')]
    public function confirmDelete($rowId): void
    {
        // Trouver et supprimer l'utilisateur
        $user = User::find($rowId);
        if ($user) {
            $user->delete();

            // Afficher un message de succès
            $this->js(
                "Swal.fire({
                    title: 'Supprimé!',
                    text: 'L\'utilisateur a été supprimé.',
                    icon: 'success',
                });"
            );

            // Rafraîchir la source de données
            $this->datasource();
        }
    }
    #[On('view')]
    public function view($rowId): void
    {
        $user = User::find($rowId);

        $this->redirect(route('show', ['user' => $user]), navigate: true);
    }

    public function actions(User $row): array
    {
        return [

            Button::add('delete')
                ->id()
                ->icon('default-trash')
                ->class('btn bg-red-600 btn-xs border-none text-white hover:bg-red-500')
                ->dispatch('delete', ['rowId' => $row->id]),
            Button::add('view')
                ->id()
                ->icon('default-eye')
                ->class('btn bg-blue-600 btn-xs border-none text-white hover:bg-blue-500')
                ->dispatch('view', ['rowId' => $row->id])
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */

    public function onUpdatedToggleable(string|int $id, string $field, string $value): void
    {
        User::query()->find($id)->update([
            $field => e($value),
        ]);
    }
}
