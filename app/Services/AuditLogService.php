<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(
        string $action,
        ?string $actorUserId,
        ?string $companyId,
        ?string $entityType,
        ?string $entityId,
        string $severity = 'info',
        array $metadata = []
    ): void {
        $request = app(Request::class);

        AuditLog::create([
            'occurred_at' => now(),
            'actor_user_id' => $actorUserId,
            'company_id' => $companyId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'severity' => $severity,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_id' => $request->header('X-Request-Id') ?? $request->header('Idempotency-Key'),
            'metadata_json' => empty($metadata) ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
        ]);
    }
}
