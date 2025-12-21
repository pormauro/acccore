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
