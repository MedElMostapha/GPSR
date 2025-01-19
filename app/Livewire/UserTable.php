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
        $user = User::find($rowId);
        $user->delete();
        $this->datasource();
    }

    public function actions(User $row): array
    {
        return [
            Button::add('edit')
                ->slot('Modifier')
                ->id()
                ->class('btn bg-blue-600 btn-xs border-none text-white hover:bg-blue-500')
                ->dispatch('edit', ['rowId' => $row->id]),
            Button::add('edit')
                ->slot('Supprimer')
                ->id()
                ->class('btn bg-red-600 btn-xs border-none text-white hover:bg-red-500')
                ->dispatch('delete', ['rowId' => $row->id])
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
