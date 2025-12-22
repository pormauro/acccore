<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Registra auditoría (NO se borra nunca). Siempre debe incluir company_id si aplica.
     */
    public function record(
        string $action,
        ?string $actorUserId,
        ?string $companyId,
        ?string $entityType,
        ?string $entityId,
        string $severity = 'info',
        array $metadata = [],
        ?Request $request = null
    ): void {
        $request = $request ?? request();

        AuditLog::create([
            'actor_user_id' => $actorUserId,
            'company_id' => $companyId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'severity' => $severity,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_id' => $request->header('X-Request-Id') ?: $request->header('Idempotency-Key'),
            'metadata_json' => empty($metadata) ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Ejemplo de lectura scopiada por company (si después exponés endpoint de auditoría).
     */
    public function listForCompany(string $companyId, int $limit = 100): array
    {
        $items = AuditLog::query()
            ->where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();

        return ['items' => $items];
    }
}
