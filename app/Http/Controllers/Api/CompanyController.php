<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $companyService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'UNAUTHENTICATED');
        }

        $this->authorize('viewAny', Company::class);

        $payload = $this->companyService->listForUser($user);

        return response()->json($payload, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'UNAUTHENTICATED');
        }

        $this->authorize('create', Company::class);

        $data = $request->validate([
            'legal_name' => ['required', 'string', 'max:200'],
            'trade_name' => ['nullable', 'string', 'max:200'],
            'tax_id' => ['nullable', 'string', 'max:50'],
        ]);

        $payload = $this->companyService->create($user, $data);

        return response()->json($payload, 201);
    }

    public function show(Request $request, string $companyId): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'UNAUTHENTICATED');
        }

        $company = $this->companyService->get($user, $companyId);
        $this->authorize('view', $company);

        return response()->json(['company' => $company], 200);
    }

    public function update(Request $request, string $companyId): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'UNAUTHENTICATED');
        }

        $company = $this->companyService->get($user, $companyId);
        $this->authorize('update', $company);

        $data = $request->validate([
            'legal_name' => ['sometimes', 'string', 'max:200'],
            'trade_name' => ['sometimes', 'nullable', 'string', 'max:200'],
            'tax_id' => ['sometimes', 'nullable', 'string', 'max:50'],
            'status' => ['sometimes', 'in:active,suspended'],
        ]);

        $company = $this->companyService->update($user, $company, $data);

        return response()->json(['company' => $company], 200);
    }

    public function destroy(Request $request, string $companyId): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'UNAUTHENTICATED');
        }

        $company = $this->companyService->get($user, $companyId);
        $this->authorize('delete', $company);

        $this->companyService->softDelete($user, $company);

        return response()->json(['status' => 'ok'], 200);
    }
}
