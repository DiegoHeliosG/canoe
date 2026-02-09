<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFundManagerRequest;
use App\Http\Requests\UpdateFundManagerRequest;
use App\Http\Resources\FundManagerResource;
use App\Http\Resources\JsonApiCollection;
use App\Models\FundManager;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Fund Managers', description: 'Fund manager operations')]
class FundManagerController extends Controller
{
    #[OA\Get(
        path: '/api/fund-managers',
        summary: 'List fund managers',
        description: 'Returns a paginated list of fund managers with their fund counts.',
        tags: ['Fund Managers'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of fund managers',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/FundManagerResource')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
        ],
    )]
    public function index()
    {
        return new JsonApiCollection(
            FundManager::withCount('funds')->paginate(15),
            FundManagerResource::class,
        );
    }

    #[OA\Post(
        path: '/api/fund-managers',
        summary: 'Create a fund manager',
        description: 'Creates a new fund manager.',
        tags: ['Fund Managers'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Blackstone Group'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Fund manager created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/FundManagerResource'),
                    ],
                ),
            ),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function store(StoreFundManagerRequest $request)
    {
        $manager = FundManager::create($request->validated());

        return (new FundManagerResource($manager))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/fund-managers/{id}',
        summary: 'Get a fund manager',
        description: 'Returns a single fund manager with its fund count.',
        tags: ['Fund Managers'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Fund Manager ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Fund manager details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/FundManagerResource'),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Fund manager not found'),
        ],
    )]
    public function show(FundManager $fundManager)
    {
        return new FundManagerResource($fundManager->loadCount('funds'));
    }

    #[OA\Put(
        path: '/api/fund-managers/{id}',
        summary: 'Update a fund manager',
        description: 'Updates a fund manager\'s attributes.',
        tags: ['Fund Managers'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Fund Manager ID', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Blackstone Inc'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Fund manager updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/FundManagerResource'),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Fund manager not found'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function update(UpdateFundManagerRequest $request, FundManager $fundManager)
    {
        $fundManager->update($request->validated());

        return new FundManagerResource($fundManager);
    }

    #[OA\Delete(
        path: '/api/fund-managers/{id}',
        summary: 'Delete a fund manager',
        description: 'Soft-deletes a fund manager. Fails with 409 if the manager still has funds.',
        tags: ['Fund Managers'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Fund Manager ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Fund manager deleted'),
            new OA\Response(response: 404, description: 'Fund manager not found'),
            new OA\Response(
                response: 409,
                description: 'Fund manager has existing funds',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Cannot delete fund manager with existing funds. Remove or reassign funds first.'),
                    ],
                ),
            ),
        ],
    )]
    public function destroy(FundManager $fundManager)
    {
        if ($fundManager->funds()->exists()) {
            return response()->json([
                'message' => 'Cannot delete fund manager with existing funds. Remove or reassign funds first.',
            ], 409);
        }

        $fundManager->delete();

        return response()->noContent();
    }
}
