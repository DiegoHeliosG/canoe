<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class JsonApiResource extends JsonResource
{
    abstract protected function resourceType(): string;

    abstract protected function resourceAttributes(Request $request): array;

    protected function resourceRelationships(Request $request): array
    {
        return [];
    }

    public function resourceIncluded(Request $request): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        $result = [
            'type' => $this->resourceType(),
            'id' => (string) $this->id,
            'attributes' => $this->resourceAttributes($request),
        ];

        $relationships = $this->resourceRelationships($request);
        if ($relationships) {
            $result['relationships'] = $relationships;
        }

        return $result;
    }

    public function with(Request $request): array
    {
        $included = $this->resourceIncluded($request);

        if ($included) {
            return ['included' => $included];
        }

        return [];
    }

    protected function relationshipLinkage(string $type, JsonApiResource $resource): array
    {
        return [
            'data' => [
                'type' => $type,
                'id' => (string) $resource->id,
            ],
        ];
    }

    protected function relationshipLinkageCollection(string $type, $collection): array
    {
        return [
            'data' => $collection->map(fn ($item) => [
                'type' => $type,
                'id' => (string) $item->id,
            ])->values()->all(),
        ];
    }
}
