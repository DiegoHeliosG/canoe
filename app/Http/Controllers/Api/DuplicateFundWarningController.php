<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DuplicateFundWarningResource;
use App\Http\Resources\JsonApiCollection;
use App\Models\DuplicateFundWarning;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Duplicate Warnings', description: 'Duplicate fund warning detection and resolution')]
class DuplicateFundWarningController extends Controller
{
    #[OA\Get(
        path: '/api/duplicate-warnings',
        summary: 'List unresolved duplicate warnings',
        description: 'Returns a paginated list of unresolved duplicate fund warnings, ordered by most recent first.',
        tags: ['Duplicate Warnings'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of unresolved duplicate warnings',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/DuplicateWarningResource')),
                        new OA\Property(property: 'included', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
        ],
    )]
    public function index()
    {
        $warnings = DuplicateFundWarning::with('fund.manager', 'fund.aliases', 'duplicateFund.aliases', 'fundManager')
            ->where('is_resolved', false)
            ->latest()
            ->paginate(15);

        return new JsonApiCollection($warnings, DuplicateFundWarningResource::class);
    }

    #[OA\Patch(
        path: '/api/duplicate-warnings/{id}/resolve',
        summary: 'Resolve a duplicate warning',
        description: 'Marks a duplicate fund warning as resolved.',
        tags: ['Duplicate Warnings'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Duplicate Warning ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Warning resolved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/DuplicateWarningResource'),
                        new OA\Property(property: 'included', type: 'array', items: new OA\Items(type: 'object')),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Warning not found'),
        ],
    )]
    public function resolve(DuplicateFundWarning $duplicateFundWarning)
    {
        $duplicateFundWarning->update(['is_resolved' => true]);

        return new DuplicateFundWarningResource(
            $duplicateFundWarning->load('fund', 'duplicateFund', 'fundManager')
        );
    }
}
