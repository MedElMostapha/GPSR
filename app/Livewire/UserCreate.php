<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class UserCreate extends Component
{

    public $name;
    public $email;
    public $password;
    public $isModalOpen = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ];


    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->resetInputFields();
        $this->isModalOpen = false;
    }

    public function saveUser()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'isValidated' => 1
        ]);
        $user->assignRole('admin');

        $this->closeModal();

        $this->dispatch('reload');
    }


    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
    }
    public function render()
    {
        return view('livewire.user-create');
    }
}
