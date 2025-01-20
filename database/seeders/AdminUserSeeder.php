<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vérifie si le rôle "Admin" existe, sinon le créer
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Crée un utilisateur administrateur
        $adminUser = User::firstOrCreate(
            ['email' => '22002@supnum.mr'], // Changez l'email si nécessaire
            [
                'name' => 'Medlemine',
                'password' => bcrypt('admin'), // Changez le mot de passe si nécessaire
                'isValidated' => true,
            ]
        );

        // Assigne le rôle "Admin" à l'utilisateur
        $adminUser->assignRole($adminRole);

        $this->command->info('Admin user created successfully!');
    }
}
