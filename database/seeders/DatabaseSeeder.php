<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Publication;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Appel des autres seeders
        $this->call([
            AdminUserSeeder::class,
        ]);

        User::factory(10)->create(); // 10 users will be created with random roles
        Publication::factory(200)->create();
    }
}
