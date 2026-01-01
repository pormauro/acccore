<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly MembershipService $membershipService
    ) {
    }

    public function login(Request $request): array
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = trim($credentials['email']);
        $password = $credentials['password'];

        // 1) Intento login normal
        $user = User::query()
            ->where('email', $email)
            ->whereNull('deleted_at')
            ->first();

        if ($user) {
            if (!Auth::attempt([
                'email' => $email,
                'password' => $password,
                'deleted_at' => null,
            ])) {
                $this->auditLogService->record('auth.login_failed', $user->id, null, 'user', $user->id, 'security', [
                    'email' => $email,
                ], $request);

                throw new HttpException(401, 'INVALID_CREDENTIALS');
            }

            $user = Auth::user() ?? $user;

            // Si existe invitación pendiente para este email, la acepta vía MembershipService (no acá)
            $this->membershipService->acceptPendingInvitationsForEmail($user, $email);

            $this->auditLogService->record('auth.login_success', $user->id, null, 'user', $user->id, 'info', [
                'email' => $email,
            ], $request);

            // En FASE 1 vos definís si usás Sanctum o JWT.
            // Ahora: Sanctum
            $token = $user->createToken('api')->plainTextToken;

            return [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ];
        }

        // 2) Si NO existe user, solo permitimos “crear” si hay invitación previa
        $created = $this->membershipService->createUserFromInvitationAndAccept($email, $password, $request);
        $token = $created->createToken('api')->plainTextToken;

        return [
            'token' => $token,
            'user' => [
                'id' => $created->id,
                'name' => $created->name,
                'email' => $created->email,
            ],
        ];
    }

    public function refresh(Request $request): array
    {
        // Para Sanctum, refresh real no aplica como JWT.
        // MVP: devolvemos 501 para no mentir.
        throw new HttpException(501, 'NOT_IMPLEMENTED');
    }

    public function logout(?User $user): void
    {
        if (!$user) {
            return;
        }

        $user->tokens()->delete();
    }

    public function me(?User $user): array
    {
        if (!$user) {
            throw new HttpException(401, 'UNAUTHENTICATED');
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }
}
