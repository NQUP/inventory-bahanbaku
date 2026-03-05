<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PemesananFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tanggal' => $this->faker->date(),
            'status' => $this->faker->randomElement(['pending', 'diterima', 'ditolak']),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => now(),
        ];
    }
}
