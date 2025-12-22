# Instrucciones Globales – Proyecto ACCCORE

Este proyecto se desarrolla bajo las siguientes reglas NO negociables:

## ENTORNO
- Hosting compartido (Hostinger)
- Base de datos: MySQL / MariaDB
- PHP 8.2+
- Framework: Laravel 10.x

## BASE DE DATOS
- NO PostgreSQL
- NO features específicas de motor
- DDL portable (MySQL ↔ PostgreSQL)
- ENGINE=InnoDB obligatorio
- Foreign keys obligatorias
- Transacciones obligatorias para operaciones contables
- Prohibido borrar datos contables (append-only)

## HISTORIAL
- Todas las entidades críticas tienen historial
- NO soft delete en contabilidad
- NO UPDATE destructivo
- Todo cambio genera registro histórico

## ARQUITECTURA
- Backend primero
- Frontend consume contrato, no lógica
- Lógica de negocio solo en Services
- Controllers mínimos
- Policies obligatorias

## CODEX – LIMITACIONES
Codex:
- NO decide arquitectura
- NO cambia motor de DB
- NO agrega features
- NO “optimiza” por cuenta propia
- NO toca fases anteriores

Codex SOLO implementa lo explícitamente indicado en los documentos de fase.

Si algo no está definido:
→ NO se implementa.

## OBJETIVO
Construir un sistema contable base sólido, trazable e inmutable,
portable a PostgreSQL en el futuro sin reescritura lógica.



VISIÓN GENERAL (antes de las fases)

Core del sistema: contabilidad (no facturación, no jobs).

Regla absoluta:
👉 Nada se borra, todo se versiona, todo tiene historial.

Base de datos: MySQL/MariaDB (InnoDB).

Historial:

Todas las tablas tienen:

created_at

created_by

updated_at

updated_by

deleted_at (soft delete)

tablas *_history o audit_log (append-only).

Permisos: por rol + contexto (empresa).

Target: trabajador independiente + microempresa.

🟢 FASE 0 — Preparación del sistema (YA HECHA)
Objetivo

Dejar una base estable, instalable y verificable en hosting compartido.

Incluye

Laravel 10.x

MySQL/MariaDB

.env productivo

Script install.sh

Checklist

Endpoint /api/v1/health

NO incluye

Usuarios

Empresas

Contabilidad

Lógica de negocio

📌 Esta fase solo valida que el sistema puede vivir en el mundo real.

🟡 FASE 1 — Identidad, usuarios y empresas
Objetivo

Resolver quién sos, en nombre de quién operás y qué podés hacer.

Incluye

Usuarios

Empresas

Relación usuario ↔ empresa

Roles por empresa:

owner

admin

member

Estados (active / suspended)

Policies de acceso

Claves

Un usuario puede estar en múltiples empresas

Todo request lleva X-Company-Id

No hay contabilidad todavía

📌 Sin esto, nada tiene contexto.

🔵 FASE 2 — Núcleo contable (el corazón del sistema)
Objetivo

Crear un motor contable real, no una planilla disfrazada.

Incluye

Plan de cuentas

Períodos contables

Asientos

Líneas de asiento

Cierre de período

Reversión de asientos

Reglas

Asientos inmutables

Revertir ≠ borrar

No se puede escribir en período cerrado

Todo pasa por AccountingService

📌 Esta fase ya vale dinero. Todo lo demás es accesorio.

🟣 FASE 3 — Documentos contables (facturas, notas, etc.)
Objetivo

Conectar documentos del mundo real con el núcleo contable.

Incluye

Documentos (factura, nota crédito/débito, recibos simples)

Ítems de documento

Estados (draft / issued / cancelled)

Emisión genera asiento automático

Cancelación genera reversión

NO incluye

AFIP

Impuestos complejos

Integraciones externas

📌 Documento ≠ contabilidad, pero la dispara.

🟠 FASE 4 — Jobs, tiempos y costos
Objetivo

Reflejar el trabajo real del mantenedor en números.

Incluye

Jobs / trabajos

Registro de horas

Materiales usados

Costos directos

Asientos automáticos de costo

Reglas

No payroll real

No inventario complejo

Impacta resultado del período

📌 Acá el trabajador independiente se ve reflejado.

🟤 FASE 5 — Pagos y cobros
Objetivo

Registrar movimiento real de dinero, sin confundirlo con documentos.

Incluye

Pagos

Cobros

Asignación parcial

Cuentas de caja/banco

Asientos de pago/cobro

NO incluye

Conciliación bancaria

Multimoneda (por ahora)

📌 Documento ≠ pago. Esto lo deja claro.

⚫ FASE 6 — Contrato de frontend (API madura)
Objetivo

Que el backend sea predecible, documentado y consumible.

Incluye

Responses con abilities

Errores normalizados

Versionado de endpoints

Documentación Markdown

Postman completo

Regla clave

No existe endpoint sin:

doc

ejemplo

item Postman

📌 Esto hace que SISA se pueda montar encima.

🔴 FASE 7 — Offline, sync y trazabilidad avanzada
Objetivo

Soportar trabajo sin conexión y auditoría seria.

Incluye

Idempotency keys

Sync por lotes

Registro de cambios

Conflictos detectables

Auditoría completa

📌 Nivel profesional. No MVP, pero sí diferencial.