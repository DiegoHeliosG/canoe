<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DuplicateFundWarningResource extends JsonApiResource
{
    protected function resourceType(): string
    {
        return 'duplicate-warnings';
    }

    protected function resourceAttributes(Request $request): array
    {
        return [
            'matched_name' => $this->matched_name,
            'is_resolved' => $this->is_resolved,
            'created_at' => $this->created_at,
        ];
    }

    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->relationLoaded('fund') && $this->fund) {
            $relationships['fund'] = $this->relationshipLinkage('funds', new FundResource($this->fund));
        }

        if ($this->relationLoaded('duplicateFund') && $this->duplicateFund) {
            $relationships['duplicate_fund'] = $this->relationshipLinkage('funds', new FundResource($this->duplicateFund));
        }

        if ($this->relationLoaded('fundManager') && $this->fundManager) {
            $relationships['fund_manager'] = $this->relationshipLinkage('fund-managers', new FundManagerResource($this->fundManager));
        }

        return $relationships;
    }

    public function resourceIncluded(Request $request): array
    {
        $included = [];

        if ($this->relationLoaded('fund') && $this->fund) {
            $included[] = (new FundResource($this->fund))->toArray($request);

            if ($this->fund->relationLoaded('manager') && $this->fund->manager) {
                $included[] = (new FundManagerResource($this->fund->manager))->toArray($request);
            }
            if ($this->fund->relationLoaded('aliases')) {
                foreach ($this->fund->aliases as $alias) {
                    $included[] = (new FundAliasResource($alias))->toArray($request);
                }
            }
        }

        if ($this->relationLoaded('duplicateFund') && $this->duplicateFund) {
            $included[] = (new FundResource($this->duplicateFund))->toArray($request);

            if ($this->duplicateFund->relationLoaded('aliases')) {
                foreach ($this->duplicateFund->aliases as $alias) {
                    $included[] = (new FundAliasResource($alias))->toArray($request);
                }
            }
        }

        if ($this->relationLoaded('fundManager') && $this->fundManager) {
            $included[] = (new FundManagerResource($this->fundManager))->toArray($request);
        }

        return $included;
    }
}
