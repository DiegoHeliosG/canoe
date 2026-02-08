<?php

namespace Tests\Unit;

use App\Models\Fund;
use App\Models\FundAlias;
use App\Models\FundManager;
use App\Services\DuplicateDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private DuplicateDetectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DuplicateDetectionService();
    }

    public function test_detects_duplicate_by_fund_name(): void
    {
        $manager = FundManager::factory()->create();
        $existing = Fund::factory()->create(['name' => 'Growth Fund', 'fund_manager_id' => $manager->id]);
        $new = Fund::factory()->create(['name' => 'Growth Fund', 'fund_manager_id' => $manager->id]);

        $duplicates = $this->service->findDuplicates($new);

        $this->assertCount(1, $duplicates);
        $this->assertEquals($existing->id, $duplicates[0]['fund']->id);
    }

    public function test_detects_duplicate_case_insensitive(): void
    {
        $manager = FundManager::factory()->create();
        Fund::factory()->create(['name' => 'Growth Fund', 'fund_manager_id' => $manager->id]);
        $new = Fund::factory()->create(['name' => 'GROWTH FUND', 'fund_manager_id' => $manager->id]);

        $duplicates = $this->service->findDuplicates($new);

        $this->assertCount(1, $duplicates);
    }

    public function test_detects_duplicate_by_alias_matching_fund_name(): void
    {
        $manager = FundManager::factory()->create();
        $existing = Fund::factory()->create(['name' => 'Growth Fund', 'fund_manager_id' => $manager->id]);
        $new = Fund::factory()->create(['name' => 'New Fund', 'fund_manager_id' => $manager->id]);
        FundAlias::factory()->create(['name' => 'Growth Fund', 'fund_id' => $new->id]);

        $new->load('aliases');
        $duplicates = $this->service->findDuplicates($new);

        $this->assertCount(1, $duplicates);
        $this->assertEquals($existing->id, $duplicates[0]['fund']->id);
    }

    public function test_detects_duplicate_by_alias_matching_alias(): void
    {
        $manager = FundManager::factory()->create();
        $existing = Fund::factory()->create(['name' => 'Fund A', 'fund_manager_id' => $manager->id]);
        FundAlias::factory()->create(['name' => 'Common Alias', 'fund_id' => $existing->id]);

        $new = Fund::factory()->create(['name' => 'Fund B', 'fund_manager_id' => $manager->id]);
        FundAlias::factory()->create(['name' => 'common alias', 'fund_id' => $new->id]);

        $new->load('aliases');
        $duplicates = $this->service->findDuplicates($new);

        $this->assertCount(1, $duplicates);
    }

    public function test_does_not_detect_duplicates_across_different_managers(): void
    {
        $manager1 = FundManager::factory()->create();
        $manager2 = FundManager::factory()->create();
        Fund::factory()->create(['name' => 'Growth Fund', 'fund_manager_id' => $manager1->id]);
        $new = Fund::factory()->create(['name' => 'Growth Fund', 'fund_manager_id' => $manager2->id]);

        $duplicates = $this->service->findDuplicates($new);

        $this->assertCount(0, $duplicates);
    }

    public function test_no_duplicates_for_unique_fund(): void
    {
        $manager = FundManager::factory()->create();
        Fund::factory()->create(['name' => 'Fund A', 'fund_manager_id' => $manager->id]);
        $new = Fund::factory()->create(['name' => 'Fund B', 'fund_manager_id' => $manager->id]);

        $duplicates = $this->service->findDuplicates($new);

        $this->assertCount(0, $duplicates);
    }
}
