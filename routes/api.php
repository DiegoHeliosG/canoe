<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DuplicateFundWarningController;
use App\Http\Controllers\Api\FundController;
use App\Http\Controllers\Api\FundManagerController;
use Illuminate\Support\Facades\Route;

Route::apiResource('funds', FundController::class);
Route::apiResource('fund-managers', FundManagerController::class);
Route::apiResource('companies', CompanyController::class);

Route::get('duplicate-warnings', [DuplicateFundWarningController::class, 'index']);
Route::patch('duplicate-warnings/{duplicateFundWarning}/resolve', [DuplicateFundWarningController::class, 'resolve']);
