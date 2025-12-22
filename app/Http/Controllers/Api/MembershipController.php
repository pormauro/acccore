<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyMembership;
use App\Services\CompanyService;
use App\Services\MembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService,
        private readonly MembershipService $membershipService
    ) {
    }

    public function index(Request $request, string $companyId): JsonResponse
    {
        $company = $this->companyService->get($request->user(), $companyId);
        $this->authorize('viewAny', [CompanyMembership::class, $company]);

        $payload = $this->membershipService->list($request->user(), $company);

        return response()->json($payload, 200);
    }

    public function store(Request $request, string $companyId): JsonResponse
    {
        $company = $this->companyService->get($request->user(), $companyId);
        $this->authorize('create', [CompanyMembership::class, $company]);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'role' => ['required', 'in:owner,admin,member'],
        ]);

        $membership = $this->membershipService->invite(
            $request->user(),
            $company,
            $data['email'],
            $data['role']
        );

        return response()->json(['membership' => $membership], 201);
    }

    public function update(Request $request, string $companyId, string $membershipId): JsonResponse
    {
        $company = $this->companyService->get($request->user(), $companyId);
        $membership = $this->membershipService->findMembership($company, $membershipId);
        $this->authorize('update', $membership);

        $data = $request->validate([
            'role' => ['sometimes', 'in:owner,admin,member'],
            'status' => ['sometimes', 'in:active,suspended,invited'],
        ]);

        $membership = $this->membershipService->update($request->user(), $company, $membership, $data);

        return response()->json(['membership' => $membership], 200);
    }

    public function destroy(Request $request, string $companyId, string $membershipId): JsonResponse
    {
        $company = $this->companyService->get($request->user(), $companyId);
        $membership = $this->membershipService->findMembership($company, $membershipId);
        $this->authorize('delete', $membership);

        $this->membershipService->remove($request->user(), $company, $membership);

        return response()->json(['status' => 'ok'], 200);
    }
}
