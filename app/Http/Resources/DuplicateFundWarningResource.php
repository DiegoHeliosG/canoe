<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DuplicateFundWarningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fund' => new FundResource($this->whenLoaded('fund')),
            'duplicate_fund' => new FundResource($this->whenLoaded('duplicateFund')),
            'matched_name' => $this->matched_name,
            'fund_manager' => new FundManagerResource($this->whenLoaded('fundManager')),
            'is_resolved' => $this->is_resolved,
            'created_at' => $this->created_at,
        ];
    }
}
