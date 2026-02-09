<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\JsonApiCollection;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index()
    {
        return new JsonApiCollection(Company::paginate(15), CompanyResource::class);
    }

    public function store(StoreCompanyRequest $request)
    {
        $company = Company::create($request->validated());

        return (new CompanyResource($company))->response()->setStatusCode(201);
    }

    public function show(Company $company)
    {
        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->update($request->validated());

        return new CompanyResource($company);
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return response()->noContent();
    }
}
