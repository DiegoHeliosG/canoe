<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class FundManagerResource extends JsonApiResource
{
    protected function resourceType(): string
    {
        return 'fund-managers';
    }

    protected function resourceAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'funds_count' => $this->whenCounted('funds'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
