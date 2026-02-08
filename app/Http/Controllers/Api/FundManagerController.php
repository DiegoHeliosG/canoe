<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFundManagerRequest;
use App\Http\Requests\UpdateFundManagerRequest;
use App\Http\Resources\FundManagerResource;
use App\Models\FundManager;

class FundManagerController extends Controller
{
    public function index()
    {
        return FundManagerResource::collection(
            FundManager::withCount('funds')->paginate(15)
        );
    }

    public function store(StoreFundManagerRequest $request)
    {
        $manager = FundManager::create($request->validated());

        return (new FundManagerResource($manager))->response()->setStatusCode(201);
    }

    public function show(FundManager $fundManager)
    {
        return new FundManagerResource($fundManager->loadCount('funds'));
    }

    public function update(UpdateFundManagerRequest $request, FundManager $fundManager)
    {
        $fundManager->update($request->validated());

        return new FundManagerResource($fundManager);
    }

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
