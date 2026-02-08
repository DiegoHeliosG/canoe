<?php

namespace Database\Factories;

use App\Models\FundManager;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundManagerFactory extends Factory
{
    protected $model = FundManager::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
        ];
    }
}
