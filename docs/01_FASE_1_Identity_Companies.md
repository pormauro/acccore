# FASE 1 – Identity + Companies (MySQL/MariaDB)
## Objetivo
Definir identidad, empresas, membresías y permisos base.
Todo request (excepto auth) requiere:
- Authorization: Bearer <token>
- X-Company-Id: <uuid>
## Stack
- Laravel 10.x
- Auth: Laravel Sanctum (tokens)
- DB: MySQL/MariaDB (InnoDB)
- IDs: UUID en CHAR(36) (portable)
## Reglas NO negociables
1) Nada se borra definitivamente (soft delete en entidades no-contables).
2) Historial inmutable:
   - Todas las tablas críticas tienen tabla *_history (append-only).
3) Integridad:
   - Foreign Keys obligatorias.
4) Multi-tenant por empresa:
   - Un usuario puede pertenecer a múltiples empresas.
   - Toda operación se hace “en nombre” de una empresa (X-Company-Id).
5) Permisos:
   - Se basan en role por empresa: owner/admin/member.
   - Policies obligatorias.
6) Auditoría:
   - registrar request_id/idempotency_key si está disponible.
   - registrar actor (user_id) cuando exista.
## Modelo de datos (source of truth)
Ver DDL: /database/ddl/001_phase1_identity_companies.sql
## Entidades
### users
- Identidad del usuario (login).
- Un usuario puede tener múltiples memberships.
### companies
- Empresa.
- Propietario es un membership con role=owner.
### company_memberships
- vínculo user↔company
- role: owner | admin | member
- status: active | invited | suspended
### audit_log (global)
- Registro de eventos de seguridad y cambios relevantes.
## Permisos (policies mínimas)
- CompaniesPolicy:
  - viewAny: membership active
  - view: membership active
  - create: cualquiera autenticado (crea empresa y se vuelve owner)
  - update: owner/admin
  - delete: solo owner (soft-delete)
- MembershipPolicy:
  - view/list: owner/admin
  - invite/create: owner/admin
  - update role/status: owner/admin (pero admin no puede promover a owner)
  - remove: owner/admin (pero no puede eliminar al último owner)
## Middleware obligatorio
### RequireAuth (Sanctum)
- el usuario debe estar autenticado.
### RequireCompany (custom)
- requiere X-Company-Id
- valida que el usuario tenga membership status=active en esa company
- inyecta company_id al request context
## Endpoints (mínimos y obligatorios)
### Auth
- POST /api/v1/auth/login
- POST /api/v1/auth/logout
- GET  /api/v1/auth/me
### Companies
- GET  /api/v1/companies
- POST /api/v1/companies
- GET  /api/v1/companies/{companyId}
- PATCH /api/v1/companies/{companyId}
- DELETE /api/v1/companies/{companyId}
### Memberships
- GET  /api/v1/companies/{companyId}/memberships
- POST /api/v1/companies/{companyId}/memberships (invite/create)
- PATCH /api/v1/companies/{companyId}/memberships/{membershipId}
- DELETE /api/v1/companies/{companyId}/memberships/{membershipId}
## Documentación + Postman (obligatorio)
- /docs/api/auth.md
- /docs/api/companies.md
- /postman/acccore-base.postman_collection.json
Cada endpoint debe tener:
- ejemplo request
- ejemplo response (success y error)
- item Postman
## Criterio de aprobación FASE 1
- Login devuelve token
- /auth/me responde y lista memberships
- RequireCompany bloquea requests sin X-Company-Id
- CRUD companies respetando policies
- Memberships list/invite/update/remove con reglas de rol
- Tablas *_history se llenan (por triggers o por escritura en service, pero se llenan sí o sí)
