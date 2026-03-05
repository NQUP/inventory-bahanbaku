<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BahanBakuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => $this->faker->word(),
            'stok' => $this->faker->numberBetween(0, 100),
            'stok_minimum' => $this->faker->numberBetween(5, 20),
            'satuan' => $this->faker->randomElement(['kg', 'liter', 'unit']),
        ];
    }
}
