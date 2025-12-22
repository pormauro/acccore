# Companies API
## Reglas globales
Todos los endpoints (excepto auth) requieren:
- Authorization: Bearer {{token}}
- X-Company-Id: {{company_id}}
## GET /api/v1/companies
Lista empresas donde el usuario tiene membership active.
### Response 200
```json
{ "items": [ { "id": "uuid", "legal_name": "DEPROS", "status": "active" } ] }
```
## POST /api/v1/companies
Crea empresa y crea membership owner.
### Request
```json
{ "legal_name": "DEPROS SA", "trade_name": "DEPROS", "tax_id": "XX-..." }
```
### Response 201
```json
{
  "company": { "id": "uuid", "legal_name": "DEPROS SA" },
  "membership": { "id": "uuid", "role": "owner", "status": "active" }
}
```
## PATCH /api/v1/companies/{companyId}
Solo owner/admin.
### Request
```json
{ "trade_name": "DEPROS" }
```
### Errores
* 403 FORBIDDEN
* 404 NOT_FOUND
## DELETE /api/v1/companies/{companyId}
Solo owner. Soft delete.
### Response 200
```json
{ "status": "ok" }
```
## GET /api/v1/companies/{companyId}/memberships
Lista members (owner/admin).
### Response 200
```json
{ "items": [ { "id":"uuid","user_id":"uuid","role":"member","status":"active" } ] }
```
## POST /api/v1/companies/{companyId}/memberships
Invita o crea membership (owner/admin).
### Request
```json
{ "email": "new@user.com", "role": "member" }
```
### Response 201
```json
{ "membership": { "id":"uuid","status":"invited","invited_email":"new@user.com" } }
```
## PATCH /api/v1/companies/{companyId}/memberships/{membershipId}
Actualiza role/status (owner/admin; admin no puede owner).
### Request
```json
{ "role": "admin", "status": "active" }
```
## DELETE /api/v1/companies/{companyId}/memberships/{membershipId}
Elimina membership (soft delete). No permitir borrar último owner.
