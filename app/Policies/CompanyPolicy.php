<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Company;
use App\Models\CompanyMembership;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return CompanyMembership::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Company $company): bool
    {
        return CompanyMembership::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();
    }

    public function delete(User $user, Company $company): bool
    {
        return CompanyMembership::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->where('status', 'active')
            ->exists();
    }
}
