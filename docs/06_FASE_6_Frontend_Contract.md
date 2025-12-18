# FASE 6 – Contrato Frontend

## Objetivo
Definir cómo la app consume el backend sin romper reglas.

## Alcance
- Permisos
- Estados
- Errores
- UX offline

## Tareas
1. Exponer abilities por usuario.
2. Definir errores estándar (403, 409, 423).
3. Definir estados locales: pending, synced, error.
4. Documentar comportamiento esperado.

## Reglas
- El frontend no decide contabilidad.
- El frontend no decide permisos.

## Prohibido
- Lógica contable en UI
