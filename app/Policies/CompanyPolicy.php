<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return CompanyMembership::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->exists();
    }

    public function view(User $user, Company $company): bool
    {
        return CompanyMembership::query()
            ->where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Company $company): bool
    {
        return CompanyMembership::query()
            ->where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->exists();
    }

    public function delete(User $user, Company $company): bool
    {
        return CompanyMembership::query()
            ->where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->exists();
    }
}
