<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'bio' => $this->faker->sentence(),
            'about' => $this->faker->realText(),
            'facebook' => $this->faker->url(),
            'twitter' => $this->faker->url(),
            'linkedin' => $this->faker->url(),
            'github' => $this->faker->url(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password
            'specialite' => $this->faker->jobTitle(),
            'attestation' => $this->faker->boolean() ? 'Attestation XYZ' : null,
            'attestation_fileName' => $this->faker->boolean() ? 'attestation.pdf' : null,
            'image' => 'default.png',
            'isValidated' => $this->faker->boolean(),
            'identitity_fileName' => $this->faker->boolean() ? 'identity.pdf' : null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            // Randomly assign either 'enseignant chercheur' or 'doctorant'
            $role = Role::whereIn('name', ['enseignant chercheur', 'doctorant'])->inRandomOrder()->first();
            if ($role) {
                $user->assignRole($role);
            }
        });
    }
}
