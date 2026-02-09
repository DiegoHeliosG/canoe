<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JsonApiCollection extends ResourceCollection
{
    public function __construct($resource, string $collects)
    {
        $this->collects = $collects;
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return $this->collection->map(fn ($resource) => $resource->toArray($request))->all();
    }

    public function with(Request $request): array
    {
        $included = [];
        $seen = [];

        foreach ($this->collection as $resource) {
            if (method_exists($resource, 'resourceIncluded')) {
                foreach ($resource->resourceIncluded($request) as $item) {
                    $key = $item['type'] . ':' . $item['id'];
                    if (!isset($seen[$key])) {
                        $seen[$key] = true;
                        $included[] = $item;
                    }
                }
            }
        }

        $result = [];

        if ($included) {
            $result['included'] = $included;
        }

        return $result;
    }

    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'meta' => [
                'current_page' => $paginated['current_page'],
                'last_page' => $paginated['last_page'],
                'per_page' => $paginated['per_page'],
                'total' => $paginated['total'],
            ],
        ];
    }
}
