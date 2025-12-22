<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            ->orderBy('created_at')
            ->get([
                'id',
                'company_id',
                'user_id',
                'role',
                'status',
                'invited_email',
                'invited_at',
                'accepted_at',
                'created_at',
                'updated_at',
            ])
            ->toArray();

        return ['items' => $items];
    }

    public function invite(User $actor, Company $company, string $email, string $role): CompanyMembership
    {
        $email = trim($email);
        if ($email === '') {
            throw new HttpException(422, 'VALIDATION_ERROR');
        }

        if (!in_array($role, ['owner', 'admin', 'member'], true)) {
            throw new HttpException(422, 'VALIDATION_ERROR');
        }

        $membership = null;

        DB::transaction(function () use (&$membership, $actor, $company, $email, $role): void {
            $now = now();
            $existing = CompanyMembership::query()
                ->where('company_id', $company->id)
                ->where('invited_email', $email)
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                throw new HttpException(409, 'ALREADY_INVITED');
            }

            $membership = CompanyMembership::create([
                // UUID se genera por modelo si no viene id (ver nota abajo)
                'company_id' => $company->id,
                'user_id' => null,
                'role' => $role,
                'status' => 'invited',
                'invited_email' => $email,
                'invited_at' => $now,
                'accepted_at' => null,
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
                    'invited_email' => $email,
                    'role' => $role,
                ]
            );
        });

        return $membership;
    }

    /**
     * Si el user ya existe, acepta cualquier invitación pendiente para su email.
     */
    public function acceptPendingInvitationsForEmail(User $user, string $email): void
    {
        $pending = CompanyMembership::query()
            ->where('invited_email', $email)
            ->where('status', 'invited')
            ->whereNull('deleted_at')
            ->get();

        if ($pending->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($user, $pending, $email): void {
            $now = now();
            foreach ($pending as $membership) {
                $membership->update([
                    'user_id' => $user->id,
                    'status' => 'active',
                    'accepted_at' => $now,
                    'updated_at' => $now,
                    'updated_by' => $user->id,
                ]);

                $this->auditLogService->record(
                    'membership.activate',
                    $user->id,
                    $membership->company_id,
                    'company_membership',
                    $membership->id,
                    'info',
                    [
                        'email' => $email,
                    ]
                );
            }
        });
    }

    /**
     * Si NO existe user, solo creamos uno si hay invitación pendiente.
     * Esto evita "registro público".
     */
    public function createUserFromInvitationAndAccept(string $email, string $password, Request $request): User
    {
        $invitedMembership = CompanyMembership::query()
            ->where('invited_email', $email)
            ->where('status', 'invited')
            ->whereNull('deleted_at')
            ->first();

        if (!$invitedMembership) {
            $this->auditLogService->record('auth.login_failed', null, null, 'user', null, 'security', [
                'email' => $email,
            ], $request);

            throw new HttpException(401, 'INVALID_CREDENTIALS');
        }

        $user = null;

        DB::transaction(function () use (&$user, $email, $password, $invitedMembership): void {
            $now = now();
            $user = User::create([
                // UUID se genera por modelo si no viene id (ver nota abajo)
                'name' => $email,
                'email' => $email,
                'password' => Hash::make($password),
                'status' => 'active',
                'created_at' => $now,
                'created_by' => null,
            ]);

            $invitedMembership->update([
                'user_id' => $user->id,
                'status' => 'active',
                'accepted_at' => $now,
                'updated_at' => $now,
                'updated_by' => $user->id,
            ]);

            $this->auditLogService->record(
                'membership.activate',
                $user->id,
                $invitedMembership->company_id,
                'company_membership',
                $invitedMembership->id,
                'info',
                [
                    'email' => $email,
                ]
            );
        });

        return $user;
    }
}
