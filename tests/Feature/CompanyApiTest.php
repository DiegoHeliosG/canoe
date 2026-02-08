<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_companies(): void
    {
        Company::factory()->count(3)->create();

        $response = $this->getJson('/api/companies');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_can_create_company(): void
    {
        $response = $this->postJson('/api/companies', ['name' => 'Canoe Intelligence']);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Canoe Intelligence');

        $this->assertDatabaseHas('companies', ['name' => 'Canoe Intelligence']);
    }

    public function test_create_company_requires_name(): void
    {
        $response = $this->postJson('/api/companies', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_update_company(): void
    {
        $company = Company::factory()->create();

        $response = $this->putJson("/api/companies/{$company->id}", ['name' => 'Updated Inc.']);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Inc.');
    }

    public function test_can_soft_delete_company(): void
    {
        $company = Company::factory()->create();

        $response = $this->deleteJson("/api/companies/{$company->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    public function test_soft_deleted_companies_not_in_list(): void
    {
        $company = Company::factory()->create();
        $company->delete();

        $response = $this->getJson('/api/companies');

        $response->assertOk()->assertJsonCount(0, 'data');
    }
}
