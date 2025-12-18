# FASE 0 – Preparación e Infraestructura (CORREGIDA)

## Objetivo
Establecer una **base técnica y contable inquebrantable** antes de escribir cualquier lógica de negocio.

FASE 0 **NO es una fase de desarrollo**, es una fase de **validación y blindaje**.

---

## Alcance estricto (scope)

EN ESTA FASE **SÍ** se hace:
- Crear proyecto Laravel limpio
- Configurar conexión a PostgreSQL
- Ejecutar el DDL completo
- Verificar constraints, triggers y auditoría
- Instalar dependencias base (Composer, JWT solo como librería)
- Crear script de instalación y checklist
- Exponer endpoint técnico `/api/v1/health`

EN ESTA FASE **NO** se hace:
- Endpoints de negocio
- Autenticación funcional
- Controllers de dominio
- Migrations de negocio
- Policies
- Sync
- Postman
- Documentación de API

Si algo de lo anterior aparece, la fase **FALLA**.

---

## Pasos técnicos

### 0.1 Crear repositorio y proyecto

- Repo: `accounting-core-api`
- Framework: Laravel (última LTS)
- PHP >= 8.2

---

### 0.2 Script de instalación obligatorio

Debe existir el archivo:

```

/scripts/install.sh

```

Este script:
- **verifica** dependencias
- **NO instala** paquetes del sistema operativo
- **falla temprano** con mensajes claros

---

### 0.3 Verificaciones obligatorias del entorno

El script debe verificar:

- PHP >= 8.2
- Composer instalado
- PostgreSQL >= 14
- Extensiones PHP obligatorias:
  - pdo_pgsql
  - mbstring
  - openssl
  - json
  - ctype
  - xml

Si falta alguna → **FAIL**.

---

### 0.4 Base de datos (DDL primero)

1. Crear base de datos vacía
2. Ejecutar **TODO el DDL contable**
3. Verificar manualmente:

- ❌ No se pueden borrar asientos
- ❌ No se pueden editar asientos
- ❌ No se puede escribir en períodos cerrados
- ✅ Toda escritura genera `audit_log`

Si cualquiera falla → **FASE 0 FALLA**.

---

### 0.5 Seed técnico mínimo (manual)

Insertar manualmente (SQL directo):
- 1 company
- 1 user
- 1 membership (owner)
- plan de cuentas mínimo

Esto es **solo para validar reglas**, no UX.

---

### 0.6 Endpoint técnico de salud

Implementar:

```

GET /api/v1/health

````

Respuesta esperada:

```json
{
  "status": "ok",
  "db": "ok",
  "time": "ISO-8601"
}
````

No requiere auth.

---

## Criterios de FALLA (gate duro)

La FASE 0 se considera FALLIDA si:

* PHP no cumple versión
* Falta una extensión obligatoria
* PostgreSQL no responde
* El DDL permite borrar o editar contabilidad
* `audit_log` no registra cambios
* `/health` no responde correctamente

NO se puede avanzar a FASE 1 si alguno falla.

---

## Criterios de aceptación

Solo se avanza si:

* La base de datos **impide errores por sí sola**
* El backend conecta sin warnings
* El script detecta correctamente faltantes
* No existe lógica de negocio en el código

````
