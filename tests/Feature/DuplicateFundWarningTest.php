<?php

namespace Tests\Feature;

use App\Events\DuplicateFundWarningEvent;
use App\Listeners\PersistDuplicateFundWarning;
use App\Models\DuplicateFundWarning;
use App\Models\Fund;
use App\Models\FundManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DuplicateFundWarningTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_duplicate_fund_dispatches_event(): void
    {
        Event::fake([DuplicateFundWarningEvent::class]);

        $manager = FundManager::factory()->create();
        Fund::factory()->create(['name' => 'Growth Fund', 'fund_manager_id' => $manager->id]);

        $this->postJson('/api/funds', [
            'name' => 'Growth Fund',
            'start_year' => 2023,
            'fund_manager_id' => $manager->id,
        ]);

        Event::assertDispatched(DuplicateFundWarningEvent::class);
    }

    public function test_creating_unique_fund_does_not_dispatch_event(): void
    {
        Event::fake([DuplicateFundWarningEvent::class]);

        $manager = FundManager::factory()->create();
        Fund::factory()->create(['name' => 'Growth Fund', 'fund_manager_id' => $manager->id]);

        $this->postJson('/api/funds', [
            'name' => 'Value Fund',
            'start_year' => 2023,
            'fund_manager_id' => $manager->id,
        ]);

        Event::assertNotDispatched(DuplicateFundWarningEvent::class);
    }

    public function test_listener_persists_warning(): void
    {
        $manager = FundManager::factory()->create();
        $fund1 = Fund::factory()->create(['fund_manager_id' => $manager->id]);
        $fund2 = Fund::factory()->create(['fund_manager_id' => $manager->id]);

        $event = new DuplicateFundWarningEvent(
            fundId: $fund2->id,
            duplicateFundId: $fund1->id,
            matchedName: 'growth fund',
            fundManagerId: $manager->id,
        );

        $listener = new PersistDuplicateFundWarning();
        $listener->handle($event);

        $this->assertDatabaseHas('duplicate_fund_warnings', [
            'fund_id' => $fund2->id,
            'duplicate_fund_id' => $fund1->id,
            'matched_name' => 'growth fund',
            'is_resolved' => false,
        ]);
    }

    public function test_end_to_end_duplicate_detection(): void
    {
        $manager = FundManager::factory()->create();
        Fund::factory()->create(['name' => 'Growth Fund', 'fund_manager_id' => $manager->id]);

        // With sync queue, the listener runs immediately
        $this->postJson('/api/funds', [
            'name' => 'Growth Fund',
            'start_year' => 2023,
            'fund_manager_id' => $manager->id,
        ]);

        $this->assertDatabaseHas('duplicate_fund_warnings', [
            'matched_name' => 'growth fund',
            'fund_manager_id' => $manager->id,
            'is_resolved' => false,
        ]);
    }

    public function test_can_list_unresolved_warnings(): void
    {
        $manager = FundManager::factory()->create();
        $fund1 = Fund::factory()->create(['fund_manager_id' => $manager->id]);
        $fund2 = Fund::factory()->create(['fund_manager_id' => $manager->id]);

        DuplicateFundWarning::create([
            'fund_id' => $fund2->id,
            'duplicate_fund_id' => $fund1->id,
            'matched_name' => 'test',
            'fund_manager_id' => $manager->id,
        ]);

        $response = $this->getJson('/api/duplicate-warnings');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_can_resolve_warning(): void
    {
        $manager = FundManager::factory()->create();
        $fund1 = Fund::factory()->create(['fund_manager_id' => $manager->id]);
        $fund2 = Fund::factory()->create(['fund_manager_id' => $manager->id]);

        $warning = DuplicateFundWarning::create([
            'fund_id' => $fund2->id,
            'duplicate_fund_id' => $fund1->id,
            'matched_name' => 'test',
            'fund_manager_id' => $manager->id,
        ]);

        $response = $this->patchJson("/api/duplicate-warnings/{$warning->id}/resolve");

        $response->assertOk()
            ->assertJsonPath('data.is_resolved', true);

        // Resolved warnings should not appear in list
        $listResponse = $this->getJson('/api/duplicate-warnings');
        $listResponse->assertOk()->assertJsonCount(0, 'data');
    }
}
