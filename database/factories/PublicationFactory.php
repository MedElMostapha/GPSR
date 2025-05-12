<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicationFactory extends Factory
{
    protected $model = Publication::class;

    public function definition(): array
    {
        $year = 2024; // Set a fixed year for all generated publications
        $type = $this->faker->randomElement(['WEB SCIENCE', 'SCOPUS']);
        $prix = $type === 'WEB SCIENCE' ? 4000 : 2000;

        return [
            'title' => $this->faker->sentence(),
            'abstract' => $this->faker->paragraph(),
            'publication_date' => $this->faker->dateTimeBetween("$year-01-01", "$year-12-31")->format('Y-m-d'),
            'journal' => $this->faker->company(),
            'isArchived' => false,
            'isPublished' => $this->faker->boolean(),
            'isAccepted' => false,
            'type' => $type,
            'prix' => $prix,
            'motifs' => $this->faker->sentence(),
            'user_id' => User::factory(),
            'file_path' => 'publications/' . $this->faker->uuid() . '.pdf',
            'file_name' => $this->faker->word() . '.pdf',
            'rib' => $this->faker->bankAccountNumber(),
            'rib_name' => $this->faker->name(),
        ];
    }
}
