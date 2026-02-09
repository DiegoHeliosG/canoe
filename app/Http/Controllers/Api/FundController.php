<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFundRequest;
use App\Http\Requests\UpdateFundRequest;
use App\Http\Resources\FundResource;
use App\Http\Resources\JsonApiCollection;
use App\Models\Fund;
use App\Services\FundService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Funds', description: 'Fund management operations')]
class FundController extends Controller
{
    public function __construct(
        private FundService $fundService,
    ) {}

    #[OA\Get(
        path: '/api/funds',
        summary: 'List funds',
        description: 'Returns a paginated list of funds with optional filters. Supports filtering by name, fund manager, start year, and company.',
        tags: ['Funds'],
        parameters: [
            new OA\Parameter(name: 'name', in: 'query', required: false, description: 'Filter by fund name (partial match)', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'fund_manager_id', in: 'query', required: false, description: 'Filter by fund manager ID', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'year', in: 'query', required: false, description: 'Filter by start year', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'company_id', in: 'query', required: false, description: 'Filter by associated company ID', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of funds',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/FundResource')),
                        new OA\Property(property: 'included', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
        ],
    )]
    public function index(Request $request)
    {
        $query = Fund::with('manager', 'aliases', 'companies');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('fund_manager_id')) {
            $query->where('fund_manager_id', $request->input('fund_manager_id'));
        }

        if ($request->filled('year')) {
            $query->where('start_year', $request->input('year'));
        }

        if ($request->filled('company_id')) {
            $query->whereHas('companies', function ($q) use ($request) {
                $q->where('companies.id', $request->input('company_id'));
            });
        }

        return new JsonApiCollection($query->paginate(15), FundResource::class);
    }

    #[OA\Post(
        path: '/api/funds',
        summary: 'Create a fund',
        description: 'Creates a new fund with optional aliases and company associations. Triggers duplicate detection if the name or aliases match an existing fund under the same manager.',
        tags: ['Funds'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'start_year', 'fund_manager_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Growth Fund I'),
                    new OA\Property(property: 'start_year', type: 'integer', example: 2024),
                    new OA\Property(property: 'fund_manager_id', type: 'integer', example: 1),
                    new OA\Property(property: 'aliases', type: 'array', items: new OA\Items(type: 'string'), example: ['GF1', 'Growth One']),
                    new OA\Property(property: 'company_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Fund created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/FundResource'),
                        new OA\Property(property: 'included', type: 'array', items: new OA\Items(type: 'object')),
                    ],
                ),
            ),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function store(StoreFundRequest $request)
    {
        $fund = $this->fundService->create(
            $request->only('name', 'start_year', 'fund_manager_id'),
            $request->input('aliases', []),
            $request->input('company_ids', []),
        );

        return (new FundResource($fund))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/funds/{id}',
        summary: 'Get a fund',
        description: 'Returns a single fund with its manager, aliases, and company relationships.',
        tags: ['Funds'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Fund ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Fund details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/FundResource'),
                        new OA\Property(property: 'included', type: 'array', items: new OA\Items(type: 'object')),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Fund not found'),
        ],
    )]
    public function show(Fund $fund)
    {
        return new FundResource($fund->load('manager', 'aliases', 'companies'));
    }

    #[OA\Put(
        path: '/api/funds/{id}',
        summary: 'Update a fund',
        description: 'Updates a fund and optionally replaces its aliases and company associations.',
        tags: ['Funds'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Fund ID', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Growth Fund II'),
                    new OA\Property(property: 'start_year', type: 'integer', example: 2025),
                    new OA\Property(property: 'fund_manager_id', type: 'integer', example: 1),
                    new OA\Property(property: 'aliases', type: 'array', items: new OA\Items(type: 'string'), example: ['GF2']),
                    new OA\Property(property: 'company_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 3]),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Fund updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/FundResource'),
                        new OA\Property(property: 'included', type: 'array', items: new OA\Items(type: 'object')),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Fund not found'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function update(UpdateFundRequest $request, Fund $fund)
    {
        $fund = $this->fundService->update(
            $fund,
            $request->only('name', 'start_year', 'fund_manager_id'),
            $request->has('aliases') ? $request->input('aliases') : null,
            $request->has('company_ids') ? $request->input('company_ids') : null,
        );

        return new FundResource($fund);
    }

    #[OA\Delete(
        path: '/api/funds/{id}',
        summary: 'Delete a fund',
        description: 'Soft-deletes a fund.',
        tags: ['Funds'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Fund ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Fund deleted'),
            new OA\Response(response: 404, description: 'Fund not found'),
        ],
    )]
    public function destroy(Fund $fund)
    {
        $fund->delete();

        return response()->noContent();
    }
}
