<?php

namespace App\Services;

use App\Models\Fund;
use Illuminate\Support\Collection;

class DuplicateDetectionService
{
    /**
     * Find existing funds under the same manager whose name or aliases
     * match (case-insensitive) the given fund's name or aliases.
     *
     * Returns a collection of ['fund' => Fund, 'matched_name' => string].
     */
    public function findDuplicates(Fund $fund): Collection
    {
        $namesToCheck = collect([$fund->name])
            ->merge($fund->aliases->pluck('name'))
            ->map(fn ($name) => mb_strtolower($name));

        $existingFunds = Fund::where('fund_manager_id', $fund->fund_manager_id)
            ->where('id', '!=', $fund->id)
            ->with('aliases')
            ->get();

        $duplicates = collect();

        foreach ($existingFunds as $existing) {
            $existingNames = collect([$existing->name])
                ->merge($existing->aliases->pluck('name'))
                ->map(fn ($name) => mb_strtolower($name));

            foreach ($namesToCheck as $nameToCheck) {
                if ($existingNames->contains($nameToCheck)) {
                    $duplicates->push([
                        'fund' => $existing,
                        'matched_name' => $nameToCheck,
                    ]);
                    break; // one match per existing fund is enough
                }
            }
        }

        return $duplicates;
    }
}
