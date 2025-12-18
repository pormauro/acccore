# FASE 2 – Contabilidad Core

## Objetivo
Implementar el motor contable independiente del negocio.

## Alcance
- Plan de cuentas
- Asientos
- Períodos
- Cierres
- Reversas

## Tareas
1. CRUD accounts.
2. CRUD accounting_periods.
3. Crear journal_entries y journal_lines.
4. Validar Debe = Haber.
5. Cerrar períodos.
6. Reversión de asientos (nunca editar).

## Reglas
- Toda contabilidad pasa por AccountingService.
- Nada contable se borra.

## Prohibido
- Facturación
- Pagos
- Jobs
