<?php

namespace App\Services;

use App\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function login(Request $request): array
    {
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        $user = User::query()
            ->where('email', $email)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            $invitedMembership = CompanyMembership::query()
                ->where('invited_email', $email)
                ->where('status', 'invited')
                ->whereNull('deleted_at')
                ->first();

            if (!$invitedMembership) {
                $this->auditLogService->record('auth.login_failed', null, null, 'user', null, 'security', [
                    'email' => $email,
                ]);
                throw new HttpException(401, 'INVALID_CREDENTIALS');
            }

            $user = null;

            DB::transaction(function () use (&$user, $email, $password, $invitedMembership): void {
                $now = now();

                $user = User::create([
                    'id' => (string) Str::uuid(),
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
        } else {
            if (!Hash::check($password, $user->password)) {
                $this->auditLogService->record('auth.login_failed', $user->id, null, 'user', $user->id, 'security', [
                    'email' => $email,
                ]);
                throw new HttpException(401, 'INVALID_CREDENTIALS');
            }

            if ($user->status === 'suspended') {
                $invitedMembership = CompanyMembership::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'invited')
                    ->whereNull('deleted_at')
                    ->first();

                if (!$invitedMembership) {
                    $this->auditLogService->record('auth.login_failed', $user->id, null, 'user', $user->id, 'security', [
                        'email' => $email,
                    ]);
                    throw new HttpException(403, 'USER_SUSPENDED');
                }
            }

            DB::transaction(function () use ($user, $email): void {
                $now = now();

                $invitedMemberships = CompanyMembership::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'invited')
                    ->whereNull('deleted_at')
                    ->get();

                foreach ($invitedMemberships as $membership) {
                    $membership->update([
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

                if ($user->status !== 'active') {
                    $user->update([
                        'status' => 'active',
                        'updated_at' => $now,
                        'updated_by' => $user->id,
                    ]);
                }
            });
        }

        $token = $user->createToken('auth')->plainTextToken;

        $memberships = CompanyMembership::query()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->get([
                'id',
                'company_id',
                'role',
                'status',
                'invited_email',
            ]);

        $this->auditLogService->record('auth.login', $user->id, null, 'user', $user->id, 'info');

        return [
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'status']),
            'memberships' => $memberships,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function me(User $user): array
    {
        $memberships = CompanyMembership::query()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->get([
                'id',
                'company_id',
                'role',
                'status',
                'invited_email',
            ]);

        return [
            'user' => $user->only(['id', 'name', 'email', 'status']),
            'memberships' => $memberships,
        ];
    }
}
