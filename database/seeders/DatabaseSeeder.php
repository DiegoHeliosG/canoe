<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Fund;
use App\Models\FundAlias;
use App\Models\FundManager;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $blackrock = FundManager::create(['name' => 'BlackRock']);
        $vanguard = FundManager::create(['name' => 'Vanguard']);
        $fidelity = FundManager::create(['name' => 'Fidelity Investments']);

        $apple = Company::create(['name' => 'Apple Inc.']);
        $google = Company::create(['name' => 'Alphabet Inc.']);
        $microsoft = Company::create(['name' => 'Microsoft Corp.']);
        $amazon = Company::create(['name' => 'Amazon.com Inc.']);
        $tesla = Company::create(['name' => 'Tesla Inc.']);

        $brGrowth = Fund::create(['name' => 'BlackRock Growth Fund', 'start_year' => 2015, 'fund_manager_id' => $blackrock->id]);
        $brGrowth->aliases()->createMany([['name' => 'BR Growth'], ['name' => 'BlackRock GF']]);
        $brGrowth->companies()->attach([$apple->id, $google->id, $microsoft->id]);

        $brTech = Fund::create(['name' => 'BlackRock Technology Fund', 'start_year' => 2018, 'fund_manager_id' => $blackrock->id]);
        $brTech->aliases()->create(['name' => 'BR Tech Fund']);
        $brTech->companies()->attach([$apple->id, $google->id, $tesla->id]);

        $vgIndex = Fund::create(['name' => 'Vanguard Total Market Index', 'start_year' => 2010, 'fund_manager_id' => $vanguard->id]);
        $vgIndex->aliases()->create(['name' => 'VTMI']);
        $vgIndex->companies()->attach([$apple->id, $google->id, $microsoft->id, $amazon->id]);

        $vgGrowth = Fund::create(['name' => 'Vanguard Growth Fund', 'start_year' => 2012, 'fund_manager_id' => $vanguard->id]);
        $vgGrowth->companies()->attach([$tesla->id, $amazon->id]);

        $fidBlue = Fund::create(['name' => 'Fidelity Blue Chip Fund', 'start_year' => 2016, 'fund_manager_id' => $fidelity->id]);
        $fidBlue->aliases()->createMany([['name' => 'Fidelity BC'], ['name' => 'FBC Fund']]);
        $fidBlue->companies()->attach([$apple->id, $microsoft->id, $amazon->id]);
    }
}
