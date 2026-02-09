<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\JsonApiCollection;
use App\Models\Company;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Companies', description: 'Company management operations')]
class CompanyController extends Controller
{
    #[OA\Get(
        path: '/api/companies',
        summary: 'List companies',
        description: 'Returns a paginated list of companies.',
        tags: ['Companies'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of companies',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CompanyResource')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
        ],
    )]
    public function index()
    {
        return new JsonApiCollection(Company::paginate(15), CompanyResource::class);
    }

    #[OA\Post(
        path: '/api/companies',
        summary: 'Create a company',
        description: 'Creates a new company.',
        tags: ['Companies'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Acme Corp'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Company created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/CompanyResource'),
                    ],
                ),
            ),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function store(StoreCompanyRequest $request)
    {
        $company = Company::create($request->validated());

        return (new CompanyResource($company))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/companies/{id}',
        summary: 'Get a company',
        description: 'Returns a single company.',
        tags: ['Companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Company ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Company details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/CompanyResource'),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Company not found'),
        ],
    )]
    public function show(Company $company)
    {
        return new CompanyResource($company);
    }

    #[OA\Put(
        path: '/api/companies/{id}',
        summary: 'Update a company',
        description: 'Updates a company\'s attributes.',
        tags: ['Companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Company ID', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Acme Inc'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Company updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/CompanyResource'),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Company not found'),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->update($request->validated());

        return new CompanyResource($company);
    }

    #[OA\Delete(
        path: '/api/companies/{id}',
        summary: 'Delete a company',
        description: 'Soft-deletes a company.',
        tags: ['Companies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Company ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Company deleted'),
            new OA\Response(response: 404, description: 'Company not found'),
        ],
    )]
    public function destroy(Company $company)
    {
        $company->delete();

        return response()->noContent();
    }
}
