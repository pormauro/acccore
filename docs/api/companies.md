# Companies API
## Reglas globales
Todos los endpoints (excepto auth) requieren:
- Authorization: Bearer {{token}}

`X-Company-Id` es obligatorio en endpoints que operan sobre una empresa existente:
- GET /companies/{companyId}
- PATCH /companies/{companyId}
- DELETE /companies/{companyId}
- /companies/{companyId}/memberships...

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

## GET /api/v1/companies/{companyId}
Requiere X-Company-Id.
### Response 200
```json
{ "company": { "id": "uuid", "legal_name": "DEPROS", "status": "active" } }
```
### Errores
* 400 MISSING_COMPANY_ID
* 403 FORBIDDEN
* 404 NOT_FOUND

## PATCH /api/v1/companies/{companyId}
Solo owner/admin. Requiere X-Company-Id.
### Request
```json
{ "trade_name": "DEPROS" }
```
### Response 200
```json
{ "company": { "id": "uuid", "trade_name": "DEPROS" } }
```
### Errores
* 400 MISSING_COMPANY_ID
* 403 FORBIDDEN
* 404 NOT_FOUND

## DELETE /api/v1/companies/{companyId}
Solo owner. Soft delete. Requiere X-Company-Id.
### Response 200
```json
{ "status": "ok" }
```
### Errores
* 400 MISSING_COMPANY_ID
* 403 FORBIDDEN
* 404 NOT_FOUND

## GET /api/v1/companies/{companyId}/memberships
Lista members (owner/admin). Requiere X-Company-Id.
### Response 200
```json
{ "items": [ { "id":"uuid","user_id":"uuid","role":"member","status":"active" } ] }
```
### Errores
* 400 MISSING_COMPANY_ID
* 403 FORBIDDEN
* 404 NOT_FOUND

## POST /api/v1/companies/{companyId}/memberships
Invita o crea membership (owner/admin). Requiere X-Company-Id.
### Request
```json
{ "email": "new@user.com", "role": "member" }
```
### Response 201
```json
{ "membership": { "id":"uuid","status":"invited","invited_email":"new@user.com" } }
```
### Errores
* 400 MISSING_COMPANY_ID
* 403 FORBIDDEN
* 404 NOT_FOUND
* 409 MEMBERSHIP_ALREADY_EXISTS

## PATCH /api/v1/companies/{companyId}/memberships/{membershipId}
Actualiza role/status (owner/admin; admin no puede owner). Requiere X-Company-Id.
### Request
```json
{ "role": "admin", "status": "active" }
```
### Response 200
```json
{ "membership": { "id":"uuid","role":"admin","status":"active" } }
```
### Errores
* 400 MISSING_COMPANY_ID
* 403 FORBIDDEN
* 404 NOT_FOUND
* 409 LAST_OWNER

## DELETE /api/v1/companies/{companyId}/memberships/{membershipId}
Elimina membership (soft delete). No permitir borrar último owner. Requiere X-Company-Id.
### Response 200
```json
{ "status": "ok" }
```
### Errores
* 400 MISSING_COMPANY_ID
* 403 FORBIDDEN
* 404 NOT_FOUND
* 409 LAST_OWNER
