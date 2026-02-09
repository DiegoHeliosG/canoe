<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class FundAliasResource extends JsonApiResource
{
    protected function resourceType(): string
    {
        return 'fund-aliases';
    }

    protected function resourceAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
