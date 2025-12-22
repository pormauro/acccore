# Auth API
## POST /api/v1/auth/login
### Headers
Content-Type: application/json
### Request
```json
{ "email": "user@example.com", "password": "secret" }
```
### Response 200
```json
{
  "token": "plain_text_token",
  "user": {
    "id": "uuid",
    "name": "Mauro",
    "email": "user@example.com",
    "status": "active"
  },
  "memberships": [
    { "company_id": "uuid", "role": "owner", "status": "active" }
  ]
}
```
### Errores
* 401 INVALID_CREDENTIALS
* 403 USER_SUSPENDED
## POST /api/v1/auth/logout
### Headers
Authorization: Bearer {{token}}
### Response 200
```json
{ "status": "ok" }
```
## GET /api/v1/auth/me
### Headers
Authorization: Bearer {{token}}
### Response 200
```json
{
  "user": { "id": "uuid", "email": "user@example.com", "status": "active" },
  "memberships": [
    { "company_id": "uuid", "role": "owner", "status": "active" }
  ]
}
```
