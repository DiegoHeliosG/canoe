<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\FundManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundManagerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_fund_managers(): void
    {
        FundManager::factory()->count(3)->create();

        $response = $this->getJson('/api/fund-managers');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_can_create_fund_manager(): void
    {
        $response = $this->postJson('/api/fund-managers', ['name' => 'BlackRock']);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'BlackRock');

        $this->assertDatabaseHas('fund_managers', ['name' => 'BlackRock']);
    }

    public function test_create_fund_manager_requires_name(): void
    {
        $response = $this->postJson('/api/fund-managers', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_update_fund_manager(): void
    {
        $manager = FundManager::factory()->create();

        $response = $this->putJson("/api/fund-managers/{$manager->id}", ['name' => 'Updated Name']);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_can_soft_delete_fund_manager_without_funds(): void
    {
        $manager = FundManager::factory()->create();

        $response = $this->deleteJson("/api/fund-managers/{$manager->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('fund_managers', ['id' => $manager->id]);
    }

    public function test_cannot_delete_fund_manager_with_funds(): void
    {
        $manager = FundManager::factory()->create();
        Fund::factory()->create(['fund_manager_id' => $manager->id]);

        $response = $this->deleteJson("/api/fund-managers/{$manager->id}");

        $response->assertStatus(409)
            ->assertJsonPath('message', 'Cannot delete fund manager with existing funds. Remove or reassign funds first.');

        $this->assertDatabaseHas('fund_managers', ['id' => $manager->id, 'deleted_at' => null]);
    }

    public function test_soft_deleted_managers_not_in_list(): void
    {
        $manager = FundManager::factory()->create();
        $manager->delete();

        $response = $this->getJson('/api/fund-managers');

        $response->assertOk()->assertJsonCount(0, 'data');
    }
}
