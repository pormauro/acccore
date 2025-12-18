# FASE 3 – Documentos Comerciales

## Objetivo
Conectar documentos comerciales con contabilidad.

## Alcance
- Presupuestos
- Facturas
- Recibos

## Tareas
1. CRUD Documents y DocumentItems.
2. Estados: draft, issued, paid, cancelled.
3. Emitir documento genera asiento.
4. Cancelar documento genera reversa.
5. Policies por rol y estado.

## Reglas
- Documento emitido no se edita.
- Toda emisión impacta contabilidad.

## Prohibido
- Pagos
- Sync
