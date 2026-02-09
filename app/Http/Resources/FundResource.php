<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class FundResource extends JsonApiResource
{
    protected function resourceType(): string
    {
        return 'funds';
    }

    protected function resourceAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'start_year' => $this->start_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function resourceRelationships(Request $request): array
    {
        $relationships = [];

        if ($this->relationLoaded('manager') && $this->manager) {
            $relationships['manager'] = $this->relationshipLinkage('fund-managers', new FundManagerResource($this->manager));
        }

        if ($this->relationLoaded('aliases')) {
            $relationships['aliases'] = $this->relationshipLinkageCollection('fund-aliases', $this->aliases);
        }

        if ($this->relationLoaded('companies')) {
            $relationships['companies'] = $this->relationshipLinkageCollection('companies', $this->companies);
        }

        return $relationships;
    }

    public function resourceIncluded(Request $request): array
    {
        $included = [];

        if ($this->relationLoaded('manager') && $this->manager) {
            $included[] = (new FundManagerResource($this->manager))->toArray($request);
        }

        if ($this->relationLoaded('aliases')) {
            foreach ($this->aliases as $alias) {
                $included[] = (new FundAliasResource($alias))->toArray($request);
            }
        }

        if ($this->relationLoaded('companies')) {
            foreach ($this->companies as $company) {
                $included[] = (new CompanyResource($company))->toArray($request);
            }
        }

        return $included;
    }
}
