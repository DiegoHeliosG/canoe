<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CompanyResource extends JsonApiResource
{
    protected function resourceType(): string
    {
        return 'companies';
    }

    protected function resourceAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
