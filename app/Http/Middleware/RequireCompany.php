<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\CompanyMembership;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $request->header('X-Company-Id');

        if (!$companyId) {
            return response()->json(['error' => 'MISSING_COMPANY_ID'], 400);
        }

        $user = $request->user();

        $membership = CompanyMembership::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->first();

        if (!$membership) {
            return response()->json(['error' => 'FORBIDDEN'], 403);
        }

        $company = Company::query()
            ->where('id', $companyId)
            ->whereNull('deleted_at')
            ->first();

        if (!$company) {
            return response()->json(['error' => 'NOT_FOUND'], 404);
        }

        $request->attributes->set('company_id', $companyId);
        $request->attributes->set('company', $company);
        $request->attributes->set('membership', $membership);

        return $next($request);
    }
}
