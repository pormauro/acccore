<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Company;
use App\Models\CompanyMembership;

class MembershipPolicy
{
    public function view(User $user, Company $company): bool
    {
        return CompanyMembership::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function invite(User $user, Company $company): bool
    {
        return CompanyMembership::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();
    }

    public function update(User $user, Company $company, CompanyMembership $membership): bool
    {
        if ($membership->role === 'owner') {
            return $this->isOwner($user, $company);
        }

        return $this->isAdminOrOwner($user, $company);
    }

    public function delete(User $user, Company $company, CompanyMembership $membership): bool
    {
        if ($membership->role === 'owner') {
            return false;
        }

        return $this->isAdminOrOwner($user, $company);
    }

    private function isOwner(User $user, Company $company): bool
    {
        return CompanyMembership::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->where('status', 'active')
            ->exists();
    }

    private function isAdminOrOwner(User $user, Company $company): bool
    {
        return CompanyMembership::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->where('status', 'active')
            ->exists();
    }
}
