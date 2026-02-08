<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Fund;
use App\Models\FundAlias;
use App\Models\FundManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_funds(): void
    {
        $manager = FundManager::factory()->create();
        Fund::factory()->count(3)->create(['fund_manager_id' => $manager->id]);

        $response = $this->getJson('/api/funds');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'name', 'start_year', 'manager', 'aliases', 'companies']],
                'meta' => ['current_page', 'last_page'],
            ]);
    }

    public function test_can_filter_funds_by_name(): void
    {
        $manager = FundManager::factory()->create();
        Fund::factory()->create(['name' => 'Alpha Growth Fund', 'fund_manager_id' => $manager->id]);
        Fund::factory()->create(['name' => 'Beta Value Fund', 'fund_manager_id' => $manager->id]);

        $response = $this->getJson('/api/funds?name=Alpha');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Alpha Growth Fund');
    }

    public function test_can_filter_funds_by_manager(): void
    {
        $manager1 = FundManager::factory()->create();
        $manager2 = FundManager::factory()->create();
        Fund::factory()->count(2)->create(['fund_manager_id' => $manager1->id]);
        Fund::factory()->create(['fund_manager_id' => $manager2->id]);

        $response = $this->getJson("/api/funds?fund_manager_id={$manager1->id}");

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_can_filter_funds_by_year(): void
    {
        $manager = FundManager::factory()->create();
        Fund::factory()->create(['start_year' => 2020, 'fund_manager_id' => $manager->id]);
        Fund::factory()->create(['start_year' => 2021, 'fund_manager_id' => $manager->id]);

        $response = $this->getJson('/api/funds?year=2020');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_can_filter_funds_by_company(): void
    {
        $manager = FundManager::factory()->create();
        $company = Company::factory()->create();
        $fund = Fund::factory()->create(['fund_manager_id' => $manager->id]);
        $fund->companies()->attach($company);
        Fund::factory()->create(['fund_manager_id' => $manager->id]);

        $response = $this->getJson("/api/funds?company_id={$company->id}");

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_can_create_fund_with_aliases_and_companies(): void
    {
        $manager = FundManager::factory()->create();
        $companies = Company::factory()->count(2)->create();

        $response = $this->postJson('/api/funds', [
            'name' => 'New Growth Fund',
            'start_year' => 2023,
            'fund_manager_id' => $manager->id,
            'aliases' => ['NGF', 'Growth Fund 1'],
            'company_ids' => $companies->pluck('id')->all(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'New Growth Fund')
            ->assertJsonCount(2, 'data.aliases')
            ->assertJsonCount(2, 'data.companies');

        $this->assertDatabaseHas('funds', ['name' => 'New Growth Fund']);
        $this->assertDatabaseHas('fund_aliases', ['name' => 'NGF']);
        $this->assertDatabaseHas('fund_aliases', ['name' => 'Growth Fund 1']);
    }

    public function test_create_fund_validates_required_fields(): void
    {
        $response = $this->postJson('/api/funds', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'start_year', 'fund_manager_id']);
    }

    public function test_create_fund_rejects_duplicate_alias(): void
    {
        $manager = FundManager::factory()->create();
        $existingFund = Fund::factory()->create(['fund_manager_id' => $manager->id]);
        FundAlias::factory()->create(['name' => 'Existing Alias', 'fund_id' => $existingFund->id]);

        $response = $this->postJson('/api/funds', [
            'name' => 'Another Fund',
            'start_year' => 2023,
            'fund_manager_id' => $manager->id,
            'aliases' => ['Existing Alias'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['aliases.0']);
    }

    public function test_can_show_fund(): void
    {
        $fund = Fund::factory()->create();

        $response = $this->getJson("/api/funds/{$fund->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $fund->id)
            ->assertJsonPath('data.name', $fund->name);
    }

    public function test_can_update_fund(): void
    {
        $fund = Fund::factory()->create();
        $company = Company::factory()->create();

        $response = $this->putJson("/api/funds/{$fund->id}", [
            'name' => 'Updated Name',
            'aliases' => ['New Alias'],
            'company_ids' => [$company->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('fund_aliases', ['name' => 'New Alias', 'fund_id' => $fund->id]);
    }

    public function test_can_soft_delete_fund(): void
    {
        $fund = Fund::factory()->create();

        $response = $this->deleteJson("/api/funds/{$fund->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('funds', ['id' => $fund->id]);
    }

    public function test_soft_deleted_funds_not_in_list(): void
    {
        $manager = FundManager::factory()->create();
        $fund = Fund::factory()->create(['fund_manager_id' => $manager->id]);
        $fund->delete();

        $response = $this->getJson('/api/funds');

        $response->assertOk()->assertJsonCount(0, 'data');
    }
}
