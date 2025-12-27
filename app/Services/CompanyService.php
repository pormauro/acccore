<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CompanyService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    private function requireUser(?User $user): User
    {
        if (!$user || !$user->id) {
            throw new HttpException(401, 'UNAUTHENTICATED');
        }

        return $user;
    }

    public function listForUser(?User $user): array
    {
        $user = $this->requireUser($user);

        $items = Company::query()
            ->join('company_memberships', 'companies.id', '=', 'company_memberships.company_id')
            ->where('company_memberships.user_id', $user->id)
            ->where('company_memberships.status', 'active')
            ->whereNull('company_memberships.deleted_at')
            ->whereNull('companies.deleted_at')
            ->get([
                'companies.id',
                'companies.legal_name',
                'companies.trade_name',
                'companies.tax_id',
                'companies.status',
            ]);

        return ['items' => $items];
    }

    public function create(?User $user, array $data): array
    {
        $user = $this->requireUser($user);
        $company = null;
        $membership = null;

        DB::transaction(function () use (&$company, &$membership, $user, $data): void {
            $now = now();

            $company = Company::create([
                'id' => (string) Str::uuid(),
                'legal_name' => $data['legal_name'],
                'trade_name' => $data['trade_name'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'status' => 'active',
                'created_at' => $now,
                'created_by' => $user->id,
                'updated_at' => $now,
                'updated_by' => $user->id,
            ]);

            $membership = CompanyMembership::create([
                'id' => (string) Str::uuid(),
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'status' => 'active',
                'created_at' => $now,
                'created_by' => $user->id,
                'updated_at' => $now,
                'updated_by' => $user->id,
                'accepted_at' => $now,
            ]);

            $this->auditLogService->record(
                'company.create',
                $user->id,
                $company->id,
                'company',
                $company->id,
                'info'
            );
        });

        return [
            'company' => $company,
            'membership' => $membership,
        ];
    }

    public function get(?User $user, string $companyId): Company
    {
        $user = $this->requireUser($user);

        $company = Company::query()
            ->where('id', $companyId)
            ->whereNull('deleted_at')
            ->first();

        if (!$company) {
            throw new HttpException(404, 'NOT_FOUND');
        }

        return $company;
    }

    public function update(?User $user, Company $company, array $data): Company
    {
        $user = $this->requireUser($user);

        DB::transaction(function () use ($user, $company, $data): void {
            $company->fill($data);
            $company->updated_at = now();
            $company->updated_by = $user->id;
            $company->save();

            $this->auditLogService->record(
                'company.update',
                $user->id,
                $company->id,
                'company',
                $company->id,
                'info'
            );
        });

        return $company->fresh();
    }

    public function softDelete(?User $user, Company $company): void
    {
        $user = $this->requireUser($user);

        DB::transaction(function () use ($user, $company): void {
            $now = now();

            $company->update([
                'deleted_at' => $now,
                'deleted_by' => $user->id,
                'updated_at' => $now,
                'updated_by' => $user->id,
            ]);

            CompanyMembership::query()
                ->where('company_id', $company->id)
                ->whereNull('deleted_at')
                ->update([
                    'status' => 'suspended',
                    'updated_at' => $now,
                    'updated_by' => $user->id,
                ]);

            $this->auditLogService->record(
                'company.delete',
                $user->id,
                $company->id,
                'company',
                $company->id,
                'info'
            );
        });
    }
}
