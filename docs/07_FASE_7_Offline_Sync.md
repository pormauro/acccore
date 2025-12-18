# FASE 7 – Sync Offline

## Objetivo
Permitir trabajo offline sin corrupción de datos.

## Alcance
- Outbox
- Batch sync
- Idempotencia
- Conflictos

## Tareas
1. Tabla idempotency_keys.
2. Tabla sync_changes.
3. Endpoint POST /sync/batch.
4. Sync token opaco.
5. Resolución de conflictos.

## Reglas
- Cada operación es idempotente.
- El sync no bypassa permisos.

## Prohibido
- Implementar offline antes de que online funcione perfecto
