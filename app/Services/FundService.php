<?php

namespace App\Services;

use App\Events\DuplicateFundWarningEvent;
use App\Models\Fund;
use Illuminate\Support\Facades\DB;

class FundService
{
    public function __construct(
        private DuplicateDetectionService $duplicateDetection,
    ) {}

    public function create(array $attributes, array $aliases = [], array $companyIds = []): Fund
    {
        $fund = DB::transaction(function () use ($attributes, $aliases, $companyIds) {
            $fund = Fund::create($attributes);

            if ($aliases) {
                $fund->aliases()->createMany(
                    collect($aliases)->map(fn ($name) => ['name' => $name])->all()
                );
            }

            if ($companyIds) {
                $fund->companies()->attach($companyIds);
            }

            return $fund->load('aliases', 'manager', 'companies');
        });

        $this->checkForDuplicates($fund);

        return $fund;
    }

    public function update(Fund $fund, array $attributes, ?array $aliases = null, ?array $companyIds = null): Fund
    {
        return DB::transaction(function () use ($fund, $attributes, $aliases, $companyIds) {
            $fund->update($attributes);

            if ($aliases !== null) {
                $fund->aliases()->delete();
                if ($aliases) {
                    $fund->aliases()->createMany(
                        collect($aliases)->map(fn ($name) => ['name' => $name])->all()
                    );
                }
            }

            if ($companyIds !== null) {
                $fund->companies()->sync($companyIds);
            }

            return $fund->load('manager', 'aliases', 'companies');
        });
    }

    private function checkForDuplicates(Fund $fund): void
    {
        $duplicates = $this->duplicateDetection->findDuplicates($fund);

        if ($duplicates->isNotEmpty()) {
            $first = $duplicates->first();
            DuplicateFundWarningEvent::dispatch(
                $fund->id,
                $first['fund']->id,
                $first['matched_name'],
                $fund->fund_manager_id,
            );
        }
    }
}
