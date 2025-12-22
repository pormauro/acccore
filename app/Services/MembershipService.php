<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MembershipService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function list(User $user, Company $company): array
    {
        $items = CompanyMembership::query()
            ->where('company_id', $company->id)
            ->whereNull('deleted_at')
            ->get([
                'id',
                'company_id',
                'user_id',
                'role',
                'status',
                'invited_email',
                'invited_at',
                'accepted_at',
            ]);

        return ['items' => $items];
    }

    public function invite(User $actor, Company $company, string $email, string $role): CompanyMembership
    {
        $membership = null;

        DB::transaction(function () use (&$membership, $actor, $company, $email, $role): void {
            $now = now();
            $actorMembership = $this->getActorMembership($actor, $company);

            if ($role === 'owner' && $actorMembership->role !== 'owner') {
                throw new HttpException(403, 'FORBIDDEN');
            }

            $user = User::query()
                ->where('email', $email)
                ->whereNull('deleted_at')
                ->first();

            if (!$user) {
                $user = User::create([
                    'id' => (string) Str::uuid(),
                    'name' => $email,
                    'email' => $email,
                    'password' => Hash::make(Str::random(32)),
                    'status' => 'suspended',
                    'created_at' => $now,
                    'created_by' => $actor->id,
                ]);
            }

            $existingMembership = CompanyMembership::query()
                ->where('company_id', $company->id)
                ->whereNull('deleted_at')
                ->where(function ($query) use ($user, $email): void {
                    $query->where('user_id', $user->id)
                        ->orWhere('invited_email', $email);
                })
                ->first();

            if ($existingMembership) {
                throw new HttpException(409, 'MEMBERSHIP_ALREADY_EXISTS');
            }

            $membership = CompanyMembership::create([
                'id' => (string) Str::uuid(),
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role' => $role,
                'status' => 'invited',
                'invited_email' => $email,
                'invited_at' => $now,
                'created_at' => $now,
                'created_by' => $actor->id,
            ]);

            $this->auditLogService->record(
                'membership.invite',
                $actor->id,
                $company->id,
                'company_membership',
                $membership->id,
                'info',
                [
                    'email' => $email,
                ]
            );
        });

        return $membership;
    }

    public function update(User $actor, Company $company, CompanyMembership $membership, array $data): CompanyMembership
    {
        DB::transaction(function () use ($actor, $company, $membership, $data): void {
            $now = now();
            $actorMembership = $this->getActorMembership($actor, $company);

            if (isset($data['role']) && $data['role'] === 'owner' && $actorMembership->role !== 'owner') {
                throw new HttpException(403, 'FORBIDDEN');
            }

            if (isset($data['role']) && $data['role'] !== $membership->role) {
                $this->ensureOwnerRemains($company, $membership, $data['role']);
                $membership->role = $data['role'];
            }

            if (isset($data['status']) && $data['status'] !== $membership->status) {
                if (!in_array($data['status'], ['active', 'suspended', 'invited'], true)) {
                    throw new HttpException(422, 'INVALID_STATUS');
                }
                if ($membership->role === 'owner' && $data['status'] !== 'active') {
                    $this->ensureOwnerRemains($company, $membership, $membership->role);
                }
                $membership->status = $data['status'];
            }

            $membership->updated_at = $now;
            $membership->updated_by = $actor->id;
            $membership->save();

            if (isset($data['role'])) {
                $this->auditLogService->record(
                    'membership.role_change',
                    $actor->id,
                    $company->id,
                    'company_membership',
                    $membership->id,
                    'info'
                );
            }

            if (isset($data['status']) && $data['status'] === 'suspended') {
                $this->auditLogService->record(
                    'membership.suspend',
                    $actor->id,
                    $company->id,
                    'company_membership',
                    $membership->id,
                    'info'
                );
            }
        });

        return $membership->fresh();
    }

    public function remove(User $actor, Company $company, CompanyMembership $membership): void
    {
        DB::transaction(function () use ($actor, $company, $membership): void {
            $this->ensureOwnerRemains($company, $membership, $membership->role, true);

            $now = now();

            $membership->update([
                'deleted_at' => $now,
                'deleted_by' => $actor->id,
                'updated_at' => $now,
                'updated_by' => $actor->id,
                'status' => 'suspended',
            ]);
        });
    }

    public function findMembership(Company $company, string $membershipId): CompanyMembership
    {
        $membership = CompanyMembership::query()
            ->where('company_id', $company->id)
            ->where('id', $membershipId)
            ->whereNull('deleted_at')
            ->first();

        if (!$membership) {
            throw new HttpException(404, 'NOT_FOUND');
        }

        return $membership;
    }

    private function getActorMembership(User $actor, Company $company): CompanyMembership
    {
        $membership = CompanyMembership::query()
            ->where('company_id', $company->id)
            ->where('user_id', $actor->id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->first();

        if (!$membership) {
            throw new HttpException(403, 'FORBIDDEN');
        }

        return $membership;
    }

    private function ensureOwnerRemains(Company $company, CompanyMembership $membership, string $newRole, bool $isDelete = false): void
    {
        if ($membership->role !== 'owner') {
            return;
        }

        if ($newRole === 'owner' && !$isDelete) {
            return;
        }

        $ownerCount = CompanyMembership::query()
            ->where('company_id', $company->id)
            ->where('role', 'owner')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->count();

        if ($ownerCount <= 1) {
            throw new HttpException(409, 'LAST_OWNER');
        }
    }
}
