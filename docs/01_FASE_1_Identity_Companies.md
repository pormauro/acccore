# FASE 1 – Identidad, Empresas y Membresías

## Objetivo
Definir autenticación, multiempresa y roles.

## Alcance
- Usuarios
- Empresas
- Membresías
- Roles

## Tareas
1. Login / refresh / logout (JWT).
2. CRUD Companies.
3. CRUD CompanyMemberships.
4. Middleware RequireCompany.
5. Policies por rol (owner, admin, employee).

## Reglas
- Toda request (excepto auth) requiere X-Company-Id.
- Sin membresía activa no se accede a nada.

## Prohibido
- Contabilidad
- Jobs
- Documentos
