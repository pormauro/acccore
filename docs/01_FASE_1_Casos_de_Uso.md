# FASE 1 – Casos de Uso Funcionales (Identity + Companies)

Este documento define **qué debe poder hacer el sistema** en FASE 1,
independientemente de cómo esté implementado.

Si un caso falla, **FASE 1 NO está aprobada**.

---

## CU-01 – Registro y primer acceso

### Escenario
Un usuario nuevo quiere usar el sistema por primera vez.

### Flujo
1. Usuario se registra (email + password).
   - El registro puede ser explícito (endpoint) o implícito al aceptar invitación.
2. Usuario inicia sesión.
3. El sistema devuelve:
   - token válido
   - datos del usuario
   - lista de memberships (vacía).

### Resultado esperado
- Usuario queda en estado `active`.
- No pertenece aún a ninguna empresa.

---

## CU-02 – Creación de empresa (usuario independiente)

### Escenario
Un usuario crea su primera empresa.

### Flujo
1. Usuario autenticado envía POST /companies.
2. Sistema crea:
   - empresa
   - membership con role = `owner`
3. Sistema responde con:
   - empresa creada
   - membership owner

### Resultado esperado
- El usuario queda como **owner** de esa empresa.
- La empresa queda `active`.
- El usuario puede operar usando `X-Company-Id` de esa empresa.

---

## CU-03 – Listado de empresas propias

### Escenario
Un usuario pertenece a varias empresas.

### Flujo
1. Usuario autenticado llama GET /companies.
2. Sistema devuelve solo empresas donde:
   - tiene membership
   - status = `active`

### Resultado esperado
- No aparecen empresas ajenas.
- No aparecen empresas soft-deleted.

---

## CU-04 – Invitación de miembro

### Escenario
Owner o admin quiere sumar un colaborador.

### Flujo
1. Owner/Admin llama POST /companies/{id}/memberships.
2. Envía email + role.
3. Sistema crea membership en estado `invited`.

### Resultado esperado
- Membership creada con status `invited`.
- No requiere que el usuario exista todavía.

---

## CU-05 – Aceptación de invitación

### Escenario
El usuario invitado acepta la invitación.

### Flujo
1. Usuario se registra o inicia sesión.
2. El sistema vincula el usuario a la membership existente.
3. Membership pasa a status `active`.

### Disparador
- La aceptación ocurre automáticamente al detectar login con email invitado.

### Resultado esperado
- Usuario queda vinculado a la empresa.
- Puede operar según su role.

---

## CU-06 – Cambio de rol

### Escenario
Owner quiere promover un member a admin.

### Flujo
1. Owner llama PATCH /memberships/{id}.
2. Cambia role de `member` a `admin`.

### Resultado esperado
- Cambio permitido.
- Se registra historial.
- Admin NO puede promover a owner.

---

## CU-07 – Protección del owner

### Escenario
Se intenta eliminar o degradar al último owner.

### Flujo
1. Admin intenta eliminar owner → falla.
2. Owner intenta eliminarse a sí mismo siendo el único owner → falla.

### Resultado esperado
- Siempre debe existir al menos **un owner activo** por empresa.
- El sistema bloquea la operación con error claro.

---

## CU-08 – Suspensión de miembro

### Escenario
Admin suspende un miembro problemático.

### Flujo
1. Admin llama PATCH /memberships/{id}.
2. Cambia status a `suspended`.

### Resultado esperado
- El miembro suspendido:
  - no puede operar
  - no pasa RequireCompany
- El historial refleja el cambio.

---

## CU-09 – Eliminación lógica de empresa

### Escenario
Owner decide cerrar una empresa.

### Flujo
1. Owner llama DELETE /companies/{id}.
2. Sistema hace soft-delete.

### Resultado esperado
- Empresa queda `deleted_at != null`.
- No se borra nada físicamente.
- Memberships quedan asociadas pero inactivas.
- Historial y audit_log se actualizan.

---

## CU-10 – Auditoría mínima

### Escenario
Se realizan acciones sensibles.

### Acciones auditables mínimas
- login exitoso
- login fallido
- create company
- invite membership
- change role
- suspend membership
- delete company
### Acciones no auditables
- Lecturas (GET) no generan audit_log salvo contexto de seguridad.

### Resultado esperado
- audit_log contiene entradas claras.
- actor_user_id correcto.
- company_id correcto cuando aplica.

---

## CRITERIO FINAL DE ACEPTACIÓN FASE 1

FASE 1 está **APROBADA** si:

- Todos los casos CU-01 a CU-10 funcionan.
- No hay acciones posibles fuera del rol.
- No se pierde historial.
- No se rompe multi-empresa.
- No hay acciones ambiguas.

Si un caso no está cubierto → **no se continúa a FASE 2**.
