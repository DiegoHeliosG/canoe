<?php

namespace Database\Factories;

use App\Models\Fund;
use App\Models\FundAlias;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundAliasFactory extends Factory
{
    protected $model = FundAlias::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true) . ' Alias',
            'fund_id' => Fund::factory(),
        ];
    }
}
