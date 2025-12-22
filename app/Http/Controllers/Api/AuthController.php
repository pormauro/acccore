<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function login(Request $request): JsonResponse
    {
        $payload = $this->authService->login($request);

        return response()->json($payload, 200);
    }

    public function refresh(Request $request): JsonResponse
    {
        $payload = $this->authService->refresh($request);

        return response()->json($payload, 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['status' => 'ok'], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $payload = $this->authService->me($request->user());

        return response()->json($payload, 200);
    }
}
