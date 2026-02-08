<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_year' => $this->start_year,
            'fund_manager_id' => $this->fund_manager_id,
            'manager' => new FundManagerResource($this->whenLoaded('manager')),
            'aliases' => FundAliasResource::collection($this->whenLoaded('aliases')),
            'companies' => CompanyResource::collection($this->whenLoaded('companies')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
