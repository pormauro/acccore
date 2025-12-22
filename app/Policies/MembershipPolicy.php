<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;

class MembershipPolicy
{
    public function viewAny(User $user, Company $company): bool
    {
        return $this->hasManagementRole($user, $company->id);
    }

    public function create(User $user, Company $company): bool
    {
        return $this->hasManagementRole($user, $company->id);
    }

    public function update(User $user, CompanyMembership $membership): bool
    {
        return $this->hasManagementRole($user, $membership->company_id);
    }

    public function delete(User $user, CompanyMembership $membership): bool
    {
        return $this->hasManagementRole($user, $membership->company_id);
    }

    private function hasManagementRole(User $user, string $companyId): bool
    {
        return CompanyMembership::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->exists();
    }
}
