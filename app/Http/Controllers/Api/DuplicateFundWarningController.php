<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DuplicateFundWarningResource;
use App\Http\Resources\JsonApiCollection;
use App\Models\DuplicateFundWarning;

class DuplicateFundWarningController extends Controller
{
    public function index()
    {
        $warnings = DuplicateFundWarning::with('fund.manager', 'fund.aliases', 'duplicateFund.aliases', 'fundManager')
            ->where('is_resolved', false)
            ->latest()
            ->paginate(15);

        return new JsonApiCollection($warnings, DuplicateFundWarningResource::class);
    }

    public function resolve(DuplicateFundWarning $duplicateFundWarning)
    {
        $duplicateFundWarning->update(['is_resolved' => true]);

        return new DuplicateFundWarningResource(
            $duplicateFundWarning->load('fund', 'duplicateFund', 'fundManager')
        );
    }
}
