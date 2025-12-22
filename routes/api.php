<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\MembershipController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // FASE 0
    Route::get('health', fn () => response()->json(['status' => 'ok'], 200));

    // Auth (NO hay register público)
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // Companies (list/create sin require.company todavía, porque todavía no elegiste una company)
        Route::get('companies', [CompanyController::class, 'index']);
        Route::post('companies', [CompanyController::class, 'store']);

        // Todo lo que sea “dentro de una company” requiere X-Company-Id
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
