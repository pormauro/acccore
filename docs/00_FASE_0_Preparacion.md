# FASE 0 – Preparación e Infraestructura

## Objetivo
Preparar el entorno técnico y la base contable antes de escribir cualquier lógica de negocio.

## Alcance
- Laravel limpio
- PostgreSQL operativo
- JWT instalado
- DDL completo ejecutado
- Triggers y auditoría funcionando

## Tareas
1. Crear proyecto Laravel.
2. Configurar conexión PostgreSQL.
3. Instalar JWT (sin endpoints aún).
4. Ejecutar DDL completo (tablas, FKs, triggers).
5. Crear endpoint GET /api/v1/health.
6. Verificar manualmente:
   - No se puede borrar contabilidad.
   - No se puede escribir en períodos cerrados.
   - audit_log registra todo.

## Prohibido
- Controllers de negocio
- Migrations de dominio
- Sync
- UI
- Facturación
