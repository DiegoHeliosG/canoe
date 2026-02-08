<?php

namespace Database\Factories;

use App\Models\Fund;
use App\Models\FundManager;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundFactory extends Factory
{
    protected $model = Fund::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true) . ' Fund',
            'start_year' => fake()->year(),
            'fund_manager_id' => FundManager::factory(),
        ];
    }
}
