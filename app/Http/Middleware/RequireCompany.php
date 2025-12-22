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
        $companyId = (string) $request->header('X-Company-Id');
        if ($companyId === '') {
            return response()->json(['error' => 'MISSING_COMPANY'], 400);
        }

        $company = Company::query()
            ->where('id', $companyId)
            ->whereNull('deleted_at')
            ->first();

        if (!$company) {
            return response()->json(['error' => 'NOT_FOUND'], 404);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'UNAUTHENTICATED'], 401);
        }

        $membership = CompanyMembership::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$membership || $membership->status !== 'active') {
            return response()->json(['error' => 'FORBIDDEN'], 403);
        }

        // Scope estable para el resto del request
        $request->attributes->set('company_id', $companyId);
        $request->attributes->set('company', $company);
        $request->attributes->set('membership', $membership);

        return $next($request);
    }
}
