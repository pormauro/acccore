# FASE 1 – Casos de Uso Funcionales (Identity + Companies)

Este documento define **qué debe poder hacer el sistema** en FASE 1,
independientemente de cómo esté implementado.

Si un caso falla, **FASE 1 NO está aprobada**.

---

## CU-01 – Primer acceso / creación de usuario

### Escenario
Una persona accede al sistema por primera vez.

### Reglas
- **No existe un endpoint público de registro libre.**
- Un usuario se crea:
  - implícitamente al aceptar una invitación, o
  - porque ya existe previamente en el sistema.

### Flujo
1. El usuario inicia sesión con email y password.
2. Si el usuario existe:
   - se autentica normalmente.
3. Si el usuario no existe pero hay una invitación pendiente para ese email:
   - el sistema crea el usuario automáticamente.
4. El sistema devuelve:
   - token válido
   - datos del usuario
   - lista de memberships (activas o invitadas).

### Resultado esperado
- Usuario queda en estado `active`.
- El sistema **no permite registro libre sin invitación o control previo**.

---

## CU-02 – Creación de empresa (usuario independiente)

### Escenario
Un usuario crea su primera empresa.

### Flujo
1. Usuario autenticado envía POST /companies.
2. El sistema crea:
   - la empresa
   - una membership con role = `owner`.
3. El sistema responde con:
   - empresa creada
   - membership owner.

### Resultado esperado
- El usuario queda como **owner** de la empresa.
- La empresa queda `active`.
- El usuario puede operar usando `X-Company-Id`.

---

## CU-03 – Listado de empresas propias

### Escenario
Un usuario pertenece a una o más empresas.

### Flujo
1. Usuario autenticado llama GET /companies.
2. El sistema devuelve solo empresas donde:
   - tiene membership
   - status = `active`
   - no están soft-deleted.

### Resultado esperado
- No aparecen empresas ajenas.
- No aparecen empresas eliminadas lógicamente.

---

## CU-04 – Invitación de miembro

### Escenario
Un owner o admin quiere sumar un colaborador.

### Flujo
1. Owner/Admin llama POST /companies/{id}/memberships.
2. Envía email + role.
3. El sistema crea una membership con:
   - status = `invited`
   - invited_email = email enviado.

### Resultado esperado
- Membership creada sin requerir que el usuario exista aún.
- No se duplica membership para el mismo email/company.

---

## CU-05 – Aceptación automática de invitación

### Escenario
Un usuario invitado accede al sistema.

### Flujo
1. El usuario inicia sesión con un email que coincide con una invitación pendiente.
2. El sistema detecta automáticamente la membership en estado `invited`.
3. La membership se vincula al usuario.
4. El status de la membership pasa a `active`.

### Resultado esperado
- No existe endpoint manual de aceptación.
- La aceptación es **automática y transparente**.
- El historial registra el cambio.

---

## CU-06 – Cambio de rol

### Escenario
Un owner quiere promover o degradar un miembro.

### Flujo
1. Owner llama PATCH /memberships/{id}.
2. Cambia role de `member` a `admin` o viceversa.

### Reglas
- Admin **NO** puede asignar role `owner`.
- Solo owner puede crear otro owner (si se permite en el futuro).

### Resultado esperado
- Cambio permitido solo si cumple reglas.
- Se registra historial del cambio.

---

## CU-07 – Protección del owner

### Escenario
Se intenta eliminar o degradar al último owner.

### Flujo
1. Admin intenta eliminar o degradar owner → falla.
2. Owner intenta eliminarse a sí mismo siendo el único owner → falla.

### Resultado esperado
- Siempre debe existir **al menos un owner activo por empresa**.
- El sistema bloquea la operación con error claro y explícito.

---

## CU-08 – Suspensión de miembro

### Escenario
Admin u owner suspende un miembro.

### Flujo
1. Admin/Owner llama PATCH /memberships/{id}.
2. Cambia status a `suspended`.

### Resultado esperado
- El miembro suspendido:
  - no pasa RequireCompany
  - no puede operar.
- El historial refleja el cambio.

---

## CU-09 – Eliminación lógica de empresa

### Escenario
Owner decide cerrar una empresa.

### Flujo
1. Owner llama DELETE /companies/{id}.
2. El sistema hace soft-delete.

### Resultado esperado
- La empresa queda con `deleted_at`.
- No se elimina información física.
- Memberships quedan inactivas.
- Historial y audit_log se actualizan.

---

## CU-10 – Auditoría mínima

### Escenario
Se ejecutan acciones sensibles.

### Acciones auditables mínimas
- auth.login (exitoso)
- auth.login_failed
- company.create
- company.update
- company.delete (soft)
- membership.invite
- membership.activate
- membership.role_change
- membership.suspend

### Exclusiones
- Operaciones de lectura (GET) **NO generan audit_log**,
  excepto en contextos de seguridad o detección de abuso.

### Resultado esperado
- audit_log contiene entradas claras.
- actor_user_id correcto.
- company_id correcto cuando aplica.

---

## CRITERIO FINAL DE ACEPTACIÓN FASE 1

FASE 1 está **APROBADA** si:

- Todos los casos CU-01 a CU-10 funcionan.
- No existen flujos ambiguos.
- No se pierde historial.
- No se rompe multiempresa.
- No se puede dejar una empresa sin owner.
