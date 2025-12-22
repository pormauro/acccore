<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\MembershipController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'time' => now()->toISOString(),
    ]);
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('companies', [CompanyController::class, 'index']);
        Route::post('companies', [CompanyController::class, 'store']);

        Route::middleware('require.company')->group(function () {
            Route::get('companies/{companyId}', [CompanyController::class, 'show']);
            Route::patch('companies/{companyId}', [CompanyController::class, 'update']);
            Route::delete('companies/{companyId}', [CompanyController::class, 'destroy']);

            Route::get('companies/{companyId}/memberships', [MembershipController::class, 'index']);
            Route::post('companies/{companyId}/memberships', [MembershipController::class, 'store']);
            Route::patch('companies/{companyId}/memberships/{membershipId}', [MembershipController::class, 'update']);
            Route::delete('companies/{companyId}/memberships/{membershipId}', [MembershipController::class, 'destroy']);
        });
    });
});
