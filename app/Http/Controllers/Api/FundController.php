<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFundRequest;
use App\Http\Requests\UpdateFundRequest;
use App\Http\Resources\FundResource;
use App\Models\Fund;
use App\Services\FundService;
use Illuminate\Http\Request;

class FundController extends Controller
{
    public function __construct(
        private FundService $fundService,
    ) {}

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

        return FundResource::collection($query->paginate(15));
    }

    public function store(StoreFundRequest $request)
    {
        $fund = $this->fundService->create(
            $request->only('name', 'start_year', 'fund_manager_id'),
            $request->input('aliases', []),
            $request->input('company_ids', []),
        );

        return (new FundResource($fund))->response()->setStatusCode(201);
    }

    public function show(Fund $fund)
    {
        return new FundResource($fund->load('manager', 'aliases', 'companies'));
    }

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

    public function destroy(Fund $fund)
    {
        $fund->delete();

        return response()->noContent();
    }
}
